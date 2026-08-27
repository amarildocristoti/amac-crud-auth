<div class="d-flex align-items-center mb-4">
    <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="mb-0"><i class="bi bi-person-plus me-2"></i>Novo Usuário</h3>
        <p class="text-muted mb-0">Preencha os dados para cadastrar</p>
    </div>
</div>

<div class="card" style="max-width: 560px;">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/users/store">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" class="form-control" placeholder="Nome completo" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="email@exemplo.com" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Salvar
                </button>
                <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
