<?php
require_once '../../includes/db-config.php';
require_once '../header.php';

// Handle Role Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_role') {
    $u_id = intval($_POST['u_id']);
    $new_role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $update_stmt = $conn->prepare("UPDATE users SET role = ? WHERE u_id = ?");
    $update_stmt->bind_param("si", $new_role, $u_id);
    if ($update_stmt->execute()) {
        echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire('Success', 'Role updated successfully!', 'success').then(() => { window.location.href='index.php'; }); });</script>";
    } else {
        echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire('Error', 'Failed to update role.', 'error'); });</script>";
    }
}

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $u_id = intval($_POST['u_id']);
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE u_id = ?");
    $delete_stmt->bind_param("i", $u_id);
    if ($delete_stmt->execute()) {
        echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire('Deleted!', 'User has been deleted.', 'success').then(() => { window.location.href='index.php'; }); });</script>";
    } else {
        echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire('Error', 'Failed to delete user.', 'error'); });</script>";
    }
}

// Fetch all users
$sql = "SELECT u_id, name, email, contact, role FROM users ORDER BY u_id DESC";
$result = $conn->query($sql);
?>

<div class="row">
    <div class="col-12">
        <div class="card admin-card">
            <div class="card-header admin-card-header">
                <h3 class="card-title">Registered System Users</h3>
            </div>
            <div class="card-body admin-table-wrapper table-responsive">
                <table class="table table-hover admin-table">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): $sn = 1; ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $sn++ ?></td>
                                    <td class="admin-cell-bold"><?= htmlspecialchars($row['name'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['contact'] ?: '-') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                            <?= strtoupper($row['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" title="Change Role" data-bs-toggle="modal" data-bs-target="#changeRoleModal" onclick="setRoleModalData(<?= $row['u_id'] ?>, '<?= $row['role'] ?>', '<?= htmlspecialchars(addslashes($row['name'])) ?>')">
                                            <i class="fas fa-user-cog"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Delete User" data-bs-toggle="modal" data-bs-target="#deleteUserModal" onclick="setDeleteModalData(<?= $row['u_id'] ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="admin-empty-row text-center py-4">No users found in the system.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Change Role Modal -->
<div class="modal fade" id="changeRoleModal" tabindex="-1" aria-labelledby="changeRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeRoleModalLabel">Change User Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_role">
                    <input type="hidden" name="u_id" id="role_u_id">
                    <p>Change role for <strong id="role_user_name"></strong>:</p>
                    <div class="form-group mb-3">
                        <label for="role_select" class="form-label">Role</label>
                        <select name="role" id="role_select" class="form-select" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteUserModalLabel">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="u_id" id="delete_u_id">
                    <p>Are you sure you want to delete <strong id="delete_user_name"></strong>?</p>
                    <p class="text-danger small"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function setRoleModalData(id, role, name) {
        document.getElementById('role_u_id').value = id;
        document.getElementById('role_user_name').textContent = name;
        document.getElementById('role_select').value = role;
    }

    function setDeleteModalData(id, name) {
        document.getElementById('delete_u_id').value = id;
        document.getElementById('delete_user_name').textContent = name;
    }
</script>

<?php require_once '../footer.php'; ?>
