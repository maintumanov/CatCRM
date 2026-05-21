<?php
$DATA_DIR = __DIR__ . '/data';
$UPLOAD_DIR = __DIR__ . '/uploads';
if (!is_dir($DATA_DIR)) mkdir($DATA_DIR, 0755, true);
if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);

$FILES = [
    'users'       => $DATA_DIR . '/users.json',
    'cats'        => $DATA_DIR . '/cats.json',
    'tasks'       => $DATA_DIR . '/tasks.json',
    'owners'      => $DATA_DIR . '/owners.json',
    'form_config' => $DATA_DIR . '/form_config.json',
    'finances'    => $DATA_DIR . '/finances.json'
];

// Инициализация файлов
if (!file_exists($FILES['users'])) {
    file_put_contents($FILES['users'], json_encode([
        ['id'=>1, 'login'=>'admin', 'pass'=>password_hash('admin', PASSWORD_DEFAULT), 'role'=>'admin', 'name'=>'Администратор'],
        ['id'=>2, 'login'=>'user', 'pass'=>password_hash('123', PASSWORD_DEFAULT), 'role'=>'user', 'name'=>'Волонтёр']
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
foreach ($FILES as $key => $file) {
    if (!file_exists($file)) {
        if ($key === 'form_config') {
            file_put_contents($file, json_encode([
                ['id'=>'experience', 'label'=>'Был ли у вас опыт содержания кошек?', 'type'=>'textarea', 'required'=>true],
                ['id'=>'housing', 'label'=>'Тип жилья', 'type'=>'text', 'required'=>true],
                ['id'=>'members', 'label'=>'Состав семьи', 'type'=>'textarea', 'required'=>true],
                ['id'=>'other_pets', 'label'=>'Есть ли другие животные?', 'type'=>'text', 'required'=>false]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            file_put_contents($file, '[]');
        }
    }
}

const STATUS_LABELS = ['new'=>'Новая','in_progress'=>'В работе','done'=>'Завершена','cancelled'=>'Отменена'];
const CAT_STATUS_LABELS = ['caught'=>'На отлове','treatment'=>'Лечение','ready_for_adoption'=>'Готова к пристройству','adopted'=>'Пристроена'];
const TASK_TYPES = ['catch'=>'Отлов','vet'=>'Ветеринар','vaccine'=>'Вакцинация','sterilize'=>'Стерилизация','photo'=>'Фотосессия','text'=>'Текст','publish'=>'Публикация','homecheck'=>'Проверка дома','transfer'=>'Передача'];
const ADOPT_STATUSES = ['searching'=>'Поиск дома','checking'=>'Проверка кандидата','transfer_scheduled'=>'Назначена передача','adopted'=>'Передана'];
const OWNER_STATUSES = ['interested'=>'Интересуются','want_take'=>'Хотят взять','filled_form'=>'Заполнили анкету','denied'=>'Отказано','transfer'=>'Передача','owners'=>'Хозяева'];
const FINANCE_TYPES = [
    'income' => ['donation'=>'Пожертвование','adoption_fee'=>'Плата за пристройство','other_income'=>'Прочий доход'],
    'expense' => ['vet'=>'Ветеринария','food'=>'Корм','supplies'=>'Наполнители','medication'=>'Лекарства','sterilization'=>'Стерилизация','transport'=>'Транспорт','other_expense'=>'Прочие расходы']
];