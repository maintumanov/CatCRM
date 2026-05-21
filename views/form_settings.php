<?php 
$configPath = $FILES['form_config']; 
$currentConfig = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [];
if (!is_array($currentConfig)) $currentConfig = [];
?>
<div class="row justify-content-center">
<div class="col-12">
<h3 class="fw-bold mb-3">⚙️ Конструктор анкеты</h3>

<div class="alert alert-info small mb-3">
<strong>Важно:</strong> Поля name, phone, address обязательны в любой анкете. Используйте drag-and-drop для изменения порядка полей.
</div>

<?php if(!empty($_SESSION['msg'])): echo "<div class='alert alert-success'>".$_SESSION['msg']."</div>"; unset($_SESSION['msg']); endif; ?>
<?php if(!empty($_SESSION['err'])): echo "<div class='alert alert-danger'>".$_SESSION['err']."</div>"; unset($_SESSION['err']); endif; ?>

<div class="row">
<!-- Панель добавления полей -->
<div class="col-md-4">
<div class="md-card p-3 mb-3">
<h5 class="fw-bold mb-3">📋 Добавить поле</h5>
<form id="addFieldForm">
<div class="mb-2">
<label class="form-label small">Тип поля</label>
<select class="form-select form-select-sm" id="fieldType" required>
<option value="text">Текст (однострочный)</option>
<option value="textarea">Текст (многострочный)</option>
<option value="select">Выпадающий список</option>
<option value="radio">Радио-кнопки</option>
<option value="checkbox">Чекбокс</option>
<option value="date">Дата</option>
<option value="number">Число</option>
<option value="email">Email</option>
<option value="tel">Телефон</option>
</select>
</div>
<div class="mb-2">
<label class="form-label small">ID поля (латиница)</label>
<input type="text" class="form-control form-control-sm" id="fieldId" placeholder="example_field" required pattern="[a-z_][a-z0-9_]*">
</div>
<div class="mb-2">
<label class="form-label small">Название поля</label>
<input type="text" class="form-control form-control-sm" id="fieldLabel" placeholder="Например: Ваш возраст" required>
</div>
<div class="mb-2" id="optionsContainer" style="display:none;">
<label class="form-label small">Варианты ответов (каждый с новой строки)</label>
<textarea class="form-control form-control-sm" id="fieldOptions" rows="4" placeholder="Вариант 1&#10;Вариант 2&#10;Вариант 3"></textarea>
</div>
<div class="form-check mb-3">
<input type="checkbox" class="form-check-input" id="fieldRequired">
<label class="form-check-label small">Обязательное поле</label>
</div>
<button type="submit" class="btn btn-primary btn-sm w-100">➕ Добавить поле</button>
</form>
</div>

<div class="md-card p-3">
<h6 class="fw-bold mb-2">💡 Подсказки</h6>
<ul class="small text-muted mb-0" style="padding-left: 1.2rem;">
<li>Перетаскивайте поля мышкой для изменения порядка</li>
<li>Нажмите 🗑️ для удаления поля</li>
<li>Нажмите ✏️ для редактирования</li>
<li>ID поля должен быть уникальным</li>
<li>Для select/radio укажите варианты ответов</li>
</ul>
</div>
</div>

