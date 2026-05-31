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

// Fetch unread notifications
$unread_count = 0;
$recent_notifications = [];
if (isset($conn)) {
    // Failsafe in case table is completely newly added
    @$conn->query("ALTER TABLE contact_submissions ADD COLUMN name VARCHAR(255) AFTER user_id");
    @$conn->query("ALTER TABLE contact_submissions ADD COLUMN email VARCHAR(255) AFTER name");
    @$conn->query("ALTER TABLE contact_submissions ADD COLUMN message TEXT AFTER email");
    @$conn->query("ALTER TABLE contact_submissions ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER message");

    $notif_sql = "SELECT * FROM contact_submissions WHERE is_read = 0 ORDER BY submitted_at DESC LIMIT 5";
    $notif_res = @$conn->query($notif_sql);
    
    // Get actual unread count
    $count_sql = @$conn->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0");
    if ($count_sql) {
        $unread_count = $count_sql->fetch_row()[0];
    }

    if ($notif_res) {
        while($row = $notif_res->fetch_assoc()) {
            $recent_notifications[] = $row;
        }
    }
}

// Define menu with full paths
$menuItems = [
    [
        "menuTitle" => "Dashboard",
        "icon" => "fas fa-tachometer-alt",
        "url" => BASE_URL . "/admin-panel/index.php"
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
        "url" => BASE_URL . "/admin-panel/users/index.php"
    ],
    [
        "menuTitle" => "Departments",
        "icon" => "fas fa-building",
        "url" => BASE_URL . "/admin-panel/departments/index.php"
    ],
    [
        "menuTitle" => "Notifications",
        "icon" => "fas fa-bell",
        "url" => BASE_URL . "/admin-panel/notifications.php"
    ]
];

