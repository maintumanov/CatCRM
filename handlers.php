<?php
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_GET['action'] ?? '';
    $users = loadData($FILES['users']);
    $cats  = loadData($FILES['cats']);
    $tasks = loadData($FILES['tasks']);
    $owners= loadData($FILES['owners']);
    $finances = loadData($FILES['finances']);

    // Авторизация
    if ($act === 'login') {
        $login_input = trim($_POST['login'] ?? '');
        $password_input = $_POST['password'] ?? '';
        $u = null;
        foreach ($users as $usr) if ($usr['login'] === $login_input) { $u = $usr; break; }
        if ($u && password_verify($password_input, $u['pass'])) {
            $_SESSION['user_id'] = $u['id'];
            if (!empty($_POST['remember'])) setcookie('cr_auth', $u['id'], time() + 30*86400, '/', '', false, true);
            redirect('?action=dashboard');
        }
        $login_err = "Неверный логин или пароль";
    }
    // Задачи
elseif ($act === 'add_task') {
    requireAuth();
    $is_general = !empty($_POST['is_general']);
    // Если задача общая, сохраняем [0]. Иначе массив выбранных ID
    $assignee_ids = $is_general ? [0] : array_map('intval', $_POST['assignee_ids'] ?? []);
    
    $tasks[] = [
        'id'=>uniqid('t_'), 'cat_id'=>$_POST['cat_id']?:null, 'type'=>$_POST['type'],
        'title'=>$_POST['title'], 'desc'=>$_POST['desc']?:'', 'status'=>'new',
        'assignee_ids'=>$assignee_ids, // Заменено на массив
        'creator_id'=>$GLOBALS['current_user']['id'],
        'due_date'=>$_POST['due_date'], 'created_at'=>date('Y-m-d H:i:s'), 'completed_at'=>null
    ];
    saveData($FILES['tasks'], $tasks); redirect('?action=my_tasks');
}

// Редактирование задачи (полное)
elseif ($act === 'update_task') {
    requireAuth();
    $tid = $_POST['task_id'] ?? '';
    foreach ($tasks as &$t) if ($t['id'] === $tid) {
        $t['title']    = $_POST['title']    ?? $t['title'];
        $t['desc']     = $_POST['desc']     ?? $t['desc'];
        $t['due_date'] = $_POST['due_date'] ?? $t['due_date'];
        
        // Логика исполнителей
        $is_general = !empty($_POST['is_general']);
        $t['assignee_ids'] = $is_general ? [0] : array_map('intval', $_POST['assignee_ids'] ?? []);
        
        // Логика статуса
        $old_status = $t['status']; 
        $t['status'] = $_POST['status'] ?? $t['status'];
        if ($t['status']==='done' && $old_status!=='done') $t['completed_at']=date('Y-m-d H:i:s');
        elseif ($t['status']!=='done') $t['completed_at']=null;
        
        break;
    }
    saveData($FILES['tasks'], $tasks); 
    redirect('?action=my_tasks');
}

