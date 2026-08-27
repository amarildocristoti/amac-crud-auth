<div class="mb-4">
    <h3 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h3>
    <p class="text-muted mb-0">Olá, <?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?>!</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-3"
                     style="width:52px;height:52px;">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">Total de Usuários</p>
                    <h3 class="mb-0"><?= (int) $totalUsers ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center me-3"
                     style="width:52px;height:52px;">
                    <i class="bi bi-person-plus-fill fs-4"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">Cadastrados Hoje</p>
                    <h3 class="mb-0"><?= (int) $todayUsers ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center me-3"
                     style="width:52px;height:52px;">
                    <i class="bi bi-calendar-month-fill fs-4"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">Cadastrados este Mês</p>
                    <h3 class="mb-0"><?= (int) $monthUsers ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Últimos usuários cadastrados</span>
        <a href="<?= APP_URL ?>/users" class="btn btn-sm btn-outline-primary">
            Ver todos <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($latestUsers)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox display-4"></i>
                <p class="mt-2 mb-0">Nenhum usuário cadastrado ainda.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <tbody>
                        <?php foreach ($latestUsers as $user): ?>
                            <tr>
                                <td class="ps-3" style="width:1%;">
                                    <span class="badge rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td class="text-end pe-3 text-muted small">
                                    <?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
