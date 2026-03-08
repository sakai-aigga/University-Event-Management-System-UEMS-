<?php
session_start();
include '../includes/db-config.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: ../login/");
    exit;
}

// Fetch complete user data
$u_id = $_SESSION['u_id'];
$stmt = $conn->prepare("SELECT name, email, contact FROM users WHERE u_id = ?");
$stmt->bind_param("i", $u_id);
$stmt->execute();
$stmt->bind_result($name, $email, $contact);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-hero {
            background: var(--primary-gradient);
            color: white;
            padding: 80px 8%;
            text-align: center;
            border-radius: 0 0 50px 50px;
        }
        .profile-hero h1 {
            font-size: 42px;
            margin-bottom: 10px;
        }
        .profile-card-wrapper {
            max-width: 800px;
            margin: -60px auto 60px;
            position: relative;
            z-index: 10;
            padding: 0 20px;
        }
        .profile-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            padding: 50px;
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: var(--bg-light);
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: var(--primary-purple);
            border: 5px solid white;
            box-shadow: var(--card-shadow);
        }
        .user-details {
            margin-bottom: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            text-align: left;
        }
        .detail-item {
            padding: 15px 20px;
            background: var(--bg-light);
            border-radius: 15px;
        }
        .detail-item label {
            display: block;
            font-size: 12px;
            color: #777;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .detail-item p {
            font-size: 16px;
            color: var(--text-dark);
            font-weight: 500;
        }
        .profile-actions {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }
        .action-btn {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-edit {
            background: var(--primary-gradient);
            color: white;
        }
        .btn-password {
            background: white;
            color: var(--primary-purple);
            border: 2px solid var(--primary-purple);
        }
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .btn-logout-alt {
            margin-top: 20px;
            display: inline-block;
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }
        .btn-logout-alt:hover {
            opacity: 0.8;
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .profile-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?> 

    <section class="profile-hero">
        <h1>Welcome Back</h1>
        <p>Manage your account settings and personal information.</p>
    </section>

    <main class="profile-card-wrapper">
        <div class="profile-card">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            
            <div class="user-details">
                <div class="detail-item">
                    <label>Full Name</label>
                    <p><?= htmlspecialchars($name) ?></p>
                </div>
                <div class="detail-item">
                    <label>Email Address</label>
                    <p><?= htmlspecialchars($email) ?></p>
                </div>
                <div class="detail-item">
                    <label>Contact Number</label>
                    <p><?= htmlspecialchars($contact ?: 'Not Provided') ?></p>
                </div>
            </div>

            <div class="profile-actions">
                <a href="edit-profile.php" class="action-btn btn-edit">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
                <a href="change-password.php" class="action-btn btn-password">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>

            <a href="logout.php" class="btn-logout-alt">
                <i class="fas fa-sign-out-alt"></i> Log Out from Account
            </a>
        </div>
    </main>

    <?php include "../includes/footer.php"; ?>
</body>
</html>
