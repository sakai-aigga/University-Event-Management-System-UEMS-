<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['u_id']);
include_once __DIR__ . '/path-config.php';
?>

<!-- Global Scripts & Styles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="<?= BASE_URL ?>/assets/images/UEMS_logo.png" type="image/x-icon"> <!-- For proper path definition -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<header>
    <div class="logo-area">
    <a href="<?= BASE_URL ?>/index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
        <img src="<?= BASE_URL ?>/assets/images/UEMS_logo.png" class="header-logo" alt="KUEMS">
        <span class="logo-text">KUEMS</span>
    </a>
</div>

    <nav>
        <a href="<?= BASE_URL ?>/event/my-events.php">My Events</a>
        <a href="<?= BASE_URL ?>/uems/about.php">About</a>
        <a href="<?= BASE_URL ?>/uems/contact.php">Contact</a>
        <?php if ($isLoggedIn): ?>
            <?php
            require_once __DIR__ . '/db-config.php';
            $p_image_html = '';
            global $conn;
            if (isset($conn)) {
                $uid = $_SESSION['u_id'];
                $stmt = $conn->prepare("SELECT profile_image FROM users WHERE u_id = ?");
                $stmt->bind_param("i", $uid);
                $stmt->execute();
                $stmt->bind_result($p_image);
                $stmt->fetch();
                $stmt->close();
                if (!empty($p_image)) {
                    $b64 = base64_encode($p_image);
                    $p_image_html = '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;overflow:hidden;flex-shrink:0;margin-right:6px;vertical-align:middle;">'
                        . '<img src="data:image/jpeg;base64,' . $b64 . '" width="28" height="28" style="width:28px;height:28px;object-fit:cover;display:block;">'
                        . '</span>';
                } else {
                    $p_image_html = '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background-color:white;color:#6a11cb;flex-shrink:0;margin-right:6px;vertical-align:middle;font-size:12px;"><i class="fas fa-user"></i></span>';
                }
            }
            $firstName = explode(' ', trim($_SESSION['name'] ?? 'User'))[0];
            ?>
            <a href="<?= BASE_URL ?>/profile/" style="display:flex;align-items:center;gap:0;"><?= $p_image_html ?><?= htmlspecialchars($firstName); ?></a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/login/" class="head-btn-login">Login</a>
        <?php endif; ?>
    </nav>
</header>

    