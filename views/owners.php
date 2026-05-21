<?php
$view_mode = isset($_GET['edit']) ? 'edit' : ($_GET['view'] ?? 'list');
$owner_id = $_GET['id'] ?? $_GET['edit'] ?? '';
$owner = null;
if (($view_mode === 'detail' || $view_mode === 'edit') && $owner_id) {
    foreach ($owners as $o) if ($o['id'] === $owner_id) { $owner = $o; break; }
}

$status_options = [
    'new' => ['label' => 'Новая', 'class' => 'primary'],
    'review' => ['label' => 'На рассмотрении', 'class' => 'warning'],
    'approved' => ['label' => 'Одобрена', 'class' => 'success'],
    'rejected' => ['label' => 'Отклонена', 'class' => 'danger'],
    'adopted' => ['label' => 'Усыновление завершено', 'class' => 'info']
];
?>

<?php if ($view_mode === 'edit' && $owner): ?>
    <!-- 🔧 ФОРМА РЕДАКТИРОВАНИЯ -->
    <div class="mb-3">
        <a href="?action=owners&view=detail&id=<?= $owner['id'] ?>" class="btn btn-outline-secondary">← Отмена</a>
    </div>
    <div class="md-card p-4">
        <h4 class="fw-bold mb-3">✏️ Редактирование заявки</h4>
        <form method="POST" action="?action=update_owner">
            <input type="hidden" name="owner_id" value="<?= $owner['id'] ?>">
            <!-- Скрываем application_result, чтобы он не затерся при сохранении -->
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Кошка</label>
                    <select name="cat_id" class="form-select">
                        <option value="">-- Не выбрана --</option>
                        <?php foreach($cats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($owner['cat_id']??'')==$c['id']?'selected':'' ?>><?= escape($c['identification']['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <?php foreach($status_options as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($owner['status']??'')==$k?'selected':'' ?>><?= $v['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">ФИО</label><input type="text" name="name" class="form-control" value="<?= escape($owner['name']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Телефон</label><input type="text" name="phone" class="form-control" value="<?= escape($owner['phone']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Адрес</label><input type="text" name="address" class="form-control" value="<?= escape($owner['address']) ?>"></div>
                <div class="col-12"><label class="form-label">Заметки куратора</label><textarea name="notes" class="form-control" rows="3"><?= escape($owner['notes'] ?? '') ?></textarea></div>
            </div>
            <button type="submit" class="btn btn-success px-4">💾 Сохранить изменения</button>
        </form>
    </div>

<?php elseif ($view_mode === 'detail' && $owner): ?>
    <!-- 👁️ ПРОСМОТР АНКЕТЫ (без изменений из предыдущего ответа) -->
    <div class="mb-3">
        <a href="?action=owners" class="btn btn-outline-secondary">← Назад к списку</a>
        <a href="?action=owners&edit=<?= $owner['id'] ?>" class="btn btn-outline-primary ms-2">✏️ Редактировать</a>
    </div>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="md-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">📋 Анкета потенциального владельца</h4>
                        <small class="text-muted">Подана: <?= date('d.m.Y H:i', strtotime($owner['created_at'] ?? 'now')) ?></small>
                    </div>
                    <?php $st = $status_options[$owner['status']] ?? ['label' => $owner['status'], 'class' => 'secondary']; ?>
                    <span class="badge bg-<?= $st['class'] ?> fs-6 px-3 py-2"><?= $st['label'] ?></span>
                </div>
                <?php if (!empty($owner['cat_id'])): 
                    $cat = findCat($owner['cat_id'], $cats);
                ?>
                <div class="mb-4 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-2">🐱 Заявка на кошку:</h6>
                    <?php if ($cat): ?>
                        <a href="?action=cat&id=<?= $cat['id'] ?>" class="text-decoration-none fw-medium"><?= escape($cat['identification']['name']) ?></a>
                        <span class="text-muted">(<?= escape($cat['identification']['breed']) ?>, <?= escape($cat['identification']['age_group']) ?>)</span>
                    <?php else: ?><span class="text-muted">Кошка не найдена</span><?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">👤 Контактная информация</h6>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small d-block">ФИО</label><div class="fw-medium"><?= escape($owner['name'] ?? '—') ?></div></div>
                        <div class="col-md-6"><label class="text-muted small d-block">Телефон</label><div class="fw-medium"><?= escape($owner['phone'] ?? '—') ?></div></div>
                        <div class="col-md-6"><label class="text-muted small d-block">Email</label><div class="fw-medium"><?= escape($owner['email'] ?? '—') ?></div></div>
                        <div class="col-md-6"><label class="text-muted small d-block">Адрес</label><div class="fw-medium"><?= escape($owner['address'] ?? '—') ?></div></div>
                    </div>
                </div>
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">📝 Заполненная анкета (из формы)</h6>
                    <?php $app_data = $owner['application_result'] ?? null;
                    if (is_string($app_data)) { $app_data = json_decode($app_data, true); }
                    if (!$app_data || empty($app_data)): ?>
                        <div class="alert alert-light text-muted fst-italic">Данные анкеты отсутствуют</div>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded border" style="white-space: pre-line;"><?= nl2br(escape(formatApplicationResult($app_data))) ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($owner['notes'])): ?>
                <div class="mb-4"><h6 class="fw-bold mb-3 border-bottom pb-2">📝 Заметки куратора</h6><div class="p-3 bg-warning bg-opacity-10 rounded"><?= nl2br(escape($owner['notes'])) ?></div></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="md-card p-4 mb-3">
                <h6 class="fw-bold mb-3">⚡ Действия</h6>
                <div class="d-grid gap-2">
                    <a href="?action=owners&edit=<?= $owner['id'] ?>" class="btn btn-outline-primary">✏️ Редактировать</a>
                    <?php if ($owner['status'] !== 'adopted'): ?>
                    <form method="POST" action="?action=update_owner"><input type="hidden" name="owner_id" value="<?= $owner['id'] ?>"><input type="hidden" name="status" value="approved"><button type="submit" class="btn btn-success w-100">✓ Одобрить</button></form>
                    <form method="POST" action="?action=update_owner"><input type="hidden" name="owner_id" value="<?= $owner['id'] ?>"><input type="hidden" name="status" value="rejected"><button type="submit" class="btn btn-danger w-100">✗ Отклонить</button></form>
                    <?php endif; ?>
                    <hr><form method="POST" action="?action=delete_owner" onsubmit="return confirm('Удалить анкету?');"><input type="hidden" name="owner_id" value="<?= $owner['id'] ?>"><button type="submit" class="btn btn-outline-danger w-100">🗑️ Удалить</button></form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- 📋 СПИСОК ВЛАДЕЛЬЦЕВ -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">🏡 Владельцы / Анкеты</h4>
        <a href="?action=owners&add=1" class="btn btn-primary">+ Добавить</a>
    </div>
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
                <thead class="table-light"><tr><th>ФИО</th><th>Телефон</th><th>Кошка</th><th>Статус</th><th>Дата</th><th class="text-end">Действия</th></tr></thead>
                <tbody>
                    <?php $filtered_owners = $owners;
                    if (isset($_GET['status'])) $filtered_owners = array_filter($owners, fn($o) => $o['status'] === $_GET['status']);
                    if (empty($filtered_owners)): ?><tr><td colspan="6" class="text-center text-muted py-4">Нет записей</td></tr>
                    <?php else: foreach ($filtered_owners as $o): 
                        $cat = !empty($o['cat_id']) ? findCat($o['cat_id'], $cats) : null;
                        $st = $status_options[$o['status']] ?? ['label' => $o['status'], 'class' => 'secondary'];
                    ?>
                    <tr>
                        <td><div class="fw-medium"><?= escape($o['name']) ?></div><?php if (!empty($o['email'])): ?><small class="text-muted"><?= escape($o['email']) ?></small><?php endif; ?></td>
                        <td><?= escape($o['phone']) ?></td>
                        <td><?php if ($cat): ?><a href="?action=cat&id=<?= $cat['id'] ?>" class="text-decoration-none"><?= escape($cat['identification']['name']) ?></a><?php else: ?><span class="text-muted">Не выбрана</span><?php endif; ?></td>
                        <td><span class="badge bg-<?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                        <td><?= date('d.m.Y', strtotime($o['created_at'] ?? 'now')) ?></td>
                        <td class="text-end">
                            <a href="?action=owners&view=detail&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary" title="Просмотр">👁️</a>
                            <a href="?action=owners&edit=<?= $o['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Редактировать">✏️</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>