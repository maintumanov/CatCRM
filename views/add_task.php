<div class="md-card p-4">
    <h4 class="fw-bold mb-4">➕ Новая задача</h4>
    <form method="POST" action="?action=add_task">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Кошка</label>
                <select name="cat_id" class="form-select">
                    <option value="">-- Общая / Не привязана к кошке --</option>
                    <?php foreach($cats as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= escape($c['identification']['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Тип задачи <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    <?php 
                    $types = defined('TASK_TYPES') ? TASK_TYPES : [];
                    foreach($types as $key => $label): ?>
                    <option value="<?= $key ?>"><?= escape($label) ?></option>
                    <?php endforeach; ?>
                    <option value="event">📅 Мероприятие</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Название <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="Краткое название задачи" required>
            </div>
            <div class="col-12">
                <label class="form-label">Описание</label>
                <textarea name="desc" class="form-control" rows="3" placeholder="Детали, инструкции, заметки..."></textarea>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Исполнитель(и)</label>
                <div class="mb-2 form-check">
                    <input type="checkbox" class="form-check-input" id="is_general" name="is_general" value="1" onchange="toggleAssignees(this.checked)">
                    <label class="form-check-label fw-medium" for="is_general">Общая задача (видна всем волонтёрам)</label>
                </div>
                <select name="assignee_ids[]" class="form-select" id="assignee_select" multiple size="4">
                    <?php foreach($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= escape($u['name']) ?> (<?= escape($u['login']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">💡 Удерживайте <kbd>Ctrl</kbd> (или <kbd>Cmd</kbd>) для выбора нескольких</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Срок выполнения <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control" required>
            </div>
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-success px-4">💾 Создать задачу</button>
                <a href="?action=my_tasks" class="btn btn-outline-secondary ms-2">Отмена</a>
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