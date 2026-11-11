<div class="admin-settings-layout">
    <aside class="admin-settings-aside">
        <span class="eyebrow">PROFIL SAYA</span>
        <h2 class="admin-title">Biar tim tahu<br>siapa kamu</h2>
        <p>Nama ini tampil sebagai petugas yang memeriksa atau menyelesaikan laporan</p>
        <dl>
            <dt>Bergabung sejak</dt>
            <dd>{{ $admin->created_at?->translatedFormat('d F Y') ?? '-' }}</dd>
        </dl>
    </aside>
    <div class="admin-settings-content">
        <section class="card admin-settings-card" id="profil" aria-labelledby="profile-title">
            <span class="eyebrow">01 / IDENTITAS AKUN</span>
            <h2 class="admin-section-title" id="profile-title">Edit profil</h2>
            <p class="admin-settings-description">Perbarui nama tampilan dan nama pengguna untuk
                login</p>
            <form method="post" action="{{ route('admin.settings.profile') }}"
                class="admin-settings-form">
                @csrf
                @method('PATCH')
                <div class="admin-settings-fields">
                    <div class="field">
                        <label for="profile-name">Nama lengkap</label>
                        <input id="profile-name" name="nama_admin"
                            value="{{ old('nama_admin', $admin->nama_admin) }}" maxlength="120"
                            autocomplete="name" required
                            @error('nama_admin', 'profile') aria-invalid="true" aria-describedby="profile-name-error" @enderror>
                        @error('nama_admin', 'profile')
                            <p class="field-error" id="profile-name-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field">
                        <label for="profile-username">Nama pengguna</label>
                        <input id="profile-username" name="nama_pengguna"
                            value="{{ old('nama_pengguna', $admin->nama_pengguna) }}" minlength="3"
                            maxlength="50" autocomplete="username" autocapitalize="none"
                            spellcheck="false" required
                            aria-describedby="profile-username-hint @error('nama_pengguna', 'profile') profile-username-error @enderror"
                            @error('nama_pengguna', 'profile') aria-invalid="true" @enderror>
                        <p class="field-hint" id="profile-username-hint">Huruf, angka, titik, garis
                            bawah, atau tanda hubung</p>
                        @error('nama_pengguna', 'profile')
                            <p class="field-error" id="profile-username-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="field">
                    <label for="profile-current-password">Kata sandi akunmu</label>
                    <input id="profile-current-password" type="password" name="current_password"
                        autocomplete="current-password" required
                        @error('current_password', 'profile') aria-invalid="true" aria-describedby="profile-password-error" @enderror>
                    @error('current_password', 'profile')
                        <p class="field-error" id="profile-password-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="admin-settings-actions">
                    <button class="button accent" type="submit">Simpan profil <span
                            aria-hidden="true">&rarr;</span></button>
                </div>
            </form>
        </section>

        <section class="card admin-settings-card" id="kata-sandi" aria-labelledby="password-title">
            <span class="eyebrow">02 / KEAMANAN</span>
            <h2 class="admin-section-title" id="password-title">Ganti kata sandi</h2>
            <p class="admin-settings-description">Gunakan minimal 12 karakter, misalnya gabungan
                beberapa kata yang mudah kamu ingat</p>
            <form method="post" action="{{ route('admin.settings.password') }}"
                class="admin-settings-form">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="password-current">Kata sandi saat ini</label>
                    <input id="password-current" type="password" name="current_password"
                        autocomplete="current-password" required
                        @error('current_password', 'password') aria-invalid="true" aria-describedby="password-current-error" @enderror>
                    @error('current_password', 'password')
                        <p class="field-error" id="password-current-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="admin-settings-fields">
                    <div class="field">
                        <label for="password-new">Kata sandi baru</label>
                        <input id="password-new" type="password" name="password" minlength="12"
                            maxlength="72" autocomplete="new-password" required
                            @error('password', 'password') aria-invalid="true" aria-describedby="password-new-error" @enderror>
                        @error('password', 'password')
                            <p class="field-error" id="password-new-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field">
                        <label for="password-confirmation">Ulangi kata sandi baru</label>
                        <input id="password-confirmation" type="password"
                            name="password_confirmation" minlength="12" maxlength="72"
                            autocomplete="new-password" required>
                    </div>
                </div>
                <div class="admin-settings-actions">
                    <button class="button accent" type="submit">Ganti kata sandi <span
                            aria-hidden="true">&rarr;</span></button>
                    <p>Perangkat lain perlu login ulang setelah perubahan ini</p>
                </div>
            </form>
        </section>
    </div>
</div>
