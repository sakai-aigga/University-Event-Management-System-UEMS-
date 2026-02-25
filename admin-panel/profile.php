<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db-config.php';

// Handle AJAX Requests (Profile Update & Password Change)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $u_id = $_SESSION['u_id'];
    $response = ['success' => false, 'message' => ''];

    // Action: Update Profile
    if ($_POST['action'] === 'update_profile') {
        $new_name = $_POST['name'] ?? '';
        $new_contact = $_POST['contact'] ?? '';
        
        $check = $conn->prepare("SELECT profile_updated_at FROM users WHERE u_id = ?");
        $check->bind_param("i", $u_id); $check->execute(); $check->bind_result($profile_updated_at); $check->fetch(); $check->close();

        if ($profile_updated_at && (time() - strtotime($profile_updated_at)) < 7*24*60*60) {
            $response['message'] = "Profile is locked for security.";
        } else if (empty($new_name)) {
            $response['message'] = "Name cannot be empty.";
        } else {
            $update = $conn->prepare("UPDATE users SET name = ?, contact = ?, profile_updated_at = NOW() WHERE u_id = ?");
            $update->bind_param("ssi", $new_name, $new_contact, $u_id);
            if ($update->execute()) {
                $_SESSION['name'] = $new_name;
                $response['success'] = true;
                $response['message'] = "Changes updated";
            } else { $response['message'] = "Failed to update."; }
            $update->close();
        }
    }

    // Action: Change Password
    if ($_POST['action'] === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $conn->prepare("SELECT password, password_updated_at FROM users WHERE u_id = ?");
        $stmt->bind_param("i", $u_id); $stmt->execute(); $stmt->bind_result($db_pass, $pass_updated_at); $stmt->fetch(); $stmt->close();

        if ($pass_updated_at && (time() - strtotime($pass_updated_at)) < 7*24*60*60) {
            $response['message'] = "Password change is on cooldown.";
        } else if (!password_verify($current, $db_pass)) {
            $response['message'] = "Current password is incorrect.";
        } else if ($new !== $confirm) {
            $response['message'] = "Passwords do not match.";
        } else if (strlen($new) < 4) {
            $response['message'] = "Minimum 4 characters required.";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ?, password_updated_at = NOW() WHERE u_id = ?");
            $update->bind_param("si", $hashed, $u_id);
            if ($update->execute()) {
                $response['success'] = true;
                $response['message'] = "Changes updated";
            } else { $response['message'] = "Process failed."; }
            $update->close();
        }
    }

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

require_once './header.php';
$u_id = $_SESSION['u_id'];
$stmt = $conn->prepare("SELECT name, email, contact, profile_updated_at, password_updated_at FROM users WHERE u_id = ?");
$stmt->bind_param("i", $u_id); $stmt->execute();
$stmt->bind_result($name, $email, $contact, $profile_updated_at, $password_updated_at);
$stmt->fetch(); $stmt->close();

$profile_locked = ($profile_updated_at && (time() - strtotime($profile_updated_at)) < 7*24*60*60);
$pass_locked = ($password_updated_at && (time() - strtotime($password_updated_at)) < 7*24*60*60);
?>

