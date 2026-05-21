<?php 
$mn=['','Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь']; 
$pm=$calendar_month==1?12:$calendar_month-1; $py=$calendar_month==1?$calendar_year-1:$calendar_year; 
$nm=$calendar_month==12?1:$calendar_month+1; $ny=$calendar_month==12?$calendar_year+1:$calendar_year; 
?>
<style>
.calendar-day { min-height: 85px; }
.calendar-task { transition: 0.15s; cursor: pointer; }
.calendar-task:hover { filter: brightness(0.92); transform: translateY(-1px); }
.calendar-task a:hover { text-decoration: underline; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold m-0">📅 Календарь задач</h3>
    <div class="d-flex gap-2">
        <a href="?action=calendar&year=<?= $py ?>&month=<?= $pm ?>" class="btn btn-outline-primary md-btn"><span class="material-icons">chevron_left</span> Назад</a>
        <span class="align-self-center fw-bold"><?= $mn[$calendar_month] ?> <?= $calendar_year ?></span>
        <a href="?action=calendar&year=<?= $ny ?>&month=<?= $nm ?>" class="btn btn-outline-primary md-btn">Вперёд <span class="material-icons">chevron_right</span></a>
    </div>
</div>

<div class="md-card p-3">
    <div class="row g-0 text-center fw-bold border-bottom bg-light py-2">
        <div class="col">Пн</div><div class="col">Вт</div><div class="col">Ср</div><div class="col">Чт</div><div class="col">Пт</div><div class="col">Сб</div><div class="col">Вс</div>
    </div>
    <div class="row g-0">
        <?php $day=1; $td=$days_in_month; $cdw=$first_day; for($w=0;$w<6;$w++): if($day>$td)break; ?>
        <div class="col-12 row g-0 border-bottom">
        <?php for($d=1;$d<=7;$d++): 
            $is_t = ($day==date('j') && $calendar_month==date('n') && $calendar_year==date('Y')); 
            $cd = sprintf('%04d-%02d-%02d', $calendar_year, $calendar_month, $day); 
            $dt = array_filter($tasks, fn($t) => substr($t['due_date'] ?? '', 0, 10) === $cd); 
        ?>
            <?php if($d<$cdw && $w==0): ?>
                <div class="col calendar-day other-month bg-light-subtle"></div>
            <?php elseif($day>$td): ?>
                <div class="col calendar-day other-month bg-light-subtle"></div>
            <?php else: ?>
                <div class="col calendar-day <?= $is_t ? 'bg-primary bg-opacity-10' : '' ?> p-1">
                    <div class="fw-bold mb-1 <?= $is_t ? 'text-primary' : '' ?>"><?= $day ?></div>
                    <?php foreach($dt as $tk): 
                        $c = findCat($tk['cat_id'] ?? '', $cats);
                        $cat_name = $c['identification']['name'] ?? 'Общая';
                        $type_lbl = $tk['type'] === 'event' ? '📅 Мероприятие' : (defined('TASK_TYPES') ? (TASK_TYPES[$tk['type']] ?? $tk['type']) : $tk['type']);
                        $cl = $tk['status']=='done'?'success':($tk['status']=='in_progress'?'warning':'primary');
                    ?>
                    <div class="calendar-task bg-<?= $cl ?> bg-opacity-75 mb-1 px-1 py-1 rounded small">
                        <a href="?action=task&id=<?= $tk['id'] ?>" class="text-decoration-none text-dark fw-medium d-block w-100 text-truncate" title="Редактировать задачу">
                            <?= escape($cat_name) ?>: <?= escape($tk['title']) ?>
                        </a>
                        <div class="text-muted fst-italic text-truncate" style="font-size:0.7rem; line-height:1.1"><?= escape($type_lbl) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php $day++; $cdw=1; ?>
            <?php endif; ?>
        <?php endfor; ?>
        </div>
        <?php endfor; ?>
    </div>
</div>