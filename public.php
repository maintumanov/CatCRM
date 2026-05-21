<?php
session_start();
$DATA_DIR = __DIR__ . '/data';
$UPLOAD_DIR = __DIR__ . '/uploads';
$FILES = [
    'cats'         => $DATA_DIR . '/cats.json',
    'owners'       => $DATA_DIR . '/owners.json',
    'form_config'  => $DATA_DIR . '/form_config.json'
];

// Инициализация
if (!is_dir($DATA_DIR)) mkdir($DATA_DIR, 0755, true);
foreach ($FILES as $f) { if (!file_exists($f)) file_put_contents($f, '[]'); }

// Конфиг формы
$formConfig = json_decode(file_get_contents($FILES['form_config']), true) ?: [];
$baseFields = [
    ['id' => 'name',        'label' => 'Ваше имя',                     'type' => 'text',   'required' => true],
    ['id' => 'phone',       'label' => 'Телефон',                      'type' => 'tel',    'required' => true],
    ['id' => 'address',     'label' => 'Адрес / Населенный пункт',     'type' => 'text',   'required' => true],
    ['id' => 'experience',  'label' => 'Опыт содержания животных',     'type' => 'textarea', 'required' => false],
    ['id' => 'conditions',  'label' => 'Условия (квартира/дом, другие животные)', 'type' => 'textarea', 'required' => true]
];

$existingIds = array_column($formConfig, 'id');
foreach ($baseFields as $f) {
    if (!in_array($f['id'], $existingIds)) $formConfig[] = $f;
}
file_put_contents($FILES['form_config'], json_encode($formConfig, JSON_PRETTY_PRINT));

function loadData($f) { return json_decode(file_get_contents($f), true) ?: []; }
function saveData($f, $d) { file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX); }
function escape($s) { return htmlspecialchars(trim($s ?? ''), ENT_QUOTES, 'UTF-8'); }

$cats = loadData($FILES['cats']);
$owners = loadData($FILES['owners']);

// Фильтр готовых к пристройству
$ready_cats = array_filter($cats, function($c) {
    return ($c['status_history']['current'] ?? '') === 'ready_for_adoption';
});

$message = ''; $msgType = '';

// Обработка заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $cat_id = $_POST['cat_id'] ?? null;
    $cat = null;
    foreach ($cats as $c) { if (($c['id'] ?? '') == $cat_id) { $cat = $c; break; } }

    if (!$cat) { $message = "Ошибка: Кошка не найдена."; $msgType = "danger"; }
    else {
        $formData = []; $hasError = false;
        foreach ($formConfig as $field) {
            $val = trim($_POST[$field['id']] ?? '');
            if ($field['required'] && $val === '') { $message = "Заполните поле: " . $field['label']; $msgType = "warning"; $hasError = true; break; }
            $formData[$field['id']] = $val;
        }

        if (!$hasError) {
            $newOwner = [
                'id' => uniqid('o_'), 'cat_id' => $cat_id,
                'name' => $formData['name'] ?? 'Аноним', 'phone' => $formData['phone'] ?? '',
                'address' => $formData['address'] ?? '', 'status' => 'interested',
                'application_result' => json_encode($formData, JSON_UNESCAPED_UNICODE),
                'notes' => 'Заявка с витрины', 'created_at' => date('Y-m-d H:i:s'), 'created_by' => 0
            ];
            $owners[] = $newOwner;
            saveData($FILES['owners'], $owners);
            $message = "Спасибо! Заявка на \"" . escape($cat['identification']['name'] ?? 'питомца') . "\" принята.";
            $msgType = "success";
        }
    }
}

