<h3 class="fw-bold mb-3">🌐 Канбан-доска задач</h3>
<div class="row g-3">
    <?php foreach(['new'=>'primary','in_progress'=>'warning','done'=>'success','cancelled'=>'secondary'] as $st=>$col): ?>
    <div class="col-md-3"><div class="md-card h-100">
        <div class="px-3 py-2 bg-<?= $col ?> bg-opacity-10 text-<?= $col ?> fw-bold rounded-top"><?= STATUS_LABELS[$st] ?> <span class="badge bg-<?= $col ?> bg-opacity-75"><?= count(array_filter($tasks,fn($t)=>$t['status']==$st)) ?></span></div>
        <div class="p-2">
            <?php foreach(array_filter($tasks,fn($t)=>$t['status']==$st) as $t): $c=findCat($t['cat_id'],$cats); $a=findUser($t['assignee_id'],$users); ?>
            <div class="p-2 mb-2 bg-light rounded shadow-sm small">
                <div class="fw-semibold"><?= TASK_TYPES[$t['type']] ?></div><div class="text-muted"><?= $c ? escape($c['identification']['name']) : 'Общая' ?></div>
                <div class="mt-1 d-flex justify-content-between"><span>👤 <?= escape($a['name']) ?></span><span class="text-muted"><?= $t['due_date'] ?></span></div>
            </div><?php endforeach; ?>
        </div>
    </div></div>
    <?php endforeach; ?>
</div>