<?php
$view_mode = $_GET['view'] ?? 'list';
$owner_id = $_GET['id'] ?? '';
$owner = null;
if ($view_mode === 'detail' && $owner_id) {
    foreach ($owners as $o) {
        if ($o['id'] === $owner_id) {
            $owner = $o;
            break;
        }
    }
}

$status_options = [
    'new' => ['label' => 'Новая', 'class' => 'primary'],
    'review' => ['label' => 'На рассмотрении', 'class' => 'warning'],
    'approved' => ['label' => 'Одобрена', 'class' => 'success'],
    'rejected' => ['label' => 'Отклонена', 'class' => 'danger'],
    'adopted' => ['label' => 'Усыновление завершено', 'class' => 'info']
];
?>

<?php if ($view_mode === 'detail' && $owner): ?>
    <!-- Просмотр анкеты -->
    <div class="mb-3">
        <a href="?action=owners" class="btn btn-outline-secondary">← Назад к списку</a>
    </div>
    
    <div class="row g-4">
        <!-- Основная информация -->
        <div class="col-md-8">
            <div class="md-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">📋 Анкета потенциального владельца</h4>
                        <small class="text-muted">Подана: <?= date('d.m.Y H:i', strtotime($owner['created_at'])) ?></small>
                    </div>
                    <?php 
                    $st = $status_options[$owner['status']] ?? ['label' => $owner['status'], 'class' => 'secondary'];
                    ?>
                    <span class="badge bg-<?= $st['class'] ?> fs-6 px-3 py-2"><?= $st['label'] ?></span>
                </div>

                <!-- Информация о кошке -->
                <?php if (!empty($owner['cat_id'])): 
                    $cat = findCat($owner['cat_id'], $cats);
                ?>
                <div class="mb-4 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-2">🐱 Кошка:</h6>
                    <?php if ($cat): ?>
                        <a href="?action=cat&id=<?= $cat['id'] ?>" class="text-decoration-none">
                            <strong><?= escape($cat['identification']['name']) ?></strong>
                        </a>
                        <span class="text-muted">(<?= escape($cat['identification']['breed']) ?>, <?= escape($cat['identification']['age_group']) ?>)</span>
                    <?php else: ?>
                        <span class="text-muted">Кошка не найдена</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Данные анкеты -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">👤 Контактная информация</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small d-block">ФИО</label>
                            <div class="fw-medium"><?= escape($owner['name'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Телефон</label>
                            <div class="fw-medium"><?= escape($owner['phone'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Email</label>
                            <div class="fw-medium"><?= escape($owner['email'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Адрес</label>
                            <div class="fw-medium"><?= escape($owner['address'] ?? '—') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Условия содержания -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">🏠 Условия содержания</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Тип жилья</label>
                            <div><?= escape($owner['housing_type'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Площадь</label>
                            <div><?= escape($owner['housing_area'] ?? '—') ?></div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small d-block">Есть ли другие животные</label>
                            <div><?= escape($owner['other_pets'] ?? '—') ?></div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small d-block">Опыт содержания кошек</label>
                            <div><?= nl2br(escape($owner['experience'] ?? '—')) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Мотивация -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">💭 Мотивация и пожелания</h6>
                    <div>
                        <label class="text-muted small d-block">Почему хотят взять кошку</label>
                        <div class="p-3 bg-light-subtle rounded"><?= nl2br(escape($owner['motivation'] ?? '—')) ?></div>
                    </div>
                </div>

                <!-- Дополнительные заметки -->
                <?php if (!empty($owner['notes'])): ?>
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">📝 Заметки</h6>
                    <div class="p-3 bg-warning bg-opacity-10 rounded"><?= nl2br(escape($owner['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Боковая панель с действиями -->
        <div class="col-md-4">
            <div class="md-card p-4 mb-3">
                <h6 class="fw-bold mb-3">⚡ Действия</h6>
                <div class="d-grid gap-2">
                    <a href="?action=owners&edit=<?= $owner['id'] ?>" class="btn btn-outline-primary">✏️ Редактировать</a>
                    <?php if ($owner['status'] !== 'adopted'): ?>
                    <form method="POST" action="?action=update_owner" class="d-grid gap-2">
                        <input type="hidden" name="owner_id" value="<?= $owner['id'] ?>">
                        <input type="hidden" name="cat_id" value="<?= $owner['cat_id'] ?? '' ?>">
                        <input type="hidden" name="name" value="<?= $owner['name'] ?? '' ?>">
                        <input type="hidden" name="phone" value="<?= $owner['phone'] ?? '' ?>">
                        <input type="hidden" name="address" value="<?= $owner['address'] ?? '' ?>">
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn btn-success">✓ Одобрить</button>
                    </form>
                    <form method="POST" action="?action=update_owner" class="d-grid gap-2">
                        <input type="hidden" name="owner_id" value="<?= $owner['id'] ?>">
                        <input type="hidden" name="cat_id" value="<?= $owner['cat_id'] ?? '' ?>">
                        <input type="hidden" name="name" value="<?= $owner['name'] ?? '' ?>">
                        <input type="hidden" name="phone" value="<?= $owner['phone'] ?? '' ?>">
                        <input type="hidden" name="address" value="<?= $owner['address'] ?? '' ?>">
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-danger">✗ Отклонить</button>
                    </form>
                    <?php endif; ?>
                    <hr>
                    <form method="POST" action="?action=delete_owner" onsubmit="return confirm('Удалить анкету?');">
                        <input type="hidden" name="owner_id" value="<?= $owner['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger w-100">🗑️ Удалить</button>
                    </form>
                </div>
            </div>

            <!-- История изменений -->
            <?php if (!empty($owner['status_history'])): ?>
            <div class="md-card p-4">
                <h6 class="fw-bold mb-3">📜 История статусов</h6>
                <ul class="list-unstyled mb-0 small">
                    <?php foreach ($owner['status_history'] as $h): ?>
                    <li class="mb-2 pb-2 border-bottom">
                        <div class="fw-medium"><?= $status_options[$h['status']] ?? $h['status'] ?></div>
                        <small class="text-muted"><?= date('d.m.Y H:i', strtotime($h['date'])) ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Список владельцев -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">🏡 Владельцы / Анкеты</h4>
        <a href="?action=owners&add=1" class="btn btn-primary">+ Добавить</a>
    </div>

    <!-- Фильтры по статусам -->
    <div class="mb-3">
        <div class="btn-group" role="group">
            <a href="?action=owners" class="btn btn-sm <?= !isset($_GET['status']) ? 'btn-primary' : 'btn-outline-primary' ?>">Все</a>
            <?php foreach ($status_options as $key => $opt): ?>
                <a href="?action=owners&status=<?= $key ?>" class="btn btn-sm btn-outline-primary"><?= $opt['label'] ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="md-card p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ФИО</th>
                        <th>Телефон</th>
                        <th>Кошка</th>
                        <th>Статус</th>
                        <th>Дата подачи</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $filtered_owners = $owners;
                    if (isset($_GET['status'])) {
                        $filtered_owners = array_filter($owners, fn($o) => $o['status'] === $_GET['status']);
                    }
                    
                    if (empty($filtered_owners)): 
                    ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Нет записей</td>
                    </tr>
                    <?php else: 
                        foreach ($filtered_owners as $o): 
                            $cat = !empty($o['cat_id']) ? findCat($o['cat_id'], $cats) : null;
                            $st = $status_options[$o['status']] ?? ['label' => $o['status'], 'class' => 'secondary'];
                    ?>
                    <tr>
                        <td>
                            <div class="fw-medium"><?= escape($o['name']) ?></div>
                            <?php if (!empty($o['email'])): ?>
                                <small class="text-muted"><?= escape($o['email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= escape($o['phone']) ?></td>
                        <td>
                            <?php if ($cat): ?>
                                <a href="?action=cat&id=<?= $cat['id'] ?>" class="text-decoration-none">
                                    <?= escape($cat['identification']['name']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Не выбрана</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-<?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                        <td><?= date('d.m.Y', strtotime($o['created_at'])) ?></td>
                        <td class="text-end">
                            <a href="?action=owners&view=detail&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary" title="Просмотр анкеты">👁️</a>
                            <a href="?action=owners&edit=<?= $o['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Редактировать">✏️</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>