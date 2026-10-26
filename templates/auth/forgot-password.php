<?php
View::layout('auth');
$title = 'Lupa Kata Sandi';
$subtitle = 'Masukkan email Anda untuk menerima tautan reset kata sandi';
?>

<form action="<?= url('forgot-password') ?>" method="POST" novalidate>
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email" class="form-label">Alamat Email</label>
        <div class="input-wrapper">
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input type="email" name="email" id="email" class="form-input" placeholder="Masukkan email terdaftar" value="<?= h(old('email')) ?>" required autocomplete="email" autofocus>
        </div>
        <?php if (has_flash('email')): ?>
            <div class="form-error"><?= get_flash('email') ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Kirim Tautan Reset</button>
</form>

<div class="auth-footer">
    <p>Ingat kata sandi? <a href="<?= url('login') ?>">Kembali ke Login</a></p>
</div>