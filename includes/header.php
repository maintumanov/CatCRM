<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CatRescue Pro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
:root { --md-primary: #1976d2; --md-primary-light: #42a5f5; --md-surface: #ffffff; --md-background: #f5f5f5; --md-on-surface: #212121; --md-on-surface-variant: #757575; --md-elevation-1: 0 2px 6px rgba(0,0,0,0.08); --md-elevation-2: 0 4px 12px rgba(0,0,0,0.12); --md-radius: 12px; }
body { background: var(--md-background); font-family: 'Roboto', system-ui, sans-serif; color: var(--md-on-surface); padding-bottom: 40px; }
.md-navbar { background: var(--md-primary); box-shadow: 0 2px 8px rgba(25,118,210,0.3); }
.md-card { background: var(--md-surface); border-radius: var(--md-radius); box-shadow: var(--md-elevation-1); border: none; transition: 0.2s; }
.md-card:hover { box-shadow: var(--md-elevation-2); transform: translateY(-2px); }
.md-avatar { width: 72px; height: 72px; border-radius: 50%; background: #e3f2fd; color: var(--md-primary); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; overflow: hidden; }
.md-avatar img { width: 100%; height: 100%; object-fit: cover; }
.md-badge { border-radius: 50px; padding: 5px 14px; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
.md-badge-caught { background: #e3f2fd; color: #1565c0; } .md-badge-treatment { background: #fff3e0; color: #e65100; }
.md-badge-ready { background: #e8f5e9; color: #2e7d32; } .md-badge-adopted { background: #f3e5f5; color: #7b1fa2; }
.md-tabs .nav-link { color: var(--md-on-surface-variant); font-weight: 500; padding: 14px 20px; border-bottom: 3px solid transparent; transition: 0.2s; }
.md-tabs .nav-link.active { color: var(--md-primary); border-bottom-color: var(--md-primary); }
.md-btn { border-radius: 8px; font-weight: 500; text-transform: none; box-shadow: none; transition: 0.2s; }
.md-field { border-radius: 8px; padding: 10px 14px; background: #f8f9fa; border: 1px solid #dee2e6; transition: 0.2s; }
.md-field:focus { border-color: var(--md-primary); box-shadow: 0 0 0 3px rgba(25,118,210,0.15); background: #fff; }
.info-chip { background: #f1f3f4; border-radius: 20px; padding: 4px 12px; font-size: 0.8rem; color: var(--md-on-surface-variant); margin: 4px; display: inline-flex; align-items: center; gap: 6px; }
.stat-bar { height: 8px; border-radius: 4px; background: #eee; overflow: hidden; margin-top: 5px; }
.stat-fill { height: 100%; background: var(--md-primary); }
.finance-income { color: #2e7d32; } .finance-expense { color: #c62828; }
.finance-balance-positive { color: #2e7d32; font-weight: bold; } .finance-balance-negative { color: #c62828; font-weight: bold; }
.calendar-day { min-height: 100px; border: 1px solid #e0e0e0; padding: 8px; background: #fff; }
.calendar-day.other-month { background: #f5f5f5; color: #999; }
.calendar-day.today { background: #e3f2fd; border-color: var(--md-primary); }
.calendar-task { font-size: 0.75rem; padding: 2px 6px; margin: 2px 0; border-radius: 4px; background: var(--md-primary); color: white; }
.owner-card { border-left: 4px solid var(--md-primary); }
.owner-interested { border-left-color: #2196f3; } .owner-want_take { border-left-color: #4caf50; }
.owner-filled_form { border-left-color: #ff9800; } .owner-denied { border-left-color: #f44336; }
.owner-transfer { border-left-color: #9c27b0; } .owner-owners { border-left-color: #009688; }
@media print { .no-print,.md-navbar,.nav-tabs,.btn,form,footer{display:none!important;} body{background:white;color:black;padding:0;} .md-card{box-shadow:none;border:1px solid #ddd;break-inside:avoid;} .tab-pane{display:block!important;opacity:1!important;} }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg md-navbar mb-4 no-print">
<div class="container">
<a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2" href="?action=dashboard"><span class="material-icons">pets</span> CatRescue Pro</a>
<?php if (!empty($current_user)): ?>
<div class="d-flex align-items-center text-white gap-3">
<span class="d-none d-sm-block"><?= escape($current_user['name']) ?></span>
<span class="badge bg-white text-primary"><?= $current_user['role']==='admin'?'Админ':'Пользователь' ?></span>
<a href="?action=logout" class="btn btn-sm btn-outline-light">Выйти</a>
</div>
<?php endif; ?>
</div>
</nav>