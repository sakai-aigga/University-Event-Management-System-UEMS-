<?php
ob_start();
require_once '../../includes/db-config.php';

// Handle AJAX Request for Department Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_dept') {
    $dept_id = (int)$_POST['dept_id'];
    $stmt = $conn->prepare("DELETE FROM departments WHERE dept_id = ?");
    $stmt->bind_param("i", $dept_id);
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Department deleted successfully'];
    } else {
        $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle AJAX Request for Department Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_dept') {
    $acronym = trim($_POST['acronym']);
    $name = trim($_POST['dept_name']);

    if (empty($acronym) || empty($name)) {
        $response = ['success' => false, 'message' => 'Please fill in all fields'];
    } else {
        $stmt = $conn->prepare("INSERT INTO departments (acronym, dept_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $acronym, $name);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            $response = ['success' => true, 'message' => 'Department added successfully', 'dept_id' => $new_id, 'acronym' => $acronym, 'dept_name' => $name];
        } else {
            $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
    }
    
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle AJAX Request for Department Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_dept') {
    $dept_id = (int)$_POST['dept_id'];
    $acronym = trim($_POST['acronym']);
    $name = trim($_POST['dept_name']);

    if (empty($acronym) || empty($name)) {
        $response = ['success' => false, 'message' => 'Please fill in all fields'];
    } else {
        $stmt = $conn->prepare("UPDATE departments SET acronym = ?, dept_name = ? WHERE dept_id = ?");
        $stmt->bind_param("ssi", $acronym, $name, $dept_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Department updated successfully', 'dept_id' => $dept_id, 'acronym' => $acronym, 'dept_name' => $name];
        } else {
            $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
    }
    
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

require_once '../header.php';

// Fetch all departments
$sql = "SELECT * FROM departments ORDER BY dept_id ASC";
$result = $conn->query($sql);
?>

<div class="row">
    <div class="col-md-8 admin-center-col">
        <div class="card admin-card">
            <div class="card-header admin-card-header-flex">
                <h3 class="card-title">University Departments</h3>
                <button class="btn btn-sm btn-primary admin-btn-add" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                    <i class="fas fa-plus"></i> Add Department
                </button>
            </div>
            <div class="card-body admin-table-wrapper">
                <table class="table table-hover admin-table">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>Acronym</th>
                            <th>Department Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $sn = 1; while($row = $result->fetch_assoc()): ?>
                                <tr id="dept-row-<?= $row['dept_id'] ?>">
                                    <td><?= $sn++ ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($row['acronym'] ?? 'N/A') ?></span></td>
                                    <td><?= htmlspecialchars($row['dept_name'] ?? 'Unnamed Department') ?></td>
                                    <td>
                                        <button class="btn btn-xs btn-primary edit-dept-btn" data-id="<?= $row['dept_id'] ?>" data-acronym="<?= htmlspecialchars($row['acronym'] ?? '') ?>" data-name="<?= htmlspecialchars($row['dept_name'] ?? '') ?>"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-xs btn-danger delete-dept-btn" data-id="<?= $row['dept_id'] ?>" data-name="<?= htmlspecialchars($row['dept_name'] ?? '') ?>" data-bs-toggle="modal" data-bs-target="#deleteDeptModal" onclick="setDeleteDeptData(<?= $row['dept_id'] ?>, '<?= htmlspecialchars(addslashes($row['dept_name'] ?? '')) ?>')"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="admin-empty-row">No departments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function setDeleteDeptData(id, name) {
    document.getElementById('delete_dept_id').value = id;
    document.getElementById('delete_dept_name').textContent = name;
}

$(document).ready(function() {
    // Handle Delete Department via Modal Form
    $('#deleteDeptForm').on('submit', function(e) {
        e.preventDefault();
        const deptId = $('#delete_dept_id').val();
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteDeptModal'));

        $.ajax({
            url: 'index.php',
            method: 'POST',
            data: { action: 'delete_dept', dept_id: deptId },
            success: function(response) {
                modal.hide();
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success');
                        $(`#dept-row-${deptId}`).fadeOut(500, function() { $(this).remove(); });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                } catch(e) {
                    console.error("JSON Error:", e, response);
                }
            },
            error: function() {
                modal.hide();
                Swal.fire('Error!', 'Connection failed.', 'error');
            }
        });
    });

    // Handle Add Department Form Submission
    $('#addDeptForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=add_dept';

        $.ajax({
            url: 'index.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        $('#addDeptModal').modal('hide');
                        $('#addDeptForm')[0].reset();
                        
                        // Append new row without reload
                        const rows = $('table.admin-table tbody tr:not(.admin-empty-row)');
                        const sn = rows.length + 1;
                        const newRow = `
                            <tr id="dept-row-${data.dept_id}" style="display:none;">
                                <td>${sn}</td>
                                <td><span class="badge badge-info">${data.acronym}</span></td>
                                <td>${data.dept_name}</td>
                                <td>
                                    <button class="btn btn-xs btn-primary edit-dept-btn" data-id="${data.dept_id}" data-acronym="${data.acronym}" data-name="${data.dept_name}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-xs btn-danger delete-dept-btn" data-id="${data.dept_id}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                        // Remove empty state message if it exists
                        $('.admin-empty-row').closest('tr').remove();
                        // Append and animate
                        $('table.admin-table tbody').append(newRow);
                        $(`#dept-row-${data.dept_id}`).fadeIn(500);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                } catch(err) {
                    Swal.fire('Error!', 'System received an invalid response.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Connection failed.', 'error');
            }
        });
    });

    // Populate Edit Department Modal
    $(document).on('click', '.edit-dept-btn', function() {
        const deptId = $(this).data('id');
        const acronym = $(this).data('acronym');
        const name = $(this).data('name');
        
        $('#edit_dept_id').val(deptId);
        $('#edit_acronym').val(acronym);
        $('#edit_name').val(name);
        $('#editDeptModal').modal('show');
    });

    // Handle Edit Department Form Submission
    $('#editDeptForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=edit_dept';

        $.ajax({
            url: 'index.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        $('#editDeptModal').modal('hide');
                        $('#editDeptForm')[0].reset();
                        
                        // Update the row dynamically
                        const row = $(`#dept-row-${data.dept_id}`);
                        row.find('td:nth-child(2) span').text(data.acronym);
                        row.find('td:nth-child(3)').text(data.dept_name);
                        
                        // Update the button data attributes
                        const editBtn = row.find('.edit-dept-btn');
                        editBtn.data('acronym', data.acronym);
                        editBtn.data('name', data.dept_name);
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                } catch(err) {
                    Swal.fire('Error!', 'System received an invalid response.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Connection failed.', 'error');
            }
        });
    });
});
</script>

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addDeptForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDeptModalLabel">Add New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="acronym" class="form-label">Acronym (e.g. CS)</label>
                        <input type="text" class="form-control" id="acronym" name="acronym" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="name" class="form-label">Full Department Name</label>
                        <input type="text" class="form-control" id="name" name="dept_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDeptModal" tabindex="-1" aria-labelledby="editDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editDeptForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDeptModalLabel">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_dept_id" name="dept_id">
                    <div class="form-group mb-3">
                        <label for="edit_acronym" class="form-label">Acronym (e.g. CS)</label>
                        <input type="text" class="form-control" id="edit_acronym" name="acronym" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_name" class="form-label">Full Department Name</label>
                        <input type="text" class="form-control" id="edit_name" name="dept_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Department Modal -->
<div class="modal fade" id="deleteDeptModal" tabindex="-1" aria-labelledby="deleteDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteDeptForm">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteDeptModalLabel">Delete Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="delete_dept_id">
                    <p>Are you sure you want to delete <strong id="delete_dept_name"></strong>?</p>
                    <p class="text-danger small"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../footer.php'; ?>
