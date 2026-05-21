<!-- Модалки -->
<?php if (!empty($current_user)): ?>
<!-- Add Finance Modal -->
<div class="modal fade no-print" id="addFinanceModal" tabindex="-1">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" action="?action=add_finance">
<div class="modal-header"><h5 class="modal-title">Финансовая операция</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label class="form-label small fw-medium">Тип</label><select name="type" id="financeType" class="form-select md-field" onchange="updateFinanceCategories()"><option value="expense">Расход</option><option value="income">Доход</option></select></div>
<div class="mb-3"><label class="form-label small fw-medium">Категория</label><select name="category" id="financeCategory" class="form-select md-field"></select></div>
<div class="mb-3"><label class="form-label small fw-medium">Кошка</label><select name="cat_id" class="form-select md-field"><option value="">-- Общие --</option><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>"><?= escape($c['identification']['name']) ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label small fw-medium">Сумма</label><input type="number" name="amount" step="0.01" class="form-control md-field" required></div>
<div class="mb-3"><label class="form-label small fw-medium">Дата</label><input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control md-field" required></div>
<div class="mb-3"><label class="form-label small fw-medium">Описание</label><textarea name="description" class="form-control md-field"></textarea></div>
</div><div class="modal-footer"><button class="btn btn-primary">Сохранить</button></div>
</form></div></div></div>

<!-- Add/Edit Cats/Owners/Users (Упрощенно для экономии места, добавьте остальные из оригинала по аналогии) -->
<div class="modal fade no-print" id="addCatModal"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="?action=add_cat"><div class="modal-header"><h5>Новая кошка</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="text" name="name" class="form-control mb-2" placeholder="Кличка" required><input type="text" name="color" class="form-control mb-2" placeholder="Окрас"><select name="sex" class="form-select mb-2"><option value="male">Кот</option><option value="female">Кошка</option></select><input type="text" name="approx_age" class="form-control mb-2" placeholder="Возраст"><input type="date" name="intake_date" class="form-control"></div><div class="modal-footer"><button class="btn btn-primary">Создать</button></div></form></div></div></div>

<div class="modal fade no-print" id="addUserModal"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="?action=add_user"><div class="modal-header"><h5>Пользователь</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="text" name="login" class="form-control mb-2" placeholder="Логин" required><input type="password" name="password" class="form-control mb-2" placeholder="Пароль" required><input type="text" name="name" class="form-control mb-2" placeholder="Имя" required><select name="role" class="form-select"><option value="user">Пользователь</option><option value="admin">Админ</option></select></div><div class="modal-footer"><button class="btn btn-primary">Создать</button></div></form></div></div></div>

<div class="modal fade no-print" id="editUserModal"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="?action=edit_user"><div class="modal-header"><h5>Редактировать</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="user_id" id="edit_id"><input type="text" name="login" id="edit_login" class="form-control mb-2" required><input type="password" name="password" class="form-control mb-2" placeholder="Новый пароль"><input type="text" name="name" id="edit_name" class="form-control mb-2" required><select name="role" id="edit_role" class="form-select"><option value="user">Пользователь</option><option value="admin">Админ</option></select></div><div class="modal-footer"><button class="btn btn-primary">Сохранить</button></div></form></div></div></div>

<div class="modal fade no-print" id="addOwnerModal"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="?action=add_owner"><div class="modal-header"><h5>Владелец</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
<select name="cat_id" class="form-select mb-2"><option value="">-- Не выбрана --</option><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>"><?= escape($c['identification']['name']) ?></option><?php endforeach; ?></select>
<input type="text" name="name" class="form-control mb-2" placeholder="Имя" required>
<input type="tel" name="phone" class="form-control mb-2" placeholder="Телефон" required>
<textarea name="address" class="form-control mb-2" placeholder="Адрес" required></textarea>
<select name="status" class="form-select mb-2"><?php foreach(OWNER_STATUSES as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select>
<textarea name="application_result" class="form-control mb-2" placeholder="Результат анкеты"></textarea>
<textarea name="notes" class="form-control" placeholder="Заметки"></textarea>
</div><div class="modal-footer"><button class="btn btn-primary">Добавить</button></div></form></div></div></div>

<div class="modal fade no-print" id="editOwnerModal"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="?action=update_owner"><div class="modal-header"><h5>Редактировать</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="owner_id" id="edit_owner_id">
<select name="cat_id" id="edit_owner_cat" class="form-select mb-2"><option value="">-- Не выбрана --</option><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>"><?= escape($c['identification']['name']) ?></option><?php endforeach; ?></select>
<input type="text" name="name" id="edit_owner_name" class="form-control mb-2" required>
<input type="tel" name="phone" id="edit_owner_phone" class="form-control mb-2" required>
<textarea name="address" id="edit_owner_address" class="form-control mb-2" required></textarea>
<select name="status" id="edit_owner_status" class="form-select mb-2"><?php foreach(OWNER_STATUSES as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select>
<textarea name="application_result" id="edit_owner_result" class="form-control mb-2"></textarea>
<textarea name="notes" id="edit_owner_notes" class="form-control"></textarea>
</div><div class="modal-footer"><button class="btn btn-primary">Сохранить</button></div></form></div></div></div>

<script>
const financeCategories = <?= json_encode(FINANCE_TYPES) ?>;
function updateFinanceCategories() {
    const type = document.getElementById('financeType').value;
    const cat = document.getElementById('financeCategory');
    cat.innerHTML = '';
    for (const [k, v] of Object.entries(financeCategories[type])) {
        const o = document.createElement('option'); o.value = k; o.textContent = v; cat.appendChild(o);
    }
}
document.addEventListener('DOMContentLoaded', function() {
    updateFinanceCategories();
    document.getElementById('editUserModal').addEventListener('show.bs.modal', function(e) {
        const b=e.relatedTarget; document.getElementById('edit_id').value=b.dataset.id; document.getElementById('edit_login').value=b.dataset.login; document.getElementById('edit_name').value=b.dataset.name; document.getElementById('edit_role').value=b.dataset.role;
    });
    document.getElementById('editOwnerModal').addEventListener('show.bs.modal', function(e) {
        const b=e.relatedTarget; document.getElementById('edit_owner_id').value=b.dataset.id; document.getElementById('edit_owner_cat').value=b.dataset.cat; document.getElementById('edit_owner_name').value=b.dataset.name; document.getElementById('edit_owner_phone').value=b.dataset.phone; document.getElementById('edit_owner_address').value=b.dataset.address; document.getElementById('edit_owner_status').value=b.dataset.status; document.getElementById('edit_owner_result').value=b.dataset.result; document.getElementById('edit_owner_notes').value=b.dataset.notes;
    });
    const addFin = document.getElementById('addFinanceModal');
    if(addFin) addFin.addEventListener('show.bs.modal', e => { const id=e.relatedTarget?.dataset?.catId; if(id) document.querySelector('#addFinanceModal select[name="cat_id"]').value=id; });
});
</script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>