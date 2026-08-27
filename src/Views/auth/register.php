<div class="auth-wrapper">
    <div class="card" style="max-width: 460px; width: 100%;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus display-4 text-primary"></i>
                <h3 class="mt-2 mb-0">Criar Conta</h3>
                <p class="text-muted">Preencha os dados abaixo</p>
            </div>

            <form method="POST" action="<?= APP_URL ?>/register">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="name" class="form-control" placeholder="Seu nome completo" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirmar Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password_confirm" class="form-control" placeholder="Repita a senha" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-check-circle me-1"></i> Cadastrar
                </button>
            </form>

            <p class="text-center text-muted mt-4 mb-0">
                Já tem conta? <a href="<?= APP_URL ?>/login" class="text-decoration-none fw-semibold">Entrar</a>
            </p>
        </div>
    </div>
</div>
