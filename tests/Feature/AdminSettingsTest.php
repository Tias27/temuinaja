<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $username = 'admin-settings'): Admin
    {
        return Admin::create([
            'nama_admin' => 'Petugas Pengaturan', 'nama_pengguna' => $username,
            'kata_sandi_hash' => bcrypt('Settings-Password-123'),
        ]);
    }

    private function newAccount(): array
    {
        return [
            'nama_admin' => 'Petugas Baru', 'nama_pengguna' => 'petugas-baru',
            'current_password' => 'Settings-Password-123',
            'password' => 'New-Password-12345', 'password_confirmation' => 'New-Password-12345',
        ];
    }

    public function test_guests_cannot_access_or_change_settings(): void
    {
        $this->get(route('admin.settings.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.settings.admins.store'), $this->newAccount())->assertRedirect(route('admin.login'));
        $this->patch(route('admin.settings.profile'), $this->newAccount())->assertRedirect(route('admin.login'));
        $this->put(route('admin.settings.password'), $this->newAccount())->assertRedirect(route('admin.login'));
        $this->assertDatabaseCount('admin', 0);
    }

    public function test_profile_only_updates_logged_in_admin_and_requires_current_password(): void
    {
        $actor = $this->admin();
        $other = $this->admin('admin-lain');
        $hash = $actor->getAuthPassword();
        $this->actingAs($actor, 'admin');
        $payload = [
            'nama_admin' => 'Nama Baru', 'nama_pengguna' => 'nama-baru',
            'current_password' => 'wrong', 'id_admin' => $other->id_admin,
            'peran' => 'superadmin', 'kata_sandi_hash' => 'injected-hash',
        ];
        $this->patch(route('admin.settings.profile'), $payload)
            ->assertSessionHasErrorsIn('profile', 'current_password');
        $this->assertSame('admin-settings', $actor->fresh()->nama_pengguna);
        $this->patch(route('admin.settings.profile'), [
            ...$payload, 'current_password' => 'Settings-Password-123',
        ])->assertRedirect(route('admin.settings.index'))->assertSessionHasNoErrors();
        $this->assertSame('nama-baru', $actor->fresh()->nama_pengguna);
        $this->assertSame('Nama Baru', $actor->fresh()->nama_admin);
        $this->assertSame('petugas', $actor->fresh()->peran);
        $this->assertSame($hash, $actor->fresh()->getAuthPassword());
        $this->assertSame('admin-lain', $other->fresh()->nama_pengguna);
        $this->assertSame('Petugas Pengaturan', $other->fresh()->nama_admin);
        $this->get(route('admin.settings.index'))->assertOk()->assertSee('Nama Baru');
    }

    public function test_username_conflicts_preserve_data_and_passwords_are_not_flashed(): void
    {
        $actor = $this->admin();
        $this->admin('taken-name');
        $this->actingAs($actor, 'admin');
        $this->patch(route('admin.settings.profile'), [
            ...$this->newAccount(), 'nama_pengguna' => 'taken-name',
        ])->assertSessionHasErrorsIn('profile', 'nama_pengguna')
            ->assertSessionMissing('_old_input.current_password')
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation');
        $this->assertSame('admin-settings', $actor->fresh()->nama_pengguna);
        $this->post(route('admin.settings.admins.store'), [
            ...$this->newAccount(), 'nama_pengguna' => 'taken-name',
        ])->assertSessionHasErrorsIn('createAdmin', 'nama_pengguna');
        $this->assertDatabaseCount('admin', 2);
        $this->patch(route('admin.settings.profile'), [
            ...$this->newAccount(), 'nama_pengguna' => $actor->nama_pengguna,
        ])->assertSessionHasNoErrors();
    }

    public function test_admin_creation_hashes_password_keeps_creator_logged_in_and_new_admin_can_log_in(): void
    {
        $actor = $this->admin();
        $this->actingAs($actor, 'admin');
        $this->post(route('admin.settings.admins.store'), [
            ...$this->newAccount(), 'peran' => 'superadmin', 'id_admin' => $actor->id_admin,
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'admins']))->assertSessionHasNoErrors();
        $created = Admin::where('nama_pengguna', 'petugas-baru')->firstOrFail();
        $this->assertTrue(Hash::check('New-Password-12345', $created->getAuthPassword()));
        $this->assertSame('petugas', $created->peran);
        $this->assertNotSame($actor->id_admin, $created->id_admin);
        $this->assertAuthenticatedAs($actor, 'admin');
        $this->get(route('admin.settings.index', ['tab' => 'admins']))->assertOk()
            ->assertSee('Petugas Baru')->assertDontSee($created->getAuthPassword())
            ->assertDontSee('New-Password-12345');
        $this->post(route('admin.logout'));
        $this->post(route('admin.login.submit'), [
            'nama_pengguna' => 'petugas-baru', 'password' => 'New-Password-12345',
        ])->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($created, 'admin');
    }

    public function test_admin_creation_requires_confirmation_and_valid_password(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $this->post(route('admin.settings.admins.store'), [
            ...$this->newAccount(), 'current_password' => 'wrong',
        ])->assertSessionHasErrorsIn('createAdmin', 'current_password');
        $this->post(route('admin.settings.admins.store'), [
            ...$this->newAccount(), 'password' => 'short', 'password_confirmation' => 'short',
        ])->assertSessionHasErrorsIn('createAdmin', 'password');
        $this->post(route('admin.settings.admins.store'), [
            ...$this->newAccount(), 'password_confirmation' => 'Does-not-match',
        ])->assertSessionHasErrorsIn('createAdmin', 'password');
        $this->post(route('admin.settings.admins.store'), [
            ...$this->newAccount(), 'password' => str_repeat('a', 73), 'password_confirmation' => str_repeat('a', 73),
        ])->assertSessionHasErrorsIn('createAdmin', 'password');
        $this->assertDatabaseCount('admin', 1);
    }

    public function test_password_change_preserves_current_session_and_revokes_other_sessions(): void
    {
        config(['session.driver' => 'database']);
        $actor = $this->admin();
        $other = $this->admin('another-admin');
        $this->actingAs($actor, 'admin')->get(route('admin.settings.index'))->assertOk();
        $oldSession = session()->getId();
        foreach (['actor-other-device' => $actor, 'another-admin-session' => $other] as $id => $account) {
            DB::table('sessions')->insert([
                'id' => $id, 'user_id' => $account->id_admin, 'payload' => '', 'last_activity' => time(),
            ]);
        }
        $this->put(route('admin.settings.password'), [
            ...$this->newAccount(), 'id_admin' => $other->id_admin,
        ])->assertRedirect(route('admin.settings.index'))->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('New-Password-12345', $actor->fresh()->getAuthPassword()));
        $this->assertTrue(Hash::check('Settings-Password-123', $other->fresh()->getAuthPassword()));
        $this->assertDatabaseMissing('sessions', ['id' => 'actor-other-device']);
        $this->assertDatabaseMissing('sessions', ['id' => $oldSession]);
        $this->assertDatabaseHas('sessions', ['id' => 'another-admin-session']);
        $this->assertNotSame($oldSession, session()->getId());
        $this->get(route('admin.settings.index'))->assertOk();
        $this->assertAuthenticatedAs($actor, 'admin');
        $this->post(route('admin.logout'));
        $this->post(route('admin.login.submit'), [
            'nama_pengguna' => $actor->nama_pengguna, 'password' => 'Settings-Password-123',
        ])->assertSessionHasErrors('nama_pengguna');
        $this->post(route('admin.login.submit'), [
            'nama_pengguna' => $actor->nama_pengguna, 'password' => 'New-Password-12345',
        ])->assertRedirect(route('admin.index'));
    }

    public function test_password_change_rejects_wrong_current_password_and_old_session_hash(): void
    {
        $actor = $this->admin();
        $this->actingAs($actor, 'admin');
        $hash = $actor->getAuthPassword();
        $this->put(route('admin.settings.password'), [
            ...$this->newAccount(), 'current_password' => 'wrong',
        ])->assertSessionHasErrorsIn('password', 'current_password');
        $this->assertSame($hash, $actor->fresh()->getAuthPassword());
        $this->withSession(['password_hash_admin' => bcrypt('Old-Password-123')])
            ->get(route('admin.settings.index'))->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_settings_requests_require_csrf_and_limit_password_guessing(): void
    {
        $actor = $this->admin();
        $this->actingAs($actor, 'admin');
        $this->app->bind(ValidateCsrfToken::class, SettingsCsrfToken::class);
        $this->post(route('admin.settings.admins.store'), $this->newAccount())->assertStatus(419);
        $this->patch(route('admin.settings.profile'), $this->newAccount())->assertStatus(419);
        $this->put(route('admin.settings.password'), $this->newAccount())->assertStatus(419);
        for ($i = 0; $i < 5; $i++) {
            $this->withSession(['_token' => 'settings-test-token'])->post(route('admin.settings.admins.store'), [
                ...$this->newAccount(), '_token' => 'settings-test-token', 'current_password' => 'wrong',
            ])->assertSessionHasErrorsIn('createAdmin', 'current_password');
        }
        $this->post(route('admin.settings.admins.store'), [
            ...$this->newAccount(), '_token' => 'settings-test-token',
        ])->assertStatus(429);
        $this->assertDatabaseCount('admin', 1);
    }

    public function test_settings_tabs_escape_names_and_paginate_accounts_for_desktop_and_mobile(): void
    {
        $actor = $this->admin();
        $actor->update(['nama_admin' => '<img src=x onerror=alert(1)>']);
        $this->actingAs($actor, 'admin');
        $this->get(route('admin.settings.index'))->assertOk()
            ->assertSee('Edit profil')->assertSee('Ganti kata sandi')
            ->assertSee(e($actor->nama_admin), false)->assertDontSee($actor->nama_admin, false);
        for ($i = 0; $i < 10; $i++) {
            $this->admin('team-'.$i);
        }
        $this->withHeaders(['User-Agent' => 'Desktop'])
            ->get(route('admin.settings.index', ['tab' => 'admins']))->assertOk()
            ->assertSee('Tambah admin')->assertSee('Berikutnya')
            ->assertViewHas('accounts', fn ($accounts) => $accounts->count() === 10 && $accounts->total() === 11);
        $this->withHeaders(['User-Agent' => 'iPhone Mobile'])
            ->get(route('admin.settings.index', ['tab' => 'admins']))->assertOk()
            ->assertViewHas('accounts', fn ($accounts) => $accounts->count() === 5 && str_contains($accounts->nextPageUrl(), 'tab=admins'));
        $this->get(route('admin.settings.index', ['tab' => 'admins', 'page' => 3]))->assertOk()
            ->assertViewHas('accounts', fn ($accounts) => $accounts->count() === 1)
            ->assertDontSee('Berikutnya');
    }
}

class SettingsCsrfToken extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}
