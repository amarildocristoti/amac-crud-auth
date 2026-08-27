<div class="auth-wrapper">
    <div class="card" style="max-width: 420px; width: 100%;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock display-4 text-primary"></i>
                <h3 class="mt-2 mb-0">Bem-vindo de volta</h3>
                <p class="text-muted">Entre com sua conta</p>
            </div>

            <form method="POST" action="<?= APP_URL ?>/login">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="seu@email.com" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="sua senha" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                </button>
            </form>

            <p class="text-center text-muted mt-4 mb-0">
                Não tem conta? <a href="<?= APP_URL ?>/register" class="text-decoration-none fw-semibold">Cadastre-se</a>
            </p>
        </div>
    </div>
</div>
