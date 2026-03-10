<?php
ob_start();
require_once '../../includes/db-config.php';

// Handle AJAX Requests (Delete/Update) - MUST BE AT TOP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => 'Invalid action'];

    if ($_POST['action'] === 'delete' && isset($_POST['event_id'])) {
        $event_id = (int)$_POST['event_id'];
        $stmt = $conn->prepare("DELETE FROM event WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Event deleted successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    if ($_POST['action'] === 'edit' && isset($_POST['event_id'])) {
        $event_id = (int)$_POST['event_id'];
        $title = $_POST['title'];
        $venue = $_POST['venue'];
        $date = $_POST['event_date'];
        $capacity = (int)$_POST['max_participants'];
        $category = (int)$_POST['category_id'];
        $desc = $_POST['description'];

        $stmt = $conn->prepare("UPDATE event SET title=?, venue=?, event_date=?, max_participants=?, category_id=?, description=? WHERE event_id=?");
        $stmt->bind_param("sssiisi", $title, $venue, $date, $capacity, $category, $desc, $event_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Changes updated'];
        } else {
            $response = ['success' => false, 'message' => 'Update failed: ' . $conn->error];
        }
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

require_once '../header.php';

// Fetch Active events
$sql_active = "SELECT e.*, u.name as organizer 
               FROM event e 
               LEFT JOIN users u ON e.u_id = u.u_id 
               WHERE e.event_date >= CURDATE()
               ORDER BY e.event_date ASC";
$result_active = $conn->query($sql_active);

// Fetch Past events
$sql_past = "SELECT e.*, u.name as organizer 
             FROM event e 
             LEFT JOIN users u ON e.u_id = u.u_id 
             WHERE e.event_date < CURDATE()
             ORDER BY e.event_date DESC";
$result_past = $conn->query($sql_past);

$categories = [
    1 => "🎓 General",
    2 => "🔬 Workshop",
    3 => "⚽ Sports",
    4 => "🎨 Cultural"
];
?>

<div class="row">
    <div class="col-12">
        <div class="card admin-card">
            <div class="card-header admin-card-header-flex">
                <h3 class="card-title"><i class="fas fa-calendar-check text-success mr-2"></i> Active Events</h3>
                <div class="card-tools">
                    <a href="create.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> New Event
                    </a>
                </div>
            </div>
            <div class="card-body admin-table-wrapper table-responsive">
                <table class="table table-hover admin-table">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>Event Title</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_active->num_rows > 0): $sn = 1; ?>
                            <?php while($row = $result_active->fetch_assoc()): ?>
                                <tr id="event-row-<?= $row['event_id'] ?>">
                                    <td><?= $sn++ ?></td>
                                    <td>
                                        <div class="admin-event-title"><?= htmlspecialchars($row['title']) ?></div>
                                        <small class="badge badge-light"><?= $categories[$row['category_id']] ?? 'Other' ?></small>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($row['event_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['venue']) ?></td>
                                    <td><?= $row['max_participants'] ?></td>
                                    <td>
                                        <?php if ($row['is_published']): ?>
                                            <span class="badge badge-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-info edit-event-btn" 
                                                data-id="<?= $row['event_id'] ?>"
                                                data-title="<?= htmlspecialchars($row['title']) ?>"
                                                data-venue="<?= htmlspecialchars($row['venue']) ?>"
                                                data-date="<?= $row['event_date'] ?>"
                                                data-capacity="<?= $row['max_participants'] ?>"
                                                data-category="<?= $row['category_id'] ?>"
                                                data-description="<?= htmlspecialchars($row['description']) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger delete-event-btn" data-id="<?= $row['event_id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="admin-empty-row">No active events found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Past Events Card -->
        <div class="card admin-card mt-4">
            <div class="card-header admin-card-header-flex">
                <h3 class="card-title text-muted"><i class="fas fa-history mr-2"></i> Past Events</h3>
            </div>
            <div class="card-body admin-table-wrapper table-responsive">
                <table class="table table-hover admin-table">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>Event Title</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_past->num_rows > 0): $sn = 1; ?>
                            <?php while($row = $result_past->fetch_assoc()): ?>
                                <tr id="event-row-<?= $row['event_id'] ?>">
                                    <td><?= $sn++ ?></td>
                                    <td>
                                        <div class="admin-event-title text-muted"><?= htmlspecialchars($row['title']) ?></div>
                                        <small class="badge badge-light"><?= $categories[$row['category_id']] ?? 'Other' ?></small>
                                    </td>
                                    <td class="text-muted"><?= date('M d, Y', strtotime($row['event_date'])) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($row['venue']) ?></td>
                                    <td class="text-muted"><?= $row['max_participants'] ?></td>
                                    <td>
                                        <span class="badge badge-secondary">Ended</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-info edit-event-btn" 
                                                data-id="<?= $row['event_id'] ?>"
                                                data-title="<?= htmlspecialchars($row['title']) ?>"
                                                data-venue="<?= htmlspecialchars($row['venue']) ?>"
                                                data-date="<?= $row['event_date'] ?>"
                                                data-capacity="<?= $row['max_participants'] ?>"
                                                data-category="<?= $row['category_id'] ?>"
                                                data-description="<?= htmlspecialchars($row['description']) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger delete-event-btn" data-id="<?= $row['event_id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="admin-empty-row">No past events found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header-primary">
                <h5 class="modal-title admin-modal-title"><i class="fas fa-edit mr-2"></i> Edit Event Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEventForm">
                <input type="hidden" name="event_id" id="edit_event_id">
                <input type="hidden" name="action" value="edit">
                <div class="modal-body admin-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group admin-form-spacing">
                                <label class="admin-form-label">Event Title</label>
                                <input type="text" class="form-control admin-input-flat" name="title" id="edit_title" required>
                            </div>
                            <div class="form-group admin-form-spacing">
                                <label class="admin-form-label">Venue</label>
                                <input type="text" class="form-control admin-input-flat" name="venue" id="edit_venue" required>
                            </div>
                            <div class="form-group admin-form-spacing">
                                <label class="admin-form-label">Event Date</label>
                                <input type="date" class="form-control admin-input-flat" name="event_date" id="edit_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group admin-form-spacing">
                                <label class="admin-form-label">Category</label>
                                <select class="form-control admin-input-flat" name="category_id" id="edit_category" required>
                                    <?php foreach ($categories as $id => $name): ?>
                                        <option value="<?= $id ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group admin-form-spacing">
                                <label class="admin-form-label">Max Participants</label>
                                <input type="number" class="form-control admin-input-flat" name="max_participants" id="edit_capacity" required>
                            </div>
                            <div class="form-group admin-form-no-margin">
                                <label class="admin-form-label">Description</label>
                                <textarea class="form-control admin-input-flat" name="description" id="edit_description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer admin-modal-footer">
                    <button type="button" class="btn btn-link admin-cancel-link" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary admin-btn-wide">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Bootstrap 5 Modal
    const editModalEl = document.getElementById('editEventModal');
    const editModal = new bootstrap.Modal(editModalEl);

    // Populate Delete logic
    $('.delete-event-btn').on('click', function() {
        const eventId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This event will be permanently removed from the system!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '', // current page
                    method: 'POST',
                    data: { action: 'delete', event_id: eventId },
                    success: function(response) {
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                Swal.fire('Deleted!', data.message, 'success');
                                $(`#event-row-${eventId}`).fadeOut(500, function() { $(this).remove(); });
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        } catch(e) {
                            console.error("JSON Error:", e, response);
                        }
                    }
                });
            }
        });
    });

    // Populate Edit Modal
    $('.edit-event-btn').on('click', function() {
        const btn = $(this);
        $('#edit_event_id').val(btn.data('id'));
        $('#edit_title').val(btn.data('title'));
        $('#edit_venue').val(btn.data('venue'));
        $('#edit_date').val(btn.data('date'));
        $('#edit_capacity').val(btn.data('capacity'));
        $('#edit_category').val(btn.data('category'));
        $('#edit_description').val(btn.data('description'));
        editModal.show();
    });

    // Handle Edit Form Submission
    $('#editEventForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: '',
            method: 'POST',
            data: formData,
            success: function(response) {
                editModal.hide();
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Changes updated',
                            timer: 2000,
                            showConfirmButton: false,
                            background: '#fff',
                            backdrop: `rgba(0,0,123,0.4)`
                        }).then(() => {
                            location.reload(); 
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                } catch(err) {
                    Swal.fire('Error!', 'System received an invalid response.', 'error');
                }
            },
            error: function() {
                editModal.hide();
                Swal.fire('Error!', 'Connection failed.', 'error');
            }
        });
    });
});
</script>

<?php require_once '../footer.php'; ?>
