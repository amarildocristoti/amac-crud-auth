<div class="d-flex align-items-center mb-4">
    <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Usuário</h3>
        <p class="text-muted mb-0">Atualize os dados do usuário</p>
    </div>
</div>

<div class="card" style="max-width: 560px;">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/users/update/<?= $user['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Atualizar
                </button>
                <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
