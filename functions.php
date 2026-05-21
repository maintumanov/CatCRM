<?php
function loadData($f) { clearstatcache(true, $f); return json_decode(file_get_contents($f), true) ?: []; }
function saveData($f, $d) { file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX); }
function escape($s) { return htmlspecialchars(trim($s ?? ''), ENT_QUOTES, 'UTF-8'); }
function redirect($u) { header("Location: $u"); exit; }
function findUser($id, $users) { return array_reduce($users, fn($c, $u) => $u['id'] == $id ? $u : $c, null); }
function findCat($id, $cats) { return array_reduce($cats, fn($c, $v) => $v['id'] == $id ? $v : $c, null); }

function requireAuth() {
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['cr_auth'])) $_SESSION['user_id'] = (int)$_COOKIE['cr_auth'];
    if (!isset($_SESSION['user_id'])) redirect('?action=login');
    global $FILES;
    $GLOBALS['current_user'] = findUser($_SESSION['user_id'], loadData($GLOBALS['FILES']['users']));
    if (!$GLOBALS['current_user']) { session_destroy(); setcookie('cr_auth','',time()-3600,'/'); redirect('?action=login'); }
}

function newCatTemplate($id) {
    return [
        'id' => $id,
        'identification' => ['name'=>'Без клички','species'=>'cat','sex'=>'unknown','age_group'=>'unknown','approx_age'=>'','breed'=>'domestic','color'=>'','marks'=>'','description'=>'','intake_date'=>date('Y-m-d'),'location'=>'','microchip'=>''],
        'photos' => [],
        'status_history' => ['current'=>'caught','log'=>[['status'=>'caught','date'=>date('Y-m-d H:i:s'),'user_id'=>0,'comment'=>'Создание карточки']]],
        'medical' => ['procedures'=>[],'sterilization'=>['done'=>false,'date'=>'','notes'=>''],'current_meds'=>[],'lab_results'=>[]],
        'behavior' => ['people_relation'=>'','animals_relation'=>'','litter_box'=>'','independence'=>'','special_needs'=>''],
        'contacts' => ['curator_user_id'=>null,'curator_name'=>'','curator_phone'=>'','curator_email'=>'','finder_name'=>'','finder_contacts'=>''],
        'notes' => [],
        'adoption' => ['status'=>'searching','owner_name'=>'','owner_contacts'=>'','owner_user_id'=>null,'homecheck_date'=>'','homecheck_result'=>'','contract_date'=>'','transfer_date'=>'']
    ];
}

function getCatMainPhoto($cat) { foreach($cat['photos'] as $p) if($p['is_main']) return $p; return !empty($cat['photos']) ? $cat['photos'][0] : null; }
function getStats($cats, $tasks) {
    $stats = ['total_cats'=>count($cats),'adopted'=>0,'ready_for_adoption'=>0,'treatment'=>0,'caught'=>0,'tasks_new'=>0,'tasks_prog'=>0,'tasks_done'=>0];
    foreach($cats as $c) { $st=$c['status_history']['current']; if(isset($stats[$st])) $stats[$st]++; }
    foreach($tasks as $t) { if($t['status']=='new') $stats['tasks_new']++; elseif($t['status']=='in_progress') $stats['tasks_prog']++; elseif($t['status']=='done') $stats['tasks_done']++; }
    return $stats;
}
function getFinanceStats($finances, $cats) {
    $stats=['total_income'=>0,'total_expense'=>0,'balance'=>0,'by_cat'=>[],'by_category'=>[]];
    foreach($cats as $cat) $stats['by_cat'][$cat['id']] = ['name'=>$cat['identification']['name'],'income'=>0,'expense'=>0];
    foreach($finances as $f) {
        $amount=(float)$f['amount'];
        if($f['type']=='income'){ $stats['total_income']+=$amount; if(!empty($f['cat_id'])&&isset($stats['by_cat'][$f['cat_id']])) $stats['by_cat'][$f['cat_id']]['income']+=$amount; }
        else{ $stats['total_expense']+=$amount; if(!empty($f['cat_id'])&&isset($stats['by_cat'][$f['cat_id']])) $stats['by_cat'][$f['cat_id']]['expense']+=$amount; }
        $k=$f['type'].'_'.$f['category']; if(!isset($stats['by_category'][$k])) $stats['by_category'][$k]=['type'=>$f['type'],'category'=>$f['category'],'label'=>FINANCE_TYPES[$f['type']][$f['category']]??$f['category'],'amount'=>0];
        $stats['by_category'][$k]['amount']+=$amount;
    }
    $stats['balance']=$stats['total_income']-$stats['total_expense'];
    return $stats;
}

function formatApplicationResult($data) {
    if (empty($data)) return '';
    if (is_string($data)) return $data;
    if (is_array($data)) {
        $lines = [];
        $configFile = __DIR__ . '/data/form_config.json';
        $config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
        foreach ($data as $key => $value) {
            if (empty($value)) continue;
            $label = $key;
            if (is_array($config)) {
                foreach ($config as $field) { if ($field['id'] === $key) { $label = $field['label']; break; } }
            }
            if (is_array($value)) $value = implode(', ', $value);
            $lines[] = "$label: $value";
        }
        return implode("\n", $lines);
    }
    return print_r($data, true);
}