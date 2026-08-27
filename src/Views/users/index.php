<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0"><i class="bi bi-people-fill me-2"></i>Usuários</h3>
        <p class="text-muted mb-0">
            <?= (int) $total ?> usuário<?= $total == 1 ? '' : 's' ?> cadastrado<?= $total == 1 ? '' : 's' ?>
        </p>
    </div>
    <a href="<?= APP_URL ?>/users/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo Usuário
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($users)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox display-4"></i>
                <p class="mt-2 mb-0">Nenhum usuário cadastrado ainda.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Criado em</th>
                            <th class="text-end pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="ps-3 text-muted">#<?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        </span>
                                        <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted"><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end pe-3">
                                    <a href="<?= APP_URL ?>/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form action="<?= APP_URL ?>/users/delete/<?= $user['id'] ?>" method="POST"
                                          class="d-inline js-delete-form"
                                          data-name="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Security::generateCsrfToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($lastPage > 1): ?>
        <div class="card-footer bg-white border-top-0 py-3">
            <nav aria-label="Paginação de usuários">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= APP_URL ?>/users?page=<?= $currentPage - 1 ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= APP_URL ?>/users?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= APP_URL ?>/users?page=<?= $currentPage + 1 ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const name = form.dataset.name;

            Swal.fire({
                title: 'Remover usuário?',
                html: 'Tem certeza que deseja excluir <strong>' + name + '</strong>? Esta ação não pode ser desfeita.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-trash me-1"></i> Sim, excluir',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
