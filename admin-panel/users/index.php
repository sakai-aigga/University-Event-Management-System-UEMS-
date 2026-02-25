<?php
require_once '../../includes/db-config.php';
require_once '../header.php';

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
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['u_id'] ?></td>
                                    <td class="admin-cell-bold"><?= htmlspecialchars($row['name'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['contact'] ?: '-') ?></td>
                                    <td>
                                        <span class="badge badge-<?= $row['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                            <?= strtoupper($row['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-outline-info"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-xs btn-outline-secondary"><i class="fas fa-user-shield"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="admin-empty-row">No users found in the system.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../footer.php'; ?>
