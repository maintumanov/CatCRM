<?php if (!empty($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?= escape($_SESSION['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error']); endif; ?>

<div class="row justify-content-center mt-5">
<div class="col-md-5">
<div class="md-card p-4 shadow-lg">
    <h3 class="mb-1 fw-bold">Вход в систему</h3>
    <p class="text-muted mb-4">Авторизуйтесь для работы с карточками и задачами</p>
    <!-- Важно: action="?action=login" отправляет POST в index.php, где его ловит handlers.php -->
    <form method="POST" action="?action=login">
        <div class="mb-3">
            <label class="form-label small fw-medium">Логин</label>
            <input type="text" name="login" class="form-control md-field" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-medium">Пароль</label>
            <input type="password" name="password" class="form-control md-field" required>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="remember" id="rem" class="form-check-input">
            <label class="form-check-label small" for="rem">Запомнить меня (30 дней)</label>
        </div>
        <button class="btn btn-primary w-100 md-btn py-2">Войти</button>
    </form>
    <small class="text-muted mt-3 d-block text-center">Демо: admin / 123 • user / 123</small>
</div>
</div>
</div>