<!-- Список полей с drag-and-drop -->
<div class="col-md-8">
<div class="md-card p-3 mb-3">
<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="fw-bold mb-0">📝 Текущие поля анкеты</h5>
<span class="badge bg-primary" id="fieldsCount"><?= count($currentConfig) ?> полей</span>
</div>
<div id="fieldsList" class="sortable-list">
<?php foreach ($currentConfig as $index => $field): ?>
<div class="field-item card mb-2 border" data-index="<?= $index ?>">
<div class="card-body py-2 px-3 d-flex align-items-center">
<span class="drag-handle me-3 cursor-move" title="Перетащить">☰</span>
<div class="flex-grow-1">
<strong><?= escape($field['label']) ?></strong>
<small class="text-muted ms-2">(<?= escape($field['type']) ?>)</small>
<?php if(!empty($field['required'])): ?>
<span class="badge bg-danger ms-1">обяз.</span>
<?php endif; ?>
<?php if($field['type'] === 'select' || $field['type'] === 'radio'): ?>
<small class="d-block text-muted mt-1">Варианты: <?= implode(', ', array_map('escape', $field['options'] ?? [])) ?></small>
<?php endif; ?>
</div>
<div class="btn-group btn-group-sm">
<button class="btn btn-outline-primary btn-edit-field" title="Редактировать">✏️</button>
<button class="btn btn-outline-danger btn-delete-field" title="Удалить">🗑️</button>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Форма сохранения -->
<form method="POST" action="?action=save_form_config" id="saveForm">
<input type="hidden" name="config_json" id="configJsonInput" value='<?= htmlspecialchars(json_encode($currentConfig, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>'>
<div class="d-flex justify-content-end gap-2">
<button type="button" class="btn btn-secondary md-btn" onclick="exportConfig()">📤 Экспорт JSON</button>
<button type="button" class="btn btn-info md-btn text-white" onclick="document.getElementById('importFile').click()">📥 Импорт JSON</button>
<input type="file" id="importFile" style="display:none;" accept=".json" onchange="importConfig(this)">
<button type="submit" class="btn btn-primary md-btn px-4">💾 Сохранить конфигурацию</button>
</div>
</form>
</div>
</div>

<!-- Модальное окно предпросмотра -->
<div class="md-card p-3 mt-3">
<h5 class="fw-bold mb-3">👁️ Предпросмотр анкеты</h5>
<div id="formPreview" class="p-3 bg-light rounded">
<em class="text-muted">Добавьте поля, чтобы увидеть предпросмотр</em>
</div>
</div>

</div>
</div>