<div class="row admin-profile-row">
    <div class="col-md-5">
        <div class="card card-primary card-outline admin-profile-card">
            <div class="card-body admin-profile-body">
                <!-- Avatar Section -->
                <div class="admin-profile-avatar-section">
                    <div class="admin-profile-avatar-wrapper">
                        <img class="profile-user-img img-fluid img-circle admin-profile-avatar-img" 
                             src="<?= BASE_URL ?>/assets/images/profile_p.png" 
                             alt="Admin profile picture">
                        <span class="admin-profile-online-dot" title="Online"></span>
                    </div>
                    <h3 class="admin-profile-name"><?= htmlspecialchars($name) ?></h3>
                    <p class="admin-profile-subtitle">Administrative Account</p>
                </div>

                <!-- Details Section -->
                <div class="admin-profile-details">
                    <div class="admin-profile-detail-row">
                        <span class="admin-profile-detail-label"><i class="fas fa-envelope icon-email"></i> Email</span>
                        <span class="admin-profile-detail-value"><?= htmlspecialchars($email) ?></span>
                    </div>
                    <div class="admin-profile-detail-row">
                        <span class="admin-profile-detail-label"><i class="fas fa-phone icon-phone"></i> Contact</span>
                        <span class="admin-profile-detail-value"><?= htmlspecialchars($contact ?: '—') ?></span>
                    </div>
                </div>

                <!-- Actions Section -->
                <div class="admin-profile-actions">
                    <button type="button" class="btn btn-primary" onclick="showModal('editProfileModal')">
                        <i class="fas fa-user-edit mr-2"></i> Edit Account Info
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="showModal('changePasswordModal')">
                        <i class="fas fa-key mr-2"></i> Update Password
                    </button>
                </div>

                <!-- Logout Section -->
                <div class="admin-profile-logout">
                    <a href="<?= BASE_URL ?>/profile/logout.php">
                        <i class="fas fa-sign-out-alt mr-1"></i> Secure Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header-primary">
                <h5 class="modal-title admin-modal-title"><i class="fas fa-user-edit mr-2"></i> Update Account Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProfileForm">
                <input type="hidden" name="action" value="update_profile">
                <div class="modal-body admin-modal-body">
                    <div class="form-group admin-form-spacing-lg">
                        <label class="admin-form-label">Display Name</label>
                        <input type="text" name="name" class="form-control admin-input-flat" value="<?= htmlspecialchars($name) ?>" <?= $profile_locked ? 'disabled' : 'required' ?>>
                    </div>
                    <div class="form-group admin-form-no-margin">
                        <label class="admin-form-label">Primary Contact</label>
                        <input type="text" name="contact" class="form-control admin-input-flat" value="<?= htmlspecialchars($contact) ?>" <?= $profile_locked ? 'disabled' : '' ?>>
                    </div>
                    <?php if ($profile_locked): ?>
                        <div class="alert admin-cooldown-alert"><i class="fas fa-info-circle mr-2"></i> Update locked for 7 days.</div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer admin-modal-footer">
                    <button type="button" class="btn btn-link admin-cancel-link" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary admin-btn-wide" <?= $profile_locked ? 'disabled' : '' ?>>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header-secondary">
                <h5 class="modal-title admin-modal-title"><i class="fas fa-lock mr-2"></i> Secure Password Update</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePasswordForm">
                <input type="hidden" name="action" value="change_password">
                <div class="modal-body admin-modal-body">
                    <div class="form-group admin-form-spacing">
                        <label class="admin-form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control admin-input-flat" required <?= $pass_locked ? 'disabled' : '' ?>>
                    </div>
                    <div class="form-group admin-form-spacing">
                        <label class="admin-form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control admin-input-flat" required minlength="4" <?= $pass_locked ? 'disabled' : '' ?>>
                    </div>
                    <div class="form-group admin-form-no-margin">
                        <label class="admin-form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control admin-input-flat" required minlength="4" <?= $pass_locked ? 'disabled' : '' ?>>
                    </div>
                    <?php if ($pass_locked): ?>
                        <div class="alert admin-cooldown-alert"><i class="fas fa-info-circle mr-2"></i> Update locked for 7 days.</div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer admin-modal-footer">
                    <button type="button" class="btn btn-link admin-cancel-link" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary admin-btn-wide" <?= $pass_locked ? 'disabled' : '' ?>>Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showModal(id) {
    new bootstrap.Modal(document.getElementById(id)).show();
}

$(document).ready(function() {
    function handleAjax(formId, modalId) {
        $(`#${formId}`).on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Success!', text: 'Changes updated', timer: 1800, showConfirmButton: false }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        });
    }

    handleAjax('editProfileForm', 'editProfileModal');
    handleAjax('changePasswordForm', 'changePasswordModal');
});
</script>

<?php include './footer.php'; ?>