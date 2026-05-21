<?php
$tid = $_GET['id'] ?? '';
$task = null;
foreach ($tasks as $t) { if ($t['id'] === $tid) { $task = $t; break; } }
if (!$task) { echo '<div class="alert alert-danger">Задача не найдена</div>'; echo '<a href="?action=my_tasks" class="btn btn-primary">Назад</a>'; return; }

$c = findCat($task['cat_id'] ?? '', $cats);
$cat_name = $c['identification']['name'] ?? 'Общая';

// Поддержка обратной совместимости: старые задачи хранят один ID в 'assignee_id'
$assignee_ids = $task['assignee_ids'] ?? (isset($task['assignee_id']) ? [(int)$task['assignee_id']] : []);
$is_general = in_array(0, array_map('intval', $assignee_ids), true);

$type_label = defined('TASK_TYPES') ? (TASK_TYPES[$task['type']] ?? $task['type']) : $task['type'];
if ($task['type'] === 'event') $type_label = '📅 Мероприятие';
?>
<div class="md-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Редактирование задачи</h4>
        <a href="?action=my_tasks" class="btn btn-outline-secondary">← Назад к списку</a>
    </div>
    <form method="POST" action="?action=update_task">
        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label text-muted small">Кошка</label>
                <input type="text" class="form-control" value="<?= escape($cat_name) ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small">Тип</label>
                <div class="form-control-plaintext fw-medium"><?= escape($type_label) ?></div>
            </div>
            <div class="col-12">
                <label class="form-label">Название <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= escape($task['title']) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label">Описание</label>
                <textarea name="desc" class="form-control" rows="3"><?= escape($task['desc']) ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Срок выполнения</label>
                <input type="date" name="due_date" class="form-control" value="<?= $task['due_date'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Статус</label>
                <select name="status" class="form-select">
                    <option value="new" <?= $task['status']=='new'?'selected':'' ?>>Новая</option>
                    <option value="in_progress" <?= $task['status']=='in_progress'?'selected':'' ?>>В работе</option>
                    <option value="done" <?= $task['status']=='done'?'selected':'' ?>>Выполнена</option>
                    <option value="cancelled" <?= $task['status']=='cancelled'?'selected':'' ?>>Отменена</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label mb-2">Исполнители</label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="is_general" name="is_general" value="1" <?= $is_general ? 'checked' : '' ?> onchange="toggleAssignees(this.checked)">
                    <label class="form-check-label fw-medium" for="is_general">Общая задача</label>
                </div>
                <select name="assignee_ids[]" class="form-select" id="assignee_select" multiple size="4" <?= $is_general ? 'disabled' : '' ?>>
                    <?php foreach($users as $u): 
                        $selected = in_array((int)$u['id'], array_map('intval', $assignee_ids), true) ? 'selected' : '';
                    ?>
                    <option value="<?= $u['id'] ?>" <?= $selected ?>><?= escape($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">💡 Удерживайте <kbd>Ctrl</kbd> для выбора нескольких</small>
            </div>
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-success px-4">💾 Сохранить изменения</button>
            </div>
        </div>
    </form>
</div>

<script>
function toggleAssignees(isGeneral) {
    const sel = document.getElementById('assignee_select');
    sel.disabled = isGeneral;
    if (isGeneral) { for (let o of sel.options) o.selected = false; }
}
</script>