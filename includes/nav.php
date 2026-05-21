<ul class="nav nav-tabs md-tabs mb-4 no-print">
<li class="nav-item"><a class="nav-link <?= $action==='dashboard'?'active':'' ?>" href="?action=dashboard"><span class="material-icons align-middle me-1" style="font-size:18px">dashboard</span> Дашборд</a></li>
<li class="nav-item"><a class="nav-link <?= $action==='tasks'?'active':'' ?>" href="?action=tasks"><span class="material-icons align-middle me-1" style="font-size:18px">list_alt</span> Задачи</a></li>
<li class="nav-item"><a class="nav-link <?= $action==='my_tasks'?'active':'' ?>" href="?action=my_tasks"><span class="material-icons align-middle me-1" style="font-size:18px">assignment_ind</span> Мои задачи</a></li>
<li class="nav-item"><a class="nav-link <?= $action==='calendar'?'active':'' ?>" href="?action=calendar"><span class="material-icons align-middle me-1" style="font-size:18px">calendar_today</span> Календарь</a></li>
<li class="nav-item"><a class="nav-link <?= $action==='cats'?'active':'' ?>" href="?action=cats"><span class="material-icons align-middle me-1" style="font-size:18px">pets</span> Кошки</a></li>
<li class="nav-item"><a class="nav-link <?= $action==='owners'?'active':'' ?>" href="?action=owners"><span class="material-icons align-middle me-1" style="font-size:18px">people_alt</span> Владельцы</a></li>
<li class="nav-item"><a class="nav-link <?= $action==='finances'?'active':'' ?>" href="?action=finances"><span class="material-icons align-middle me-1" style="font-size:18px">account_balance_wallet</span> Финансы</a></li>
<li class="nav-item"><a class="nav-link <?= $action==='add_task'?'active':'' ?>" href="?action=add_task"><span class="material-icons align-middle me-1" style="font-size:18px">add_task</span> + Задача</a></li>
<?php if ($current_user['role']==='admin'): ?>
<li class="nav-item"><a class="nav-link <?= $action==='form_settings'?'active':'' ?>" href="?action=form_settings"><span class="material-icons align-middle me-1" style="font-size:18px">build</span> Конструктор</a></li>
<li class="nav-item"><a class="nav-link <?= $action==='users'?'active':'' ?>" href="?action=users"><span class="material-icons align-middle me-1" style="font-size:18px">people</span> Пользователи</a></li>
<?php endif; ?>
</ul>