// Подготовка данных для JS (Галерея, Фильтры, Инфо)
$js_cats_data = [];
foreach ($ready_cats as $c) {
    $photos = array_filter(array_map(fn($p) => ($p['path'] ?? ''), $c['photos'] ?? []));
    
    // Определение возрастной группы для фильтра
    $ageStr = strtolower($c['identification']['approx_age'] ?? '');
    $ageGroup = 'adult';
    if (preg_match('/(котен|мес|год|1 )/i', $ageStr)) $ageGroup = 'kitten';
    if (preg_match('/(стар|пожил|7|8|9|10|11|12|13|14|15)/i', $ageStr)) $ageGroup = 'senior';

    $js_cats_data[$c['id']] = [
        'id' => $c['id'],
        'name' => $c['identification']['name'] ?? 'Без имени',
        'age' => $c['identification']['approx_age'] ?? '-',
        'sex' => ($c['identification']['sex'] ?? 'unknown') === 'male' ? 'male' : 'female',
        'color' => $c['identification']['color'] ?? '-',
        'desc' => $c['identification']['description'] ?? 'Нет описания',
        'photos' => array_values($photos),
        'age_group' => $ageGroup
    ];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Найди друга | Пристройство кошек</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #FF6B6B; --secondary: #4ECDC4; --dark: #2C3E50; --light-bg: #F7F9FC; }
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; flex-direction: column; color: var(--dark); }
        
        /* Hero */
        .hero-section { background: linear-gradient(135deg, rgba(255,107,107,0.95) 0%, rgba(78,205,196,0.95) 100%); color: white; padding: 2.5rem 0 5rem; border-radius: 0 0 40px 40px; margin-bottom: -2rem; position: relative; z-index: 1; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .hero-title { font-size: 2.5rem; font-weight: 800; text-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        
        /* Filters */
        .filters-container { background: white; padding: 1.5rem; margin-top: -2rem; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); position: relative; z-index: 2; margin-bottom: 2rem; }
        .filter-btn { border: 2px solid #e0e0e0; background: white; padding: 0.5rem 1.2rem; border-radius: 50px; font-weight: 600; transition: all 0.2s; margin: 0.2rem; color: #555; }
        .filter-btn:hover, .filter-btn.active { background: var(--primary); color: white; border-color: var(--primary); transform: translateY(-2px); }
        
        /* Cards */
        .cat-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: all 0.3s; height: 100%; display: flex; flex-direction: column; border: none; }
        .cat-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .cat-img-wrap { position: relative; height: 240px; overflow: hidden; background: #eee; }
        .cat-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .cat-card:hover .cat-img { transform: scale(1.05); }
        .badge-status { position: absolute; top: 15px; left: 15px; background: var(--secondary); color: white; padding: 0.3rem 0.8rem; border-radius: 10px; font-weight: 700; font-size: 0.8rem; box-shadow: 0 3px 8px rgba(0,0,0,0.2); }
        .fav-btn { position: absolute; top: 15px; right: 15px; background: white; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 3px 8px rgba(0,0,0,0.2); transition: all 0.2s; z-index: 5; }
        .fav-btn.active { color: var(--primary); background: white; transform: scale(1.1); }
        .fav-btn.active i { font-weight: bold; }
        
        .cat-body { padding: 1.2rem; flex: 1; display: flex; flex-direction: column; }
        .cat-name { font-size: 1.4rem; font-weight: 800; margin-bottom: 0.3rem; color: var(--dark); }
        .cat-desc { font-size: 0.9rem; color: #666; line-height: 1.4; margin-bottom: 1rem; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .cat-features { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.2rem; }
        .feat-item { background: var(--light-bg); padding: 0.3rem 0.7rem; border-radius: 8px; font-size: 0.85rem; color: #555; display: flex; align-items: center; gap: 0.3rem; }
        .btn-adopt { background: var(--primary); color: white; border: none; padding: 0.8rem; border-radius: 12px; font-weight: 700; transition: all 0.3s; box-shadow: 0 4px 10px rgba(255,107,107,0.3); width: 100%; }
        .btn-adopt:hover { background: #ff5252; color: white; transform: translateY(-2px); }
        .btn-details { background: white; color: var(--dark); border: 2px solid #eee; padding: 0.8rem; border-radius: 12px; font-weight: 700; transition: all 0.3s; width: 100%; }
        .btn-details:hover { border-color: var(--primary); color: var(--primary); }

        /* Modals */
        .modal-content { border-radius: 20px; border: none; overflow: hidden; }
        .modal-header { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; border: none; padding: 1.5rem; }
        .modal-body { background: var(--light-bg); padding: 2rem; }
        .gallery-box { height: 350px; background: white; border-radius: 15px; overflow: hidden; margin-bottom: 1.5rem; position: relative; display: flex; align-items: center; justify-content: center; }
        .carousel-inner img { height: 350px; object-fit: contain; background: white; }
        .pet-info-card { background: white; padding: 1.5rem; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .form-control { border-radius: 10px; border: 2px solid #e0e0e0; padding: 0.8rem; }
        .form-control:focus { border-color: var(--secondary); box-shadow: 0 0 0 0.2rem rgba(78,205,196,0.25); }

        /* Footer */
        footer { background: white; padding: 2rem 0; margin-top: auto; border-top: 1px solid #eee; text-align: center; }
        .qr-box { background: #f8f9fa; padding: 10px; display: inline-block; border-radius: 15px; }

        /* Empty */
        .empty-state { background: white; padding: 3rem; border-radius: 20px; text-align: center; }

        @media (max-width: 768px) {
            .hero-title { font-size: 1.8rem; }
            .gallery-box { height: 250px; }
            .carousel-inner img { height: 250px; }
        }
    </style>
</head>
<body>

    <!-- Hero -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title">Найди своего друга! 🐾</h1>
            <p class="lead mb-0" style="opacity: 0.95;">Эти кошки ищут дом. Выберите того, кто откликнется в сердце.</p>
        </div>
    </section>

    <div class="container pb-5" style="position: relative; z-index: 5;">
        <!-- Filters -->
        <div class="filters-container">
            <div class="d-flex flex-wrap gap-2 justify-content-center" id="filterGroup">
                <button class="filter-btn active" data-filter="all">Все</button>
                <button class="filter-btn" data-filter="kitten">🍼 Котята</button>
                <button class="filter-btn" data-filter="adult">🐈 Взрослые</button>
                <button class="filter-btn" data-filter="senior">👴 Пожилые</button>
                <div class="vr mx-2 d-none d-md-block"></div>
                <button class="filter-btn" data-filter="male">♂ Мальчики</button>
                <button class="filter-btn" data-filter="female">♀ Девочки</button>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show rounded-4 shadow-sm mb-4">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Catalog -->
        <?php if (empty($ready_cats)): ?>
            <div class="empty-state">
                <div style="font-size: 4rem;">📦</div>
                <h3>Пока нет свободных кошек</h3>
                <p class="text-muted">Мы обновим каталог, как только появятся новые хвостатые.</p>
                <a href="index.php" class="btn btn-primary rounded-pill mt-2">Вход для сотрудников</a>
            </div>
        <?php else: ?>
            <div class="row g-4" id="catsGrid">
                <?php foreach ($ready_cats as $cat): 
                    $photo = null;
                    foreach($cat['photos'] ?? [] as $p) { if($p['is_main'] ?? false) { $photo = $p; break; } }
                    if (!$photo && !empty($cat['photos'])) $photo = $cat['photos'][0];
                    
                    $ageStr = strtolower($cat['identification']['approx_age'] ?? '');
                    $ageGroup = 'adult';
                    if (preg_match('/(котен|мес|год|1 )/i', $ageStr)) $ageGroup = 'kitten';
                    if (preg_match('/(стар|пожил|7|8|9|1[0-9])/i', $ageStr)) $ageGroup = 'senior';
                ?>
                <div class="col-md-6 col-lg-4 cat-item" data-age="<?= $ageGroup ?>" data-sex="<?= ($cat['identification']['sex'] ?? '') === 'male' ? 'male' : 'female' ?>">
                    <div class="cat-card">
                        <div class="cat-img-wrap">
                            <?php if ($photo): ?>
                                <img src="<?= escape($photo['path'] ?? '') ?>" class="cat-img" alt="Фото">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted"><i class="bi bi-image" style="font-size: 4rem;"></i></div>
                            <?php endif; ?>
                            <span class="badge-status">Ищет дом</span>
                            <button class="fav-btn" onclick="toggleFav('<?= escape($cat['id']) ?>', this)">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>
                        <div class="cat-body">
                            <h3 class="cat-name"><?= escape($cat['identification']['name'] ?? 'Без имени') ?></h3>
                            <p class="cat-desc"><?= escape(mb_substr($cat['identification']['description'] ?? 'Милый котик ждет вас...', 0, 90)) ?>...</p>
                            <div class="cat-features">
                                <div class="feat-item"><i class="bi bi-calendar3"></i> <?= escape($cat['identification']['approx_age'] ?? '-') ?></div>
                                <div class="feat-item"><i class="bi bi-<?= ($cat['identification']['sex'] ?? '') === 'male' ? 'gender-male' : 'gender-female' ?>"></i> <?= ($cat['identification']['sex'] ?? '') === 'male' ? 'Мальчик' : 'Девочка' ?></div>
                                <div class="feat-item"><i class="bi bi-palette"></i> <?= escape($cat['identification']['color'] ?? '-') ?></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn-details" onclick="openDetails('<?= escape($cat['id']) ?>')"><i class="bi bi-eye me-1"></i> Подробнее</button>
                                <button class="btn-adopt" onclick="openAdopt('<?= escape($cat['id']) ?>', '<?= escape($cat['identification']['name'] ?? 'питомца') ?>')"><i class="bi bi-heart-fill me-1"></i> Забрать</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="fw-bold mb-2">Остались вопросы?</h5>
                    <p class="text-muted small mb-0">Позвоните нам или приходите в гости на знакомство.</p>
                    <p class="text-muted small mb-0">Тел: +7 (XXX) XXX-XX-XX</p>
                </div>
                <div class="col-md-6 text-center">
                    <div class="qr-box mb-2">
                        <img src="qr.png" alt="QR Code" style="width: 100px; height: 100px; object-fit: contain;">
                    </div>
                    <p class="small text-muted mb-1">Отсканируйте для открытия с телефона</p>
                    <a href="index.php" class="badge bg-secondary text-decoration-none px-3 py-2 rounded-pill">Вход в систему</a>
                </div>
            </div>
            <div class="text-center mt-4 pt-3 border-top small text-muted">
                © 2026 Система пристройства
            </div>
        </div>
    </footer>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="dTitle">Просмотр питомца</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="galleryContainer" class="gallery-box"></div>
                    <div class="pet-info-card">
                        <h6 class="fw-bold mb-3 text-primary">📋 Характеристики</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-6 col-sm-4"><strong>Возраст:</strong> <span id="dAge"></span></div>
                            <div class="col-6 col-sm-4"><strong>Пол:</strong> <span id="dSex"></span></div>
                            <div class="col-6 col-sm-4"><strong>Окрас:</strong> <span id="dColor"></span></div>
                            <div class="col-12 mt-2"><strong>Здоровье:</strong> <span class="text-success"><i class="bi bi-check-circle"></i> Привит/Стерилизован</span></div>
                        </div>
                        <hr>
                        <h6 class="fw-bold mb-2 text-primary">💬 Характер</h6>
                        <p id="dDesc" class="text-muted" style="white-space: pre-wrap;"></p>
                    </div>
                    <button class="btn-adopt py-3" onclick="switchToAdopt()">
                        <i class="bi bi-send-fill me-2"></i>Заполнить анкету на этого питомца
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Adopt Modal -->
    <div class="modal fade" id="adoptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light text-dark">
                    <h5 class="modal-title fw-bold">Анкета для <span id="aName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="cat_id" id="formCatId">
                        <div class="alert alert-info small mb-4"><i class="bi bi-info-circle me-2"></i>Честные ответы помогут нам быстрее найти дом для хвостика.</div>
                        <?php foreach ($formConfig as $field): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?= escape($field['label']) ?> <?php if ($field['required']): ?><span class="text-danger">*</span><?php endif; ?></label>
                            <?php if ($field['type'] === 'textarea'): ?>
                                <textarea name="<?= escape($field['id']) ?>" class="form-control" rows="3" <?= $field['required'] ? 'required' : '' ?>></textarea>
                            <?php elseif ($field['type'] === 'select'): ?>
                                <select name="<?= escape($field['id']) ?>" class="form-select" <?= $field['required'] ? 'required' : '' ?>>
                                    <option value="">Выберите...</option>
                                    <?php foreach(($field['options'] ?? []) as $opt): ?><option value="<?= escape($opt) ?>"><?= escape($opt) ?></option><?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="<?= escape($field['type']) ?>" name="<?= escape($field['id']) ?>" class="form-control" <?= $field['required'] ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <div class="d-grid mt-4">
                            <button type="submit" name="submit_application" class="btn btn-success btn-lg py-3 fw-bold shadow">Отправить заявку</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const catsData = <?= json_encode($js_cats_data, JSON_UNESCAPED_UNICODE) ?>;
        const favorites = JSON.parse(localStorage.getItem('catFavs') || '[]');
        let currentCatId = '';

        // Инициализация избранного
        document.querySelectorAll('.fav-btn').forEach(btn => {
            const id = btn.getAttribute('onclick').match(/'([^']+)'/)[1];
            if (favorites.includes(id)) {
                btn.classList.add('active');
                btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
            }
        });

        function toggleFav(id, btn) {
            const idx = favorites.indexOf(id);
            if (idx > -1) {
                favorites.splice(idx, 1);
                btn.classList.remove('active');
                btn.innerHTML = '<i class="bi bi-heart"></i>';
            } else {
                favorites.push(id);
                btn.classList.add('active');
                btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
            }
            localStorage.setItem('catFavs', JSON.stringify(favorites));
        }

        // Фильтры
        document.querySelectorAll('#filterGroup .filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('#filterGroup .filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const f = this.dataset.filter;
                let visibleCount = 0;

                document.querySelectorAll('.cat-item').forEach(item => {
                    const age = item.dataset.age;
                    const sex = item.dataset.sex;
                    let show = false;
                    
                    if (f === 'all') show = true;
                    else if (f === 'male' || f === 'female') show = (sex === f);
                    else show = (age === f);

                    item.style.display = show ? 'block' : 'none';
                    if (show) visibleCount++;
                });
                
                // Показываем сообщение если ничего не найдено
                const grid = document.getElementById('catsGrid');
                let emptyMsg = document.getElementById('filterEmptyMsg');
                if (visibleCount === 0) {
                    if (!emptyMsg) {
                        emptyMsg = document.createElement('div');
                        emptyMsg.id = 'filterEmptyMsg';
                        emptyMsg.className = 'col-12 text-center py-4';
                        emptyMsg.innerHTML = '<h5>😿 Никого не найдено</h5><p>Попробуйте изменить фильтры</p>';
                        grid.appendChild(emptyMsg);
                    }
                    emptyMsg.style.display = 'block';
                } else {
                    if (emptyMsg) emptyMsg.style.display = 'none';
                }
            });
        });

        // Модальные окна
        const dModal = new bootstrap.Modal(document.getElementById('detailsModal'));
        const aModal = new bootstrap.Modal(document.getElementById('adoptModal'));

        function openDetails(id) {
            const cat = catsData[id];
            if (!cat) return;
            currentCatId = id;

            document.getElementById('dTitle').innerText = cat.name;
            document.getElementById('dAge').innerText = cat.age;
            document.getElementById('dSex').innerText = cat.sex === 'male' ? 'Мальчик ♂' : 'Девочка ♀';
            document.getElementById('dColor').innerText = cat.color;
            document.getElementById('dDesc').innerText = cat.desc;

            // Галерея
            const container = document.getElementById('galleryContainer');
            container.innerHTML = '';
            
            if (cat.photos.length === 0) {
                container.innerHTML = '<div class="text-muted"><i class="bi bi-image" style="font-size: 4rem;"></i><br>Фото скоро будут</div>';
            } else {
                let itemsHtml = '';
                let indHtml = '';
                cat.photos.forEach((src, i) => {
                    indHtml += `<button type="button" data-bs-target="#galleryCarousel" data-bs-slide-to="${i}" ${i===0?'class="active" aria-current="true"':''}></button>`;
                    itemsHtml += `<div class="carousel-item ${i===0?'active':''}"><img src="${src}" class="d-block w-100" alt="Фото"></div>`;
                });
                container.innerHTML = `
                    <div id="galleryCarousel" class="carousel slide h-100" data-bs-ride="false">
                        <div class="carousel-indicators">${indHtml}</div>
                        <div class="carousel-inner h-100">${itemsHtml}</div>
                        ${cat.photos.length > 1 ? `
                        <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                        <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>` : ''}
                    </div>`;
                new bootstrap.Carousel(document.getElementById('galleryCarousel'));
            }
            dModal.show();
        }

        function switchToAdopt() {
            dModal.hide();
            setTimeout(() => {
                const cat = catsData[currentCatId];
                openAdopt(currentCatId, cat ? cat.name : 'питомца');
            }, 300);
        }

        function openAdopt(id, name) {
            currentCatId = id;
            document.getElementById('aName').innerText = name;
            document.getElementById('formCatId').value = id;
            aModal.show();
        }
    </script>
</body>
</html>