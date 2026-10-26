<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
    ['label' => 'Pengaturan', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Pengaturan Sistem</h1>
        <p class="page-subtitle">Konfigurasi aplikasi CBT School</p>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
    <!-- General Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Umum</h3>
        </div>
        <div class="card-body">
            <form id="generalSettingsForm">
                <div class="form-group">
                    <label class="form-label">Nama Sekolah</label>
                    <input type="text" class="form-input" value="<?= h(config('app.name', 'CBT School')) ?>" disabled>
                    <div class="form-hint">Diubah melalui file .env</div>
                </div>

                <div class="form-group">
                    <label class="form-label">URL Aplikasi</label>
                    <input type="text" class="form-input" value="<?= h(config('app.url', url('/'))) ?>" disabled>
                    <div class="form-hint">Diubah melalui file .env</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Timezone</label>
                    <input type="text" class="form-input" value="<?= h(config('app.timezone', 'Asia/Jakarta')) ?>" disabled>
                    <div class="form-hint">Diubah melalui file .env</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Debug Mode</label>
                    <input type="text" class="form-input" value="<?= config('app.debug') ? 'Aktif' : 'Nonaktif' ?>" disabled>
                    <div class="form-hint">Diubah melalui file .env</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Versi Aplikasi</label>
                    <input type="text" class="form-input" value="1.0.0" disabled>
                </div>
            </form>
        </div>
    </div>

    <!-- Exam Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pengaturan Ujian</h3>
        </div>
        <div class="card-body">
            <form id="examSettingsForm">
                <div class="form-group">
                    <label class="form-label" for="default_duration">Durasi Default (menit)</label>
                    <input type="number" id="default_duration" class="form-input" value="60" min="1" max="1440">
                </div>

                <div class="form-group">
                    <label class="form-label" for="default_passing_score">Nilai Kelulusan Default (%)</label>
                    <input type="number" id="default_passing_score" class="form-input" value="70" min="0" max="100" step="0.01">
                </div>

                <div class="form-group">
                    <label class="form-label" for="max_retry_attempts">Maksimal Percobaan Ulang Default</label>
                    <input type="number" id="max_retry_attempts" class="form-input" value="0" min="0" max="10">
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" id="default_shuffle_questions" class="form-check-input" checked>
                    <label class="form-check-label" for="default_shuffle_questions">Acak soal secara default</label>
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" id="default_shuffle_options" class="form-check-input" checked>
                    <label class="form-check-label" for="default_shuffle_options">Acak opsi secara default</label>
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" id="default_show_results" class="form-check-input" checked>
                    <label class="form-check-label" for="default_show_results">Tampilkan hasil setelah selesai</label>
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" id="default_browser_lockdown" class="form-check-input">
                    <label class="form-check-label" for="default_browser_lockdown">Fullscreen enforcement default</label>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Pengaturan Ujian</button>
            </form>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Keamanan</h3>
        </div>
        <div class="card-body">
            <form id="securitySettingsForm">
                <div class="form-group">
                    <label class="form-label" for="session_lifetime">Masa Berlaku Session (menit)</label>
                    <input type="number" id="session_lifetime" class="form-input" value="120" min="10" max="1440">
                </div>

                <div class="form-group">
                    <label class="form-label" for="csrf_expiry">CSRF Token Expiry (menit)</label>
                    <input type="number" id="csrf_expiry" class="form-input" value="120" min="10" max="1440">
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" id="require_https" class="form-check-input" checked>
                    <label class="form-check-label" for="require_https">Require HTTPS (production)</label>
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" id="secure_cookies" class="form-check-input" checked>
                    <label class="form-check-label" for="secure_cookies">Secure Cookies</label>
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" id="http_only_cookies" class="form-check-input" checked>
                    <label class="form-check-label" for="http_only_cookies">HttpOnly Cookies</label>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Pengaturan Keamanan</button>
            </form>
        </div>
    </div>

    <!-- Database Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Database</h3>
        </div>
        <div class="card-body">
            <dl style="display: grid; grid-template-columns: 180px 1fr; gap: 0.75rem 1.5rem; font-size: 0.875rem;">
                <dt class="text-secondary">Driver</dt>
                <dd><?= h(config('database.driver', 'mysql')) ?></dd>

                <dt class="text-secondary">Host</dt>
                <dd><?= h(config('database.host', 'localhost')) ?></dd>

                <dt class="text-secondary">Database</dt>
                <dd><?= h(config('database.database', 'cbt_school')) ?></dd>

                <dt class="text-secondary">Charset</dt>
                <dd><?= h(config('database.charset', 'utf8mb4')) ?></dd>

                <dt class="text-secondary">Prefix</dt>
                <dd><?= h(config('database.prefix', '')) ?></dd>
            </dl>

            <hr style="margin: 1.5rem 0;">

            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="runMigration()">Jalankan Migrasi</button>
                <button type="button" class="btn btn-warning" onclick="runSeeder()">Jalankan Seeder</button>
            </div>
        </div>
    </div>

    <!-- Maintenance -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pemeliharaan</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <h4 style="margin-bottom: 0.5rem;">Clear Cache</h4>
                    <button type="button" class="btn btn-secondary" onclick="clearCache()">Bersihkan Cache</button>
                </div>

                <div>
                    <h4 style="margin-bottom: 0.5rem;">Log Files</h4>
                    <button type="button" class="btn btn-secondary" onclick="viewLogs()">Lihat Log</button>
                </div>

                <div>
                    <h4 style="margin-bottom: 0.5rem;">Backup Database</h4>
                    <button type="button" class="btn btn-primary" onclick="backupDatabase()">Backup Sekarang</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submissions (placeholder - would connect to actual settings API)
    ['generalSettingsForm', 'examSettingsForm', 'securitySettingsForm'].forEach(id => {
        const form = document.getElementById(id);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                toast('Pengaturan disimpan (demo)', 'success');
            });
        }
    });
});

function runMigration() {
    if (!confirm('Jalankan migrasi database?')) return;
    toast('Migrasi dijalankan (demo)', 'info');
}

function runSeeder() {
    if (!confirm('Jalankan seeder database?')) return;
    toast('Seeder dijalankan (demo)', 'info');
}

function clearCache() {
    toast('Cache dibersihkan (demo)', 'success');
}

function viewLogs() {
    toast('Log dibuka (demo)', 'info');
}

function backupDatabase() {
    toast('Backup dimulai (demo)', 'success');
}
</script>