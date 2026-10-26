<?php
View::layout('auth');
$title = 'Masuk';
$subtitle = 'Masuk ke akun Anda untuk mengakses sistem CBT';
?>

<form action="<?= url('login') ?>" method="POST" novalidate>
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email" class="form-label">Email / Username</label>
        <div class="input-wrapper">
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.4"/></svg>
            <input type="text" name="email" id="email" class="form-input" placeholder="Masukkan email atau username" value="<?= h(old('email')) ?>" required autocomplete="username" autofocus>
        </div>
        <?php if (has_flash('email')): ?>
            <div class="form-error"><?= get_flash('email') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Kata Sandi</label>
        <div class="input-wrapper">
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" name="password" id="password" class="form-input" placeholder="Masukkan kata sandi" required autocomplete="current-password">
            <button type="button" class="toggle-password" aria-label="Tampilkan/ sembunyikan kata sandi">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>
        <?php if (has_flash('password')): ?>
            <div class="form-error"><?= get_flash('password') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-row">
        <label class="form-check">
            <input type="checkbox" name="remember" class="form-check-input" value="1" <?= old('remember') ? 'checked' : '' ?>>
            <span class="form-check-label">Ingat saya</span>
        </label>
        <a href="<?= url('forgot-password') ?>" class="forgot-link">Lupa kata sandi?</a>
    </div>

    <button type="submit" class="btn btn-primary">Masuk</button>
</form>

<div class="auth-footer">
    <p>Belum punya akun? <a href="<?= url('register') ?>">Daftar di sini</a></p>
</div>

<script>
    // Auto-focus
    document.getElementById('email')?.focus();
</script>