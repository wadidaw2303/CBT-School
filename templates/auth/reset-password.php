<?php
View::layout('auth');
$title = 'Reset Kata Sandi';
$subtitle = 'Masukkan kata sandi baru Anda';
?>

<form action="<?= url('reset-password') ?>" method="POST" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="email" value="<?= h($email) ?>">
    <input type="hidden" name="token" value="<?= h($token) ?>">

    <div class="form-group">
        <label for="password" class="form-label">Kata Sandi Baru</label>
        <div class="input-wrapper">
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" name="password" id="password" class="form-input" placeholder="Minimal 8 karakter" required autocomplete="new-password" minlength="8" autofocus>
            <button type="button" class="toggle-password" aria-label="Tampilkan/ sembunyikan kata sandi">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>
        <?php if (has_flash('password')): ?>
            <div class="form-error"><?= get_flash('password') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
        <div class="input-wrapper">
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" placeholder="Ulangi kata sandi baru" required autocomplete="new-password" minlength="8">
            <button type="button" class="toggle-password" aria-label="Tampilkan/ sembunyikan kata sandi">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>
        <?php if (has_flash('password_confirmation')): ?>
            <div class="form-error"><?= get_flash('password_confirmation') ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Reset Kata Sandi</button>
</form>

<div class="auth-footer">
    <p>Kembali ke <a href="<?= url('login') ?>">Login</a></p>
</div>