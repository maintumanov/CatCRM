<?php
$sorted_tasks = array_values($my_tasks);
usort($sorted_tasks, fn($a, $b) => strcmp($a['due_date'] ?? '', $b['due_date'] ?? ''));
$status_map = [
    'new' => ['label'=>'Новая', 'class'=>'primary'],
    'in_progress' => ['label'=>'В работе', 'class'=>'warning'],
    'done' => ['label'=>'Выполнена', 'class'=>'success'],
    'cancelled' => ['label'=>'Отменена', 'class'=>'secondary']
];
?>
<div class="md-card p-4 h-100">
    <h5 class="fw-bold mb-3">📋 Мои задачи</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Кошка</th>
                    <th>Тип</th>
                    <th>Название</th>
                    <th>Статус</th>
                    <th>Срок</th>
                    <th class="text-end">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sorted_tasks)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">У вас нет задач</td></tr>
                <?php else: foreach ($sorted_tasks as $t): 
                    $c = findCat($t['cat_id'] ?? '', $cats);
                    $cat_name = $c['identification']['name'] ?? 'Общая';
                    $type_label = defined('TASK_TYPES') ? (TASK_TYPES[$t['type']] ?? $t['type']) : $t['type'];
                    $st = $status_map[$t['status']] ?? ['label' => $t['status'], 'class' => 'secondary'];
                ?>
                <tr>
                    <td><?= escape($cat_name) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= escape($type_label) ?></span></td>
                    <td><?= escape($t['title']) ?></td>
                    <td><span class="badge bg-<?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                    <td><?= escape($t['due_date']) ?></td>
                    <td class="text-end">
                        <a href="?action=task&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Редактировать">✏️</a>
                        <form method="POST" action="?action=delete_task" class="d-inline" onsubmit="return confirm('Удалить задачу?');">
                            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>