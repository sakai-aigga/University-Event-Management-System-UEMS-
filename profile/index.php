<?php
session_start();
include '../includes/db-config.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: ../login/");
    exit;
}

// Fetch complete user data
$u_id = $_SESSION['u_id'];
$stmt = $conn->prepare("SELECT name, email, contact, profile_image FROM users WHERE u_id = ?");
$stmt->bind_param("i", $u_id);
$stmt->execute();
$stmt->bind_result($name, $email, $contact, $profile_image);
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
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .avatar-edit-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            font-size: 20px;
            text-align: center;
            padding: 8px 0;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .profile-avatar:hover .avatar-edit-overlay {
            opacity: 1;
        }
        .btn-remove-avatar {
            font-size: 13px;
            color: #ef4444;
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            display: block;
            margin: -20px auto 30px;
            font-weight: 500;
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
            border: none;
        }
        .btn-password {
            background: rgba(126, 34, 206, 0.05);
            color: var(--primary-purple);
            border: 2px solid var(--primary-purple);
        }
        .btn-delete-acc {
            background: rgba(239, 68, 68, 0.05);
            color: #ef4444;
            border: 2px solid #ef4444;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .btn-logout-alt {
            margin-top: 25px;
            padding: 12px 30px;
            background: #fdf2f2;
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border-radius: 50px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(239, 68, 68, 0.1);
        }
        .btn-logout-alt:hover {
            background: #fee2e2;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.1);
        }
        @media (max-width: 768px) {
            .profile-actions {
                flex-direction: column;
                gap: 15px;
            }
        }

        /* ====== GUI Alert / Confirm Modals ====== */
        .gui-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(6px);
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .gui-modal-overlay.show {
            display: flex;
            opacity: 1;
        }
        .gui-modal-box {
            background: #fff;
            border-radius: 22px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.2);
            overflow: hidden;
            transform: translateY(20px) scale(0.97);
            transition: transform 0.25s ease;
        }
        .gui-modal-overlay.show .gui-modal-box {
            transform: translateY(0) scale(1);
        }
        .gui-modal-header {
            background: var(--primary-gradient);
            padding: 22px 24px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .gui-modal-header .modal-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }
        .gui-modal-header h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #fff;
        }
        .gui-modal-body {
            padding: 24px 28px 10px;
            color: #374151;
            font-size: 15px;
            line-height: 1.6;
        }
        .gui-modal-footer {
            padding: 16px 28px 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        .gui-btn {
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: 0.25s;
        }
        .gui-btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }
        .gui-btn-cancel:hover {
            background: #e5e7eb;
        }
        .gui-btn-danger {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: #fff;
        }
        .gui-btn-danger:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(239,68,68,0.35);
        }
        .gui-btn-ok {
            background: var(--primary-gradient);
            color: #fff;
        }
        .gui-btn-ok:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
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
            <div class="profile-avatar" onclick="document.getElementById('profile-image-input').click();">
                <?php if (!empty($profile_image)): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($profile_image) ?>" alt="Profile Avatar">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
                <div class="avatar-edit-overlay">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            <form id="profile-image-form" style="display:none;">
                <input type="file" name="profile_image" id="profile-image-input" accept="image/*">
            </form>
            <?php if (!empty($profile_image)): ?>
                <button class="btn-remove-avatar" onclick="removeProfileImage()">Remove Image</button>
            <?php endif; ?>
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
                    <i class="fas fa-key"></i> Passwords
                </a>
                <a href="javascript:void(0)" onclick="confirmDeleteAccount()" class="action-btn btn-delete-acc">
                    <i class="fas fa-user-slash"></i> Delete Account
                </a>
            </div>

            <a href="logout.php" class="btn-logout-alt">
                <i class="fas fa-sign-out-alt"></i> Log Out from Account
            </a>
        </div>
    </main>

    <?php include "../includes/footer.php"; ?>

    <!-- GUI Confirm Modal (Remove Image) -->
    <div class="gui-modal-overlay" id="confirmRemoveModal">
        <div class="gui-modal-box">
            <div class="gui-modal-header">
                <div class="modal-icon"><i class="fas fa-trash-alt"></i></div>
                <h3>Remove Profile Picture</h3>
            </div>
            <div class="gui-modal-body">
                Are you sure you want to remove your profile picture? This action cannot be undone.
            </div>
            <div class="gui-modal-footer">
                <button class="gui-btn gui-btn-cancel" onclick="closeGuiModal('confirmRemoveModal')">Cancel</button>
                <button class="gui-btn gui-btn-danger" id="confirmRemoveBtn">Yes, Remove</button>
            </div>
        </div>
    </div>

    <!-- GUI Confirm Modal (Delete Account) -->
    <div class="gui-modal-overlay" id="confirmDeleteModal">
        <div class="gui-modal-box">
            <div class="gui-modal-header" style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
                <div class="modal-icon" style="background: rgba(255,255,255,0.2);"><i class="fas fa-user-slash"></i></div>
                <h3>Delete Your Account?</h3>
            </div>
            <div class="gui-modal-body">
                We're sorry to see you go. This action will permanently remove all your data and event history. This cannot be undone.
            </div>
            <div class="gui-modal-footer">
                <button class="gui-btn gui-btn-cancel" onclick="closeGuiModal('confirmDeleteModal')">Don't delete</button>
                <button class="gui-btn gui-btn-danger" id="confirmDeleteAccBtn">Permanently Delete</button>
            </div>
        </div>
    </div>

    <!-- GUI Alert Modal (Info / Error) -->
    <script>
        /* ===== GUI Modal Helpers ===== */
        function openGuiModal(id) {
            const el = document.getElementById(id);
            el.style.display = 'flex';
            requestAnimationFrame(() => el.classList.add('show'));
        }
        function closeGuiModal(id) {
            const el = document.getElementById(id);
            el.classList.remove('show');
            setTimeout(() => { el.style.display = 'none'; }, 250);
        }
        function showGuiAlert(message, type = 'info') {
            const header = document.getElementById('alertModalHeader');
            const icon   = document.getElementById('alertModalIcon');
            const title  = document.getElementById('alertModalTitle');
            document.getElementById('alertModalBody').textContent = message;
            if (type === 'error') {
                header.style.background = 'linear-gradient(135deg, #ef4444, #b91c1c)';
                icon.innerHTML  = '<i class="fas fa-exclamation-circle"></i>';
                title.textContent = 'Error';
            } else {
                header.style.background = '';
                icon.innerHTML  = '<i class="fas fa-info-circle"></i>';
                title.textContent = 'Notice';
            }
            openGuiModal('alertModal');
        }

        /* Close modals on overlay click */
        document.querySelectorAll('.gui-modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) closeGuiModal(this.id);
            });
        });

        /* ===== Upload image ===== */
        document.getElementById('profile-image-input').addEventListener('change', function() {
            if(this.files && this.files[0]) {
                const file = this.files[0];
                const maxSize = 16 * 1024 * 1024; // 16MB
                if(file.size > maxSize) {
                    showGuiAlert('Image size is too large. Maximum supported size is 16 MB.', 'error');
                    return;
                }
                const formData = new FormData();
                formData.append('profile_image', file);

                fetch('upload-image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        showGuiAlert(data.message || 'Error uploading image.', 'error');
                    }
                })
                .catch(() => showGuiAlert('Error uploading image.', 'error'));
            }
        });

        /* ===== Remove image (confirm modal) ===== */
        function removeProfileImage() {
            openGuiModal('confirmRemoveModal');
        }

        document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
            closeGuiModal('confirmRemoveModal');
            fetch('remove-image.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    showGuiAlert(data.message || 'Error removing image.', 'error');
                }
            })
            .catch(() => showGuiAlert('Error removing image.', 'error'));
        });

        /* ===== Delete Account ===== */
        function confirmDeleteAccount() {
            openGuiModal('confirmDeleteModal');
        }

        document.getElementById('confirmDeleteAccBtn').addEventListener('click', function() {
            closeGuiModal('confirmDeleteModal');
            fetch('delete-account.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showGuiAlert("Your account has been permanently deleted. Redirecting you to the dashboard...", "success");
                    setTimeout(() => {
                        window.location.href = '../index.php';
                    }, 2000);
                } else {
                    showGuiAlert(data.message || 'Error deleting account.', 'error');
                }
            })
            .catch(() => showGuiAlert('Error deleting account.', 'error'));
        });
    </script>
</body>
</html>
