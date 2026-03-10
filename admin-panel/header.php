<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Find base path reliably
$admin_root = dirname(__FILE__);
$config_path = dirname($admin_root) . '/includes/path-config.php';
if (file_exists($config_path)) {
    require_once $config_path;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));

// Define menu with full paths
$menuItems = [
    [
        "menuTitle" => "Dashboard",
        "icon" => "fas fa-tachometer-alt",
        "pages" => [
            ["title" => "Home", "url" => BASE_URL . "/admin-panel/index.php"]
        ],
    ],
    [
        "menuTitle" => "Events",
        "icon" => "fas fa-calendar-alt",
        "pages" => [
            ["title" => "Manage Events", "url" => BASE_URL . "/admin-panel/events/index.php"],
            ["title" => "Create Event", "url" => BASE_URL . "/admin-panel/events/create.php"]
        ],
    ],
    [
        "menuTitle" => "Users",
        "icon" => "fas fa-users",
        "pages" => [
            ["title" => "Manage Users", "url" => BASE_URL . "/admin-panel/users/index.php"]
        ],
    ],
    [
        "menuTitle" => "Departments",
        "icon" => "fas fa-building",
        "pages" => [
            ["title" => "Manage Departments", "url" => BASE_URL . "/admin-panel/departments/index.php"]
        ],
    ]
];

$active_pageInfo = null;
foreach ($menuItems as $menuItem) {
    foreach ($menuItem['pages'] as $page) {
        if (basename($page['url']) === $currentPage && ($currentDir === 'admin-panel' || strpos($page['url'], "/$currentDir/") !== false)) {
            $active_pageInfo = [
                "breadcrumb_Items" => [
                    ["title" => $menuItem['menuTitle'], "url" => "#"],
                    ["title" => $page['title'], "url" => $page['url']]
                ],
                "page_title" => $page['title'],
                "active_menu" => $menuItem,
                "active_page" => $page
            ];
            break 2;
        }
    }
}

$breadcrumb_Items = $active_pageInfo['breadcrumb_Items'] ?? [];
$page_title = $active_pageInfo['page_title'] ?? 'Admin Panel';
$active_menu = $active_pageInfo['active_menu'] ?? null;
$active_page = $active_pageInfo['active_page'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/UEMS_logo.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin-panel/css/admin-styles.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">


        <nav class="main-header navbar navbar-expand navbar-white navbar-light content-header-bar d-flex align-items-center">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <div class="content-header w-100 ps-2">
                <div class="row breadcrumb-row align-items-center m-0">
                    <div class="col-sm-6 col-12 mb-2 mb-sm-0">
                        <h1 class="admin-page-title"><?= $page_title ?></h1>
                    </div>
                    <div class="col-sm-6 col-12">
                        <ol class="breadcrumb admin-breadcrumb m-0 float-sm-right float-none">
                            <?php foreach ($breadcrumb_Items as $item): ?>
                                <li class="breadcrumb-item <?= $item['url'] === '#' ? 'active' : '' ?>">
                                    <?= $item['url'] === '#' ? $item['title'] : "<a href='{$item['url']}'>{$item['title']}</a>" ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="<?= BASE_URL ?>/admin-panel/" class="brand-link">
                <img src="<?= BASE_URL ?>/assets/images/UEMS_logo.png" alt="Logo" class="brand-image admin-brand-logo">
                <span class="brand-text admin-brand-text">
                    Admin Panel
                </span>
            </a>
            <div class="sidebar">
                <div class="user-panel admin-user-panel">
                    <a href="<?= BASE_URL ?>/admin-panel/profile.php" class="admin-user-link">
                        <div class="image">
                            <img src="<?= BASE_URL ?>/assets/images/profile_p.png" class="sidebar-user-avatar elevation-2" alt="User Image">
                        </div>
                        <div class="admin-user-info">
                            <span class="admin-greeting">Hello,</span>
                            <span class="admin-username"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></span>
                        </div>
                    </a>
                </div>
                <nav class="admin-sidebar-nav">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <?php foreach ($menuItems as $menuItem): ?>
                            <li class="nav-item has-treeview <?= $menuItem === $active_menu ? 'menu-open' : '' ?>">
                                <a class="nav-link <?= $menuItem === $active_menu ? 'active' : '' ?>" href="#">
                                    <i class="nav-icon <?= $menuItem['icon'] ?>"></i>
                                    <p>
                                        <?= $menuItem['menuTitle'] ?>
                                        <?= !empty($menuItem['pages']) ? '<i class="right fas fa-angle-left"></i>' : '' ?>
                                    </p>
                                </a>
                                <?php if (!empty($menuItem['pages'])): ?>
                                    <ul class="nav nav-treeview">
                                        <?php foreach ($menuItem['pages'] as $page): ?>
                                            <li class="nav-item">
                                                <a href="<?= $page['url'] ?>"
                                                    class="nav-link <?= $page === $active_page ? 'active' : '' ?>">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p><?= $page['title'] ?></p>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        <li class="nav-item admin-logout-item">
                            <a href="<?= BASE_URL ?>/profile/logout.php" class="nav-link admin-logout-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">