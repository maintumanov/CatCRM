<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'handlers.php';

$action = $_GET['action'] ?? 'login';
if ($action === 'logout') { session_destroy(); setcookie('cr_auth','',time()-3600,'/'); redirect('?action=login'); }
if ($action !== 'login') requireAuth();

$current_user = $GLOBALS['current_user'] ?? [];
$users = loadData($FILES['users']);
$cats  = loadData($FILES['cats']);
$tasks = loadData($FILES['tasks']);
$owners= loadData($FILES['owners']);
$form_config = loadData($FILES['form_config']);
$finances = loadData($FILES['finances']);
$uid = $current_user['id'] ?? 0;
$my_tasks = array_filter($tasks, function($t) use ($uid) {
    $ids = $t['assignee_ids'] ?? [$t['assignee_id'] ?? 0]; // Фоллбэк для старых задач
    return ($ids[0] == 0) || in_array($uid, array_map('intval', $ids), true) || ($t['creator_id']??0) == $uid;
});

$calendar_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$calendar_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$days_in_month = (int)date('t', mktime(0, 0, 0, $calendar_month, 1, $calendar_year));
$first_day = (int)date('N', mktime(0, 0, 0, $calendar_month, 1, $calendar_year));

require 'includes/header.php';
if ($action !== 'login') require 'includes/nav.php';

echo '<div class="container">';
if ($action === 'login' || empty($_SESSION['user_id'])) {
    require 'views/login.php';
} else {
    switch($action) {
        case 'dashboard': require 'views/dashboard.php'; break;
        case 'tasks': require 'views/tasks.php'; break;
        case 'my_tasks': require 'views/my_tasks.php'; break;
        case 'calendar': require 'views/calendar.php'; break;
        case 'cats': require 'views/cats.php'; break;
        case 'cat': require 'views/cat_detail.php'; break;
        case 'owners': require 'views/owners.php'; break;
        case 'finances': require 'views/finances.php'; break;
        case 'add_task': require 'views/add_task.php'; break;
        case 'task': require 'views/task_edit.php'; break;
        case 'users': require 'views/users.php'; break;
        case 'form_settings': require 'views/form_settings.php'; break;
        default: echo '<div class="md-card p-5 text-center"><a href="?action=dashboard" class="btn btn-primary md-btn px-4">Вернуться</a></div>';
    }
}
echo '</div>';

require 'includes/footer.php';