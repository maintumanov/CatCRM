<?php $stats = getStats($cats, $tasks); $total_tasks_active = $stats['tasks_new'] + $stats['tasks_prog']; ?>
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="md-card p-3 text-center h-100"><h6 class="text-muted text-uppercase small fw-bold">Всего кошек</h6><h2 class="fw-bold text-primary mb-0"><?= $stats['total_cats'] ?></h2></div></div>
    <div class="col-md-3"><div class="md-card p-3 text-center h-100"><h6 class="text-muted text-uppercase small fw-bold">Ищут дом</h6><h2 class="fw-bold text-success mb-0"><?= $stats['ready_for_adoption'] ?? 0 ?></h2></div></div>
    <div class="col-md-3"><div class="md-card p-3 text-center h-100"><h6 class="text-muted text-uppercase small fw-bold">На лечении</h6><h2 class="fw-bold text-warning mb-0"><?= $stats['treatment'] ?></h2></div></div>
    <div class="col-md-3"><div class="md-card p-3 text-center h-100"><h6 class="text-muted text-uppercase small fw-bold">Активные задачи</h6><h2 class="fw-bold text-info mb-0"><?= $total_tasks_active ?></h2></div></div>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="md-card p-4 h-100"><h5 class="fw-bold mb-3">Статусы кошек</h5>
            <?php foreach(['caught'=>['label'=>'На отлове','color'=>'#1565c0'],'treatment'=>['label'=>'Лечение','color'=>'#e65100'],'ready_for_adoption'=>['label'=>'Готовы к дому','color'=>'#2e7d32'],'adopted'=>['label'=>'Пристроены','color'=>'#7b1fa2']] as $k=>$d): $c=$stats[$k] ?? 0; $p=$stats['total_cats']>0?($c/$stats['total_cats'])*100:0; ?>
            <div class="mb-2"><div class="d-flex justify-content-between small"><span><?= $d['label'] ?></span><span class="fw-bold"><?= $c ?></span></div><div class="stat-bar"><div class="stat-fill" style="width:<?= $p ?>%;background:<?= $d['color'] ?>"></div></div></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="md-card p-4 h-100"><h5 class="fw-bold mb-3">Задачи</h5>
            <div class="d-flex justify-content-around text-center mb-4"><div><div class="h4 fw-bold text-secondary mb-0"><?= $stats['tasks_new'] ?></div><small class="text-muted">Новые</small></div><div><div class="h4 fw-bold text-warning mb-0"><?= $stats['tasks_prog'] ?></div><small class="text-muted">В работе</small></div><div><div class="h4 fw-bold text-success mb-0"><?= $stats['tasks_done'] ?></div><small class="text-muted">Выполнено</small></div></div>
            <h6 class="small fw-bold text-muted mb-2">Мои ближайшие задачи</h6>
            <ul class="list-group list-group-flush small">
                <?php $sm=array_values($my_tasks); usort($sm,fn($a,$b)=>strcmp($a['due_date'],$b['due_date'])); $sh=0; foreach($sm as $t): if($t['status']=='done')continue; if($sh>=5)break; $c=findCat($t['cat_id'],$cats); ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0"><div><span class="badge bg-light text-dark border me-2"><?= TASK_TYPES[$t['type']] ?></span><?= escape($c['identification']['name']??'Общая') ?>: <?= escape($t['title']) ?></div><span class="text-muted"><?= $t['due_date'] ?></span></li>
                <?php $sh++; endforeach; if($sh==0)echo "<li class='list-group-item text-muted px-0'>Нет активных задач</li>"; ?>
            </ul>
        </div>
    </div>
</div>