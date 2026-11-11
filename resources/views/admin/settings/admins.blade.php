<div class="admin-team-layout">
    <section class="card admin-settings-card" id="tambah-admin" aria-labelledby="create-admin-title">
        <span class="eyebrow">TAMBAH REKAN PETUGAS</span>
        <h2 class="admin-section-title" id="create-admin-title">Bantu kelola bersama</h2>
        <p class="admin-settings-description">Admin baru bisa mengelola laporan dan menambahkan
            petugas, sama seperti akunmu</p>
        <form method="post" action="{{ route('admin.settings.admins.store') }}"
            class="admin-settings-form">
            @csrf
            <div class="admin-settings-fields">
                <div class="field">
                    <label for="admin-name">Nama lengkap</label>
                    <input id="admin-name" name="nama_admin" value="{{ old('nama_admin') }}"
                        maxlength="120" autocomplete="off" required
                        @error('nama_admin', 'createAdmin') aria-invalid="true" aria-describedby="admin-name-error" @enderror>
                    @error('nama_admin', 'createAdmin')
                        <p class="field-error" id="admin-name-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="admin-username">Nama pengguna</label>
                    <input id="admin-username" name="nama_pengguna"
                        value="{{ old('nama_pengguna') }}" minlength="3" maxlength="50"
                        autocomplete="off" autocapitalize="none" spellcheck="false" required
                        @error('nama_pengguna', 'createAdmin') aria-invalid="true" aria-describedby="admin-username-error" @enderror>
                    @error('nama_pengguna', 'createAdmin')
                        <p class="field-error" id="admin-username-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="admin-settings-fields">
                <div class="field">
                    <label for="admin-password">Kata sandi admin baru</label>
                    <input id="admin-password" type="password" name="password" minlength="12"
                        maxlength="72" autocomplete="new-password" required
                        aria-describedby="admin-password-hint @error('password', 'createAdmin') admin-password-error @enderror"
                        @error('password', 'createAdmin') aria-invalid="true" @enderror>
                    <p class="field-hint" id="admin-password-hint">Minimal 12 karakter</p>
                    @error('password', 'createAdmin')
                        <p class="field-error" id="admin-password-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="admin-password-confirmation">Ulangi kata sandi baru</label>
                    <input id="admin-password-confirmation" type="password"
                        name="password_confirmation" minlength="12" maxlength="72"
                        autocomplete="new-password" required>
                </div>
            </div>
            <div class="admin-settings-confirm">
                <div class="field">
                    <label for="admin-current-password">Konfirmasi dengan kata sandi akunmu</label>
                    <input id="admin-current-password" type="password" name="current_password"
                        autocomplete="current-password" required
                        aria-describedby="admin-current-hint @error('current_password', 'createAdmin') admin-current-error @enderror"
                        @error('current_password', 'createAdmin') aria-invalid="true" @enderror>
                    <p class="field-hint" id="admin-current-hint">Masukkan kata sandi
                        {{ $admin->nama_pengguna }} yang sedang login</p>
                    @error('current_password', 'createAdmin')
                        <p class="field-error" id="admin-current-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="admin-settings-actions">
                <button class="button accent" type="submit">Tambah admin <span
                        aria-hidden="true">&plus;</span></button>
            </div>
        </form>
    </section>

    <section class="card admin-settings-card admin-team" aria-labelledby="admin-team-title">
        <div class="admin-team-heading">
            <div><span class="eyebrow">TIM TEMUIN AJA</span>
                <h2 class="admin-section-title" id="admin-team-title">Daftar admin</h2>
            </div>
            <span class="admin-team-total">{{ $accounts->total() }} akun</span>
        </div>
        <ul class="admin-team-list">
            @foreach ($accounts as $account)
                <li>
                    <span class="admin-avatar"
                        aria-hidden="true">{{ mb_strtoupper(mb_substr($account->nama_admin, 0, 1)) }}</span>
                    <div class="admin-team-person">
                        <strong>{{ $account->nama_admin }}</strong>
                        <span>{{ $account->nama_pengguna }}</span>
                        <small>Bergabung
                            {{ $account->created_at?->translatedFormat('d M Y') ?? '-' }}</small>
                    </div>
                    @if ($account->id_admin === $admin->id_admin)
                        <span class="admin-self-badge">Kamu</span>
                    @endif
                </li>
            @endforeach
        </ul>
        @if ($accounts->hasPages())
            <nav class="catalog-pagination" aria-label="Halaman daftar admin">
                @if ($accounts->previousPageUrl())
                    <a href="{{ $accounts->previousPageUrl() }}">&larr; Sebelumnya</a>
                @endif
                <span>Halaman {{ $accounts->currentPage() }} dari
                    {{ $accounts->lastPage() }}</span>
                @if ($accounts->nextPageUrl())
                    <a href="{{ $accounts->nextPageUrl() }}">Berikutnya &rarr;</a>
                @endif
            </nav>
        @endif
    </section>
</div>