$active_pageInfo = null;
foreach ($menuItems as $menuItem) {
    if (isset($menuItem['pages'])) {
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
    } else {
        if (basename($menuItem['url']) === $currentPage && ($currentDir === 'admin-panel' || strpos($menuItem['url'], "/$currentDir/") !== false)) {
            $active_pageInfo = [
                "breadcrumb_Items" => [
                    ["title" => $menuItem['menuTitle'], "url" => $menuItem['url']]
                ],
                "page_title" => $menuItem['menuTitle'],
                "active_menu" => $menuItem,
                "active_page" => null
            ];
            break;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                    <div class="col-sm-6 col-12 d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb admin-breadcrumb m-0 float-none d-inline-flex me-4">
                            <?php foreach ($breadcrumb_Items as $item): ?>
                                <li class="breadcrumb-item <?= $item['url'] === '#' ? 'active' : '' ?>">
                                    <?= $item['url'] === '#' ? $item['title'] : "<a href='{$item['url']}'>{$item['title']}</a>" ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>

                        <!-- Notification Bell Dropdown -->
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link" data-bs-toggle="dropdown" href="#" style="position: relative;" id="notif-bell">
                                    <i class="far fa-bell" style="font-size: 1.2rem;"></i>
                                    <span id="notif-badge-container">
                                        <?php if ($unread_count > 0): ?>
                                            <span class="badge rounded-pill bg-danger" style="position: absolute; top: 0; right: -5px; font-size: 0.65rem; border: 2px solid var(--dark-purple);"><?= $unread_count ?></span>
                                        <?php endif; ?>
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end border-0 shadow-lg" style="min-width: 300px; border-radius: 12px; margin-top: 10px;">
                                    <div class="dropdown-header text-center" style="background: var(--bg-light); border-top-left-radius: 12px; border-top-right-radius: 12px;">
                                        <strong style="color: var(--text-dark);" id="notif-header-text">
                                            <?= $unread_count ?> New Notifications
                                        </strong>
                                    </div>
                                    <div class="dropdown-divider m-0"></div>
                                    
                                    <div id="notif-items-container">
                                        <?php if (empty($recent_notifications)): ?>
                                            <a href="#" class="dropdown-item text-center text-muted py-3">
                                                No recent notifications.
                                            </a>
                                        <?php else: ?>
                                            <?php foreach ($recent_notifications as $notif): ?>
                                                <a href="<?= BASE_URL ?>/admin-panel/notifications.php" class="dropdown-item py-3" style="white-space: normal; line-height: 1.4;">
                                                    <i class="fas fa-envelope mr-2 text-primary"></i> 
                                                    <strong><?= htmlspecialchars($notif['name'] ?? 'User') ?></strong> left a message: <br>
                                                    <span class="text-muted text-sm d-inline-block mt-1" style="max-height: 40px; overflow: hidden; text-overflow: ellipsis;">
                                                        <?= htmlspecialchars(substr($notif['message'] ?? '', 0, 50)) ?>...
                                                    </span>
                                                </a>
                                                <div class="dropdown-divider m-0"></div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <a href="<?= BASE_URL ?>/admin-panel/notifications.php" class="dropdown-item dropdown-footer text-center" style="background: var(--bg-light); border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                                        See All Inquiries
                                    </a>
                                </div>
                            </li>
                        </ul>
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
                            <div class="sidebar-user-avatar d-flex align-items-center justify-content-center elevation-2" style="background-color: white; color: #343a40; font-weight: bold; width: 35px; height: 35px; border-radius: 50%; font-size: 18px;">A</div>
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
                            <?php if (isset($menuItem['pages'])): ?>
                                <?php 
                                $isEvents = ($menuItem['menuTitle'] === 'Events');
                                $isOpen = $isEvents || ($menuItem === $active_menu);
                                ?>
                                <li class="nav-item has-treeview <?= $isOpen ? 'menu-open' : '' ?> <?= $isEvents ? 'events-menu-item' : '' ?>">
                                    <a class="nav-link <?= $menuItem === $active_menu ? 'active' : '' ?>" href="#" <?= $isEvents ? 'onclick="return false;" style="cursor: default;"' : '' ?>>
                                        <i class="nav-icon <?= $menuItem['icon'] ?>"></i>
                                        <p>
                                            <?= $menuItem['menuTitle'] ?>
                                            <?php if (!$isEvents): ?>
                                                <i class="right fas fa-angle-left"></i>
                                            <?php endif; ?>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview" style="<?= $isOpen ? 'display: block;' : '' ?>">
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
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a href="<?= $menuItem['url'] ?>" class="nav-link <?= $menuItem === $active_menu ? 'active' : '' ?>">
                                        <i class="nav-icon <?= $menuItem['icon'] ?>"></i>
                                        <p><?= $menuItem['menuTitle'] ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
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

<script>
    // Real-time Notification Updates
    function updateNotifications() {
        $.ajax({
            url: '<?= BASE_URL ?>/api/admin/get-notifications.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Update Badge
                    const badgeContainer = $('#notif-badge-container');
                    if (response.unread_count > 0) {
                        badgeContainer.html(`<span class="badge rounded-pill bg-danger" style="position: absolute; top: 0; right: -5px; font-size: 0.65rem; border: 2px solid var(--dark-purple);">${response.unread_count}</span>`);
                    } else {
                        badgeContainer.empty();
                    }

                    // Update Header Text
                    $('#notif-header-text').text(`${response.unread_count} New Notifications`);

                    // Update List
                    const itemsContainer = $('#notif-items-container');
                    if (response.recent.length === 0) {
                        itemsContainer.html('<a href="#" class="dropdown-item text-center text-muted py-3">No recent notifications.</a>');
                    } else {
                        let html = '';
                        response.recent.forEach(notif => {
                            html += `
                                <a href="<?= BASE_URL ?>/admin-panel/notifications.php" class="dropdown-item py-3" style="white-space: normal; line-height: 1.4;">
                                    <i class="fas fa-envelope mr-2 text-primary"></i> 
                                    <strong>${notif.name || 'User'}</strong> left a message: <br>
                                    <span class="text-muted text-sm d-inline-block mt-1" style="max-height: 40px; overflow: hidden; text-overflow: ellipsis;">
                                        ${notif.message_short}
                                    </span>
                                </a>
                                <div class="dropdown-divider m-0"></div>
                            `;
                        });
                        itemsContainer.html(html);
                    }
                }
            },
            error: function(err) {
                console.error("Notification Fetch Error:", err);
            }
        });
    }

    // Update every 10 seconds
    setInterval(updateNotifications, 10000);
</script>