<style>
.sortable-list { min-height: 100px; }
.field-item { background: #fff; transition: all 0.2s; user-select: none; }
.field-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.drag-handle { cursor: grab; font-size: 1.2rem; color: #6c757d; user-select: none; }
.drag-handle:active { cursor: grabbing; }
.sortable-list .field-item.dragging { opacity: 0.4; background: #e9ecef; border: 2px dashed #0d6efd !important; }
.sortable-list .field-item.over { border: 2px dashed #0d6efd; background: #f0f7ff; }
.cursor-move { cursor: grab; }
</style>

<script>
let fields = <?= json_encode($currentConfig, JSON_UNESCAPED_UNICODE) ?>;

// Показать/скрыть поле вариантов для select/radio
document.getElementById('fieldType').addEventListener('change', function() {
const optsContainer = document.getElementById('optionsContainer');
optsContainer.style.display = (this.value === 'select' || this.value === 'radio') ? 'block' : 'none';
});

// Добавление поля
document.getElementById('addFieldForm').addEventListener('submit', function(e) {
e.preventDefault();
const type = document.getElementById('fieldType').value;
const id = document.getElementById('fieldId').value.trim();
const label = document.getElementById('fieldLabel').value.trim();
const required = document.getElementById('fieldRequired').checked;

// Проверка уникальности ID
if (fields.some(f => f.id === id)) {
alert('Поле с таким ID уже существует!');
return;
}

const newField = { id, label, type, required };
if (type === 'select' || type === 'radio') {
const optionsText = document.getElementById('fieldOptions').value.trim();
newField.options = optionsText ? optionsText.split('\n').map(s => s.trim()).filter(s => s) : ['Вариант 1', 'Вариант 2'];
}

fields.push(newField);
renderFields();
updatePreview();
this.reset();
document.getElementById('optionsContainer').style.display = 'none';
});

// Рендеринг списка полей
function renderFields() {
const container = document.getElementById('fieldsList');
document.getElementById('fieldsCount').textContent = fields.length + ' полей';
document.getElementById('configJsonInput').value = JSON.stringify(fields, null, 2);

if (fields.length === 0) {
container.innerHTML = '<div class="text-center text-muted py-4"><em>Список полей пуст. Добавьте первое поле!</em></div>';
return;
}

container.innerHTML = fields.map((field, index) => `
<div class="field-item card mb-2 border" data-index="${index}" draggable="true">
<div class="card-body py-2 px-3 d-flex align-items-center">
<span class="drag-handle me-3 cursor-move" title="Перетащить">☰</span>
<div class="flex-grow-1">
<strong>${escapeHtml(field.label)}</strong>
<small class="text-muted ms-2">(${field.type})</small>
${field.required ? '<span class="badge bg-danger ms-1">обяз.</span>' : ''}
${(field.type === 'select' || field.type === 'radio') && field.options ? 
`<small class="d-block text-muted mt-1">Варианты: ${field.options.map(escapeHtml).join(', ')}</small>` : ''}
</div>
<div class="btn-group btn-group-sm">
<button class="btn btn-outline-primary btn-edit-field" title="Редактировать" onclick="editField(${index})">✏️</button>
<button class="btn btn-outline-danger btn-delete-field" title="Удалить" onclick="deleteField(${index})">🗑️</button>
</div>
</div>
</div>
`).join('');

initSortable();
}

// Инициализация drag-and-drop
function initSortable() {
const list = document.getElementById('fieldsList');
let draggedItem = null;
let draggedIndex = null;

list.addEventListener('dragstart', function(e) {
draggedItem = e.target.closest('.field-item');
if (draggedItem) {
draggedIndex = parseInt(draggedItem.dataset.index);
setTimeout(() => draggedItem.classList.add('dragging'), 0);
e.dataTransfer.effectAllowed = 'move';
e.dataTransfer.setData('text/plain', draggedIndex);
}
});

list.addEventListener('dragend', function(e) {
if (draggedItem) {
draggedItem.classList.remove('dragging');
draggedItem = null;
draggedIndex = null;
// Снимаем классы over со всех элементов
list.querySelectorAll('.field-item').forEach(item => item.classList.remove('over'));
}
});

list.addEventListener('dragover', function(e) {
e.preventDefault();
e.dataTransfer.dropEffect = 'move';
const afterElement = getDragAfterElement(list, e.clientY);
const draggable = document.querySelector('.dragging');
if (!draggable) return;

if (afterElement == null) {
list.appendChild(draggable);
} else {
list.insertBefore(draggable, afterElement);
}
});

list.addEventListener('dragenter', function(e) {
const item = e.target.closest('.field-item');
if (item && !item.classList.contains('dragging')) {
item.classList.add('over');
}
});

list.addEventListener('dragleave', function(e) {
const item = e.target.closest('.field-item');
if (item) {
item.classList.remove('over');
}
});

list.addEventListener('drop', function(e) {
e.preventDefault();
const droppedItem = document.querySelector('.dragging');
if (!droppedItem) return;

// Получаем новый порядок из DOM
const items = list.querySelectorAll('.field-item');
const newFields = [];
items.forEach(item => {
const oldIndex = parseInt(item.dataset.index);
newFields.push(fields[oldIndex]);
});

fields = newFields;
renderFields();
updatePreview();
});
}

function getDragAfterElement(container, y) {
const draggableElements = [...container.querySelectorAll('.field-item:not(.dragging)')];
return draggableElements.reduce((closest, child) => {
const box = child.getBoundingClientRect();
const offset = y - box.top - box.height / 2;
if (offset < 0 && offset > closest.offset) {
return { offset: offset, element: child };
} else {
return closest;
}
}, { offset: Number.NEGATIVE_INFINITY }).element;
}

// Удаление поля
function deleteField(index) {
if (confirm('Удалить это поле?')) {
fields.splice(index, 1);
renderFields();
updatePreview();
}
}

// Редактирование поля
function editField(index) {
const field = fields[index];
document.getElementById('fieldType').value = field.type;
document.getElementById('fieldType').dispatchEvent(new Event('change'));
document.getElementById('fieldId').value = field.id;
document.getElementById('fieldLabel').value = field.label;
document.getElementById('fieldRequired').checked = field.required || false;
if (field.options) {
document.getElementById('fieldOptions').value = field.options.join('\n');
}
document.getElementById('addFieldForm').querySelector('button[type="submit"]').textContent = '✏️ Обновить поле';
document.getElementById('addFieldForm').onsubmit = function(e) {
e.preventDefault();
const type = document.getElementById('fieldType').value;
const id = document.getElementById('fieldId').value.trim();
const label = document.getElementById('fieldLabel').value.trim();
const required = document.getElementById('fieldRequired').checked;

if (id !== field.id && fields.some(f => f.id === id)) {
alert('Поле с таким ID уже существует!');
return;
}

fields[index] = { id, label, type, required };
if (type === 'select' || type === 'radio') {
const optionsText = document.getElementById('fieldOptions').value.trim();
fields[index].options = optionsText ? optionsText.split('\n').map(s => s.trim()).filter(s => s) : ['Вариант 1', 'Вариант 2'];
}

renderFields();
updatePreview();
this.reset();
this.querySelector('button[type="submit"]').textContent = '➕ Добавить поле';
this.onsubmit = null;
document.getElementById('optionsContainer').style.display = 'none';
};
window.scrollTo(0, 0);
}

// Предпросмотр формы
function updatePreview() {
const preview = document.getElementById('formPreview');
if (fields.length === 0) {
preview.innerHTML = '<em class="text-muted">Добавьте поля, чтобы увидеть предпросмотр</em>';
return;
}

let html = '<form class="row g-3">';
fields.forEach(field => {
html += `<div class="col-md-6">`;
html += `<label class="form-label">${escapeHtml(field.label)}${field.required ? ' <span class="text-danger">*</span>' : ''}</label>`;

if (field.type === 'textarea') {
html += `<textarea class="form-control" rows="3" ${field.required ? 'required' : ''}></textarea>`;
} else if (field.type === 'select') {
html += `<select class="form-select" ${field.required ? 'required' : ''}>`;
html += `<option value="">-- Выберите --</option>`;
(field.options || []).forEach(opt => {
html += `<option value="${escapeHtml(opt)}">${escapeHtml(opt)}</option>`;
});
html += `</select>`;
} else if (field.type === 'radio') {
(field.options || []).forEach((opt, i) => {
html += `<div class="form-check">`;
html += `<input class="form-check-input" type="radio" name="${field.id}" id="${field.id}_${i}" ${field.required && i===0 ? 'required' : ''}>`;
html += `<label class="form-check-label" for="${field.id}_${i}">${escapeHtml(opt)}</label>`;
html += `</div>`;
});
} else if (field.type === 'checkbox') {
html += `<div class="form-check">`;
html += `<input class="form-check-input" type="checkbox" id="${field.id}">`;
html += `<label class="form-check-label" for="${field.id}">${escapeHtml(field.label)}</label>`;
html += `</div>`;
} else {
html += `<input type="${field.type}" class="form-control" ${field.required ? 'required' : ''}>`;
}

html += `</div>`;
});
html += `<div class="col-12"><button type="submit" class="btn btn-primary" disabled>Отправить заявку (предпросмотр)</button></div>`;
html += `</form>`;

preview.innerHTML = html;
}

// Экспорт конфигурации
function exportConfig() {
const dataStr = JSON.stringify(fields, null, 2);
const blob = new Blob([dataStr], {type: 'application/json'});
const url = URL.createObjectURL(blob);
const a = document.createElement('a');
a.href = url;
a.download = 'form_config_' + new Date().toISOString().slice(0,10) + '.json';
a.click();
URL.revokeObjectURL(url);
}

// Импорт конфигурации
function importConfig(input) {
const file = input.files[0];
if (!file) return;
const reader = new FileReader();
reader.onload = function(e) {
try {
const imported = JSON.parse(e.target.result);
if (!Array.isArray(imported)) throw new Error('Неверный формат');
if (confirm('Импортировать конфигурацию? Текущие поля будут заменены.')) {
fields = imported;
renderFields();
updatePreview();
}
} catch (err) {
alert('Ошибка импорта: ' + err.message);
}
};
reader.readAsText(file);
input.value = '';
}

// Утилита для экранирования HTML
function escapeHtml(text) {
const div = document.createElement('div');
div.textContent = text;
return div.innerHTML;
}

// Инициализация при загрузке
renderFields();
updatePreview();
</script>