// Удаление задачи (поддержка GET и POST)
elseif ($act === 'delete_task') {
    requireAuth();
    $tid = $_POST['task_id'] ?? $_GET['id'] ?? '';
    saveData($FILES['tasks'], array_values(array_filter($tasks, fn($t) => $t['id'] !== $tid)));
    redirect('?action=my_tasks');
}
    // Финансы
    elseif ($act === 'add_finance') { requireAuth(); $finances[]=['id'=>uniqid('f_'),'type'=>$_POST['type'],'category'=>$_POST['category'],'amount'=>(float)$_POST['amount'],'cat_id'=>$_POST['cat_id']?:null,'description'=>$_POST['description']??'','date'=>$_POST['date']?:date('Y-m-d'),'user_id'=>$GLOBALS['current_user']['id'],'created_at'=>date('Y-m-d H:i:s')]; saveData($FILES['finances'], $finances); redirect('?action=finances'); }
    elseif ($act === 'delete_finance') { requireAuth(); saveData($FILES['finances'], array_values(array_filter($finances, fn($f)=>$f['id']!==$_POST['finance_id']))); redirect('?action=finances'); }
    // Пользователи
    elseif ($act === 'add_user' || $act === 'edit_user') { requireAuth(); if ($GLOBALS['current_user']['role']!=='admin') redirect('?action=dashboard'); $id=(int)($_POST['user_id']??max(array_column($users,'id'))+1); $exists=false; foreach($users as &$u){ if($u['id']==$id){ $u['login']=$_POST['login']; $u['name']=$_POST['name']; $u['role']=$_POST['role']; if(!empty($_POST['password'])) $u['pass']=password_hash($_POST['password'],PASSWORD_DEFAULT); $exists=true; break; } } if(!$exists) $users[]=['id'=>$id,'login'=>$_POST['login'],'pass'=>password_hash($_POST['password'],PASSWORD_DEFAULT),'role'=>$_POST['role'],'name'=>$_POST['name']]; saveData($FILES['users'],$users); redirect('?action=users'); }
    elseif ($act === 'delete_user') { requireAuth(); if($GLOBALS['current_user']['role']!=='admin'||$GLOBALS['current_user']['id']==$_POST['user_id']) redirect('?action=users'); saveData($FILES['users'], array_values(array_filter($users, fn($u)=>$u['id']!=$_POST['user_id']))); redirect('?action=users'); }
    // Владельцы
	elseif ($act === 'add_owner' || $act === 'update_owner') {
    requireAuth();

    if ($act === 'update_owner') {
        $id = $_POST['owner_id'] ?? '';
        foreach ($owners as &$o) {
            if ($o['id'] === $id) {
                $o['cat_id']  = $_POST['cat_id']  ?: null;
                $o['name']    = $_POST['name'];
                $o['phone']   = $_POST['phone'];
                $o['address'] = $_POST['address'];
                $o['status']  = $_POST['status'];
                $o['notes']   = $_POST['notes'] ?? '';
                
                // application_result меняется только если явно прислали новое значение
                if (isset($_POST['application_result'])) {
                    $o['application_result'] = $_POST['application_result'];
                }
                
                $o['created_at'] ??= date('Y-m-d H:i:s');
                $o['created_by'] ??= $GLOBALS['current_user']['id'];
                 break;
            }
        }
    } else {
        // Создание новой записи (если админ добавляет вручную)
        $owners[] = [
            'id' => uniqid('o_'),
            'cat_id' => $_POST['cat_id'] ?: null,
            'name' => $_POST['name'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address'],
            'status' => $_POST['status'] ?? 'new',
            'application_result' => $_POST['application_result'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $GLOBALS['current_user']['id']
        ];
    }
    saveData($FILES['owners'], $owners);
    redirect('?action=owners');
	}
    elseif ($act === 'delete_owner') { requireAuth(); saveData($FILES['owners'], array_values(array_filter($owners, fn($o)=>$o['id']!==$_POST['owner_id']))); redirect('?action=owners'); }
    // Кошки
    elseif ($act === 'add_cat') { requireAuth(); $id='c_'.bin2hex(random_bytes(4)); $tpl=newCatTemplate($id); $tpl['identification']=array_merge($tpl['identification'],['name'=>$_POST['name'],'color'=>$_POST['color'],'sex'=>$_POST['sex'],'age_group'=>$_POST['age_group'],'approx_age'=>$_POST['approx_age'],'intake_date'=>$_POST['intake_date']]); $cats[]=$tpl; saveData($FILES['cats'],$cats); redirect('?action=cat&id='.$id); }
    elseif (strpos($act, 'update_cat_')===0 || strpos($act,'add_cat_')===0) { requireAuth(); $cid=$_POST['cat_id']; $cat=findCat($cid,$cats); if(!$cat) redirect('?action=cats');
        if($act==='update_cat_ident') $cat['identification']=array_merge($cat['identification'],['name'=>$_POST['name'],'sex'=>$_POST['sex'],'age_group'=>$_POST['age_group'],'approx_age'=>$_POST['approx_age'],'breed'=>$_POST['breed'],'color'=>$_POST['color'],'marks'=>$_POST['marks'],'description'=>$_POST['description'],'intake_date'=>$_POST['intake_date'],'location'=>$_POST['location'],'microchip'=>$_POST['microchip']]);
        elseif($act==='update_cat_status'){ if($cat['status_history']['current']!==$_POST['new_status']){ $cat['status_history']['current']=$_POST['new_status']; $cat['status_history']['log'][]=['status'=>$_POST['new_status'],'date'=>date('Y-m-d H:i:s'),'user_id'=>$GLOBALS['current_user']['id'],'comment'=>$_POST['comment']]; } }
        elseif($act==='add_cat_photo'){ if(!empty($_FILES['photo']['tmp_name'])){ $dir="$UPLOAD_DIR/$cid"; if(!is_dir($dir))mkdir($dir,0755,true); $ext=strtolower(pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION)); if(in_array($ext,['jpg','jpeg','png','webp','gif'])){ $fname='photo_'.time().mt_rand(10,99).'.'.$ext; if(move_uploaded_file($_FILES['photo']['tmp_name'],"$dir/$fname")) $cat['photos'][]=['path'=>"uploads/$cid/$fname",'caption'=>$_POST['caption'],'is_main'=>!empty($_POST['is_main']),'uploaded_at'=>date('Y-m-d H:i:s')]; } } }
        elseif($act==='add_cat_procedure') $cat['medical']['procedures'][]=['type'=>$_POST['type'],'name'=>$_POST['drug_name'],'date'=>$_POST['date'],'clinic'=>$_POST['clinic'],'notes'=>$_POST['notes']];
        elseif($act==='update_cat_sterilization') $cat['medical']['sterilization']=['done'=>$_POST['done']=='1','date'=>$_POST['date'],'notes'=>$_POST['notes']];
        elseif($act==='add_cat_med') $cat['medical']['current_meds'][]=['drug'=>$_POST['drug'],'dosage'=>$_POST['dosage'],'start'=>$_POST['start'],'end'=>$_POST['end']];
        elseif($act==='update_cat_behavior') $cat['behavior']=array_map('trim',$_POST);
        elseif($act==='update_cat_contacts') $cat['contacts']=['curator_user_id'=>$_POST['curator_id']?:null,'curator_name'=>$_POST['curator_name'],'curator_phone'=>$_POST['curator_phone'],'curator_email'=>$_POST['curator_email'],'finder_name'=>$_POST['finder_name'],'finder_contacts'=>$_POST['finder_contacts']];
        elseif($act==='add_cat_note') $cat['notes'][]=['user_id'=>$GLOBALS['current_user']['id'],'date'=>date('Y-m-d H:i:s'),'text'=>$_POST['text']];
        elseif($act==='update_adoption') $cat['adoption']=['status'=>$_POST['status'],'owner_name'=>$_POST['owner_name'],'owner_contacts'=>$_POST['owner_contacts'],'owner_user_id'=>$_POST['owner_id']?:null,'homecheck_date'=>$_POST['hc_date'],'homecheck_result'=>$_POST['hc_result'],'contract_date'=>$_POST['contract'],'transfer_date'=>$_POST['transfer']];
        foreach($cats as &$c) if($c['id']==$cid){$c=$cat;break;} saveData($FILES['cats'],$cats); redirect('?action=cat&id='.$cid);
    }
    // Конструктор
    elseif ($act === 'save_form_config') {
    requireAuth();
    if ($GLOBALS['current_user']['role'] !== 'admin') redirect('?action=dashboard');
    
    $json = $_POST['config_json'] ?? '[]';
    $decoded = json_decode($json, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        // Явное сохранение с флагом JSON_UNESCAPED_UNICODE предотвращает экранирование кириллицы
        file_put_contents($FILES['form_config'], json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        $_SESSION['msg'] = "Конфигурация анкеты успешно сохранена";
    } else {
        $_SESSION['err'] = "Ошибка JSON: " . json_last_error_msg();
    }
    redirect('?action=form_settings');
}
}