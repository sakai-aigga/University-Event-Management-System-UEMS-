<?php
session_start();
include '../includes/db-config.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: ../login/");
    exit;
}

$u_id = $_SESSION['u_id'];
$message = "";
$status = "";

// Fetch current data and cooldown info
$stmt = $conn->prepare("SELECT name, email, contact, profile_updated_at FROM users WHERE u_id = ?");
$stmt->bind_param("i", $u_id);
$stmt->execute();
$stmt->bind_result($name, $email, $contact, $profile_updated_at);
$stmt->fetch();
$stmt->close();

$is_locked = false;
$days_left = 0;

if ($profile_updated_at) {
    $last_update = strtotime($profile_updated_at);
    $diff = time() - $last_update;
    $one_week = 7 * 24 * 60 * 60;
    
    if ($diff < $one_week) {
        $is_locked = true;
        $days_left = ceil(($one_week - $diff) / (24 * 60 * 60));
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_locked) {
    $new_name = $_POST['name'] ?? '';
    $new_contact = $_POST['contact'] ?? '';

    if (!empty($new_name)) {
        $update = $conn->prepare("UPDATE users SET name = ?, contact = ?, profile_updated_at = NOW() WHERE u_id = ?");
        $update->bind_param("ssi", $new_name, $new_contact, $u_id);
        if ($update->execute()) {
            $_SESSION['name'] = $new_name; // Update session name
            $name = $new_name;
            $contact = $new_contact;
            $message = "Profile updated successfully!";
            $status = "success";
            $is_locked = true; // Lock immediately after update
            $days_left = 7;
        } else {
            $message = "Failed to update profile.";
            $status = "error";
        }
        $update->close();
    } else {
        $message = "Name cannot be empty.";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="icon" href="../assets/images/UEMS_logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .edit-hero {
            background: var(--primary-gradient);
            color: white;
            padding: 60px 8%;
            text-align: center;
            border-radius: 0 0 50px 50px;
        }
        .form-card-wrapper {
            max-width: 600px;
            margin: -40px auto 60px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }
        .form-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            padding: 40px;
        }
        .message-banner {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            text-align: center;
        }
        .banner-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .banner-error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?> 

    <section class="edit-hero">
        <h1>Edit Your Profile</h1>
        <p>Update your personal information here.</p>
    </section>

    <main class="form-card-wrapper">
        <div class="form-card">
            <?php if ($is_locked): ?>
                <div class="message-banner banner-error">
                    <i class="fas fa-lock"></i> Cooldown Active: You can update your profile in <?= $days_left ?> day(s).
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="message-banner banner-<?= $status ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <div class="input-wrapper">
                        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" <?= $is_locked ? 'disabled' : 'required' ?>>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email Address (Read Only)</label>
                    <div class="input-wrapper">
                        <input type="email" value="<?= htmlspecialchars($email) ?>" readonly style="background: #f3f4f6; cursor: not-allowed;">
                    </div>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <div class="input-wrapper">
                        <input type="text" name="contact" value="<?= htmlspecialchars($contact) ?>" <?= $is_locked ? 'disabled' : '' ?>>
                    </div>
                </div>
                
                <button type="submit" class="btn-login" <?= $is_locked ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                    <?= $is_locked ? 'Update Locked' : 'Update Profile' ?>
                </button>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="index.php" style="color: var(--primary-purple); font-weight: 600; text-decoration: none;">Back to Profile</a>
                </div>
            </form>
        </div>
    </main>

    <?php include "../includes/footer.php"; ?>
</body>
</html>
