<?php
ob_start();
require_once '../../includes/db-config.php';

// Helper function to compress and resize images to prevent database packet errors
function compressEventImage($source_path, $quality = 60, $max_width = 1200) {
    if (!extension_loaded('gd')) return file_get_contents($source_path);
    
    $info = getimagesize($source_path);
    if (!$info) return file_get_contents($source_path);

    $type = $info[2];
    switch ($type) {
        case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($source_path); break;
        case IMAGETYPE_PNG:  $image = imagecreatefrompng($source_path); break;
        case IMAGETYPE_GIF:  $image = imagecreatefromgif($source_path); break;
        case IMAGETYPE_WEBP: $image = imagecreatefromwebp($source_path); break;
        default: return file_get_contents($source_path);
    }

    // Resize if too wide
    $width = imagesx($image);
    $height = imagesy($image);
    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = floor($height * ($max_width / $width));
        $tmp_img = imagecreatetruecolor($new_width, $new_height);
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($tmp_img, false);
            imagesavealpha($tmp_img, true);
        }
        imagecopyresampled($tmp_img, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        $image = $tmp_img;
    }

    ob_start();
    imagejpeg($image, NULL, $quality); // Convert to JPG and compress
    $compressed_data = ob_get_clean();
    imagedestroy($image);
    return $compressed_data;
}

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

    if ($_POST['action'] === 'fetch_participants' && isset($_POST['event_id'])) {
        $event_id = (int)$_POST['event_id'];
        $sql = "SELECT u.*, r.reg_date, r.attendance_status, d.dept_name 
                FROM registration r 
                JOIN users u ON r.u_id = u.u_id 
                LEFT JOIN departments d ON u.dept_id = d.dept_id
                WHERE r.event_id = ? 
                ORDER BY r.reg_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $participants = [];
        while ($row = $result->fetch_assoc()) {
            $img = '';
            if (!empty($row['profile_image'])) {
                $raw_img = $row['profile_image'];
                if (strpos($raw_img, 'data:image') === 0 || strpos($raw_img, 'http') === 0) {
                    $img = $raw_img;
                } else {
                    $img = 'data:image/jpeg;base64,' . base64_encode($raw_img);
                }
            } else {
                $img = 'https://ui-avatars.com/api/?name=' . urlencode($row['name']) . '&background=random';
            }

            $participants[] = [
                'u_id' => $row['u_id'],
                'name' => htmlspecialchars($row['name']),
                'email' => htmlspecialchars($row['email']),
                'contact' => htmlspecialchars($row['contact'] ?? 'N/A'),
                'role' => strtoupper($row['role']),
                'dept' => htmlspecialchars($row['dept_name'] ?? 'N/A'),
                'reg_date' => date('d/m/Y H:i', strtotime($row['reg_date'])),
                'profile_image' => $img,
                // Including hidden fields as requested (though not normally displayed)
                'password' => '******', // Security placeholder for the hash
                'password_updated' => $row['password_updated_at'] ? date('d/m/Y H:i', strtotime($row['password_updated_at'])) : 'Never'
            ];
        }
        $stmt->close();
        
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'participants' => $participants]);
        exit;
    }

    if ($_POST['action'] === 'edit' && isset($_POST['event_id'])) {
        $event_id = (int)$_POST['event_id'];
        $title = $_POST['title'];
        $venue = $_POST['venue'];
        $date = $_POST['event_date'];
        $capacity = (int)$_POST['max_participants'];
        $category = (int)$_POST['category_id'];
        $dept_id  = !empty($_POST['dept_id']) ? (int)$_POST['dept_id'] : null;
        $desc     = $_POST['description'];

        // Handle image upload/update
        $update_image = false;
        $event_image = null;

        if (!empty($_FILES['image_file']['name'])) {
            $file_tmp = $_FILES['image_file']['tmp_name'];
            $file_size = $_FILES['image_file']['size'];
            $file_type = $_FILES['image_file']['type'];
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 16 * 1024 * 1024;
            
            if (!in_array($file_type, $allowed_types)) {
                $response = ['success' => false, 'message' => 'Invalid file type. Please upload JPG, PNG, GIF, or WebP.'];
                ob_clean(); header('Content-Type: application/json'); echo json_encode($response); exit;
            } elseif ($file_size > $max_size) {
                $response = ['success' => false, 'message' => 'File size exceeds 16MB limit.'];
                ob_clean(); header('Content-Type: application/json'); echo json_encode($response); exit;
            } else {
                $img_data = compressEventImage($file_tmp);
                if ($img_data !== false) {
                    $event_image = $img_data;
                    $update_image = true;
                } else {
                    $response = ['success' => false, 'message' => 'Failed to process image file.'];
                    ob_clean(); header('Content-Type: application/json'); echo json_encode($response); exit;
                }
            }
        } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            $event_image = null; 
            $update_image = true;
        }

        if ($update_image) {
            $sql = "UPDATE event SET title=?, venue=?, event_date=?, max_participants=?, category_id=?, dept_id=?, description=?, event_image=? WHERE event_id=?";
            $stmt = $conn->prepare($sql);
            $null = null;
            $stmt->bind_param("sssiiisbi", $title, $venue, $date, $capacity, $category, $dept_id, $desc, $null, $event_id);
            if ($event_image !== null && $event_image !== '') {
                $stmt->send_long_data(7, $event_image);
            }
        } else {
            $sql = "UPDATE event SET title=?, venue=?, event_date=?, max_participants=?, category_id=?, dept_id=?, description=? WHERE event_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiiisi", $title, $venue, $date, $capacity, $category, $dept_id, $desc, $event_id);
        }
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Event details updated successfully'];
        } else {
            $err = $conn->error;
            $msg = 'Update failed: ' . $err;
            if (strpos($err, 'max_allowed_packet') !== false || strpos($err, 'Packet too large') !== false) {
                $msg = 'Image file is too complex for the database. Try a smaller image or ask Admin to increase MySQL max_allowed_packet.';
            }
            $response = ['success' => false, 'message' => $msg];
        }
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

require_once '../header.php';

$categories = [
    1 => "🎓 General",
    2 => "🔬 Workshop",
    3 => "⚽ Sports",
    4 => "🎨 Cultural"
];

$placeholders = [
    1 => 'https://images.unsplash.com/photo-1523050853063-bd8012fbb2a0?q=80&w=1000&auto=format&fit=crop', // Academic
    2 => 'https://images.unsplash.com/photo-1540575861501-7c93b177ef96?q=80&w=1000&auto=format&fit=crop', // Workshop
    3 => 'https://images.unsplash.com/photo-1461896756186-009f97c72c9c?q=80&w=1000&auto=format&fit=crop', // Sports
    4 => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?q=80&w=1000&auto=format&fit=crop', // Cultural
];
$default_placeholder = 'https://images.unsplash.com/photo-1541339907198-e08756ebafe3?q=80&w=1000&auto=format&fit=crop';

// Fetch Departments
$dept_list = [];
$d_res = $conn->query("SELECT * FROM departments ORDER BY dept_name ASC");
if ($d_res) while($r = $d_res->fetch_assoc()) $dept_list[] = $r;

// Filtering Logic
$f_dept = isset($_GET['dept_id']) ? (int)$_GET['dept_id'] : null;
$f_cat  = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

$where_clause = "";
if ($f_dept) $where_clause .= " AND e.dept_id = $f_dept";
if ($f_cat)  $where_clause .= " AND e.category_id = $f_cat";

// Fetch Active events
$sql_active = "SELECT e.*, u.name as organizer, d.acronym as dept_acronym 
               FROM event e 
               LEFT JOIN users u ON e.u_id = u.u_id 
               LEFT JOIN departments d ON e.dept_id = d.dept_id
               WHERE e.event_date >= CURDATE() $where_clause
               ORDER BY e.event_date ASC";
$result_active = $conn->query($sql_active);
if (!$result_active) die("Active Query Failed: " . $conn->error);

// Fetch Past events
$sql_past = "SELECT e.*, u.name as organizer, d.acronym as dept_acronym 
             FROM event e 
             LEFT JOIN users u ON e.u_id = u.u_id 
             LEFT JOIN departments d ON e.dept_id = d.dept_id
             WHERE e.event_date < CURDATE() $where_clause
             ORDER BY e.event_date DESC";
$result_past = $conn->query($sql_past);
if (!$result_past) die("Past Query Failed: " . $conn->error);
?>

<div class="row">
    <div class="col-12">
        <div class="card admin-card">
            <div class="card-header admin-card-header-flex">
                <h3 class="card-title"><i class="fas fa-calendar-check text-success mr-2"></i> Active Events</h3>
                <div class="d-flex align-items-center">
                    <!-- Integrated Filter Form -->
                    <form method="GET" class="form-inline mr-3">
                        <select name="dept_id" class="form-control form-control-sm mr-2" style="width: auto;">
                            <option value="">All Departments</option>
                            <?php foreach ($dept_list as $d): ?>
                                <option value="<?= $d['dept_id'] ?>" <?= $f_dept == $d['dept_id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['acronym']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="category_id" class="form-control form-control-sm mr-2" style="width: auto;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $id => $name): ?>
                                <option value="<?= $id ?>" <?= $f_cat == $id ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-xs btn-outline-primary mr-1"><i class="fas fa-filter"></i></button>
                        <?php if ($f_dept || $f_cat): ?>
                            <a href="index.php" class="btn btn-xs btn-outline-secondary"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </form>

                    <div class="card-tools">
                        <a href="create.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> New Event
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body admin-table-wrapper table-responsive">
                <table class="table table-hover admin-table">
                    <colgroup>
                        <col style="width: 50px;">
                        <col style="width: 25%;">
                        <col style="width: 10%;">
                        <col style="width: 100px;">
                        <col style="width: 20%;">
                        <col style="width: 80px;">
                        <col style="width: 100px;">
                        <col style="width: 120px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>Event Title</th>
                            <th>Dept</th>
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
                                    <td class="text-truncate-cell"><span class="badge badge-info"><?= htmlspecialchars($row['dept_acronym'] ?? '-') ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($row['event_date'])) ?></td>
                                    <td class="text-truncate-cell" title="<?= htmlspecialchars($row['venue']) ?>"><?= htmlspecialchars($row['venue']) ?></td>
                                    <td><?= $row['max_participants'] ?></td>
                                    <td>
                                        <?php if ($row['is_published']): ?>
                                            <span class="badge badge-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $has_banner = !empty($row['event_image']);
                                            $preview_img = '';
                                            if ($has_banner) {
                                                $raw_img = $row['event_image'];
                                                if (strpos($raw_img, 'data:image') === 0 || strpos($raw_img, 'http') === 0) {
                                                    $preview_img = $raw_img;
                                                } else {
                                                    $preview_img = 'data:image/jpeg;base64,' . base64_encode($raw_img);
                                                }
                                            } else {
                                                $preview_img = $placeholders[$row['category_id']] ?? $default_placeholder;
                                            }
                                        ?>
                                        <button class="btn btn-xs btn-primary view-participants-btn" 
                                                data-id="<?= $row['event_id'] ?>"
                                                data-title="<?= htmlspecialchars($row['title']) ?>">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <button class="btn btn-xs btn-info edit-event-btn" 
                                                data-id="<?= $row['event_id'] ?>"
                                                data-title="<?= htmlspecialchars($row['title']) ?>"
                                                data-venue="<?= htmlspecialchars($row['venue']) ?>"
                                                data-date="<?= $row['event_date'] ?>"
                                                data-capacity="<?= $row['max_participants'] ?>"
                                                data-category="<?= $row['category_id'] ?>"
                                                data-dept="<?= $row['dept_id'] ?>"
                                                data-description="<?= htmlspecialchars($row['description']) ?>"
                                                data-has-image="<?= $has_banner ? '1' : '0' ?>"
                                                data-image="<?= $preview_img ?>">
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
                                <td colspan="8" class="admin-empty-row">No active events found.</td>
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
                    <colgroup>
                        <col style="width: 50px;">
                        <col style="width: 25%;">
                        <col style="width: 10%;">
                        <col style="width: 100px;">
                        <col style="width: 20%;">
                        <col style="width: 80px;">
                        <col style="width: 100px;">
                        <col style="width: 120px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>Event Title</th>
                            <th>Dept</th>
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
                                    <td class="text-truncate-cell"><span class="badge badge-secondary"><?= htmlspecialchars($row['dept_acronym'] ?? '-') ?></span></td>
                                    <td class="text-muted"><?= date('d/m/Y', strtotime($row['event_date'])) ?></td>
                                    <td class="text-truncate-cell text-muted" title="<?= htmlspecialchars($row['venue']) ?>"><?= htmlspecialchars($row['venue']) ?></td>
                                    <td class="text-muted"><?= $row['max_participants'] ?></td>
                                    <td>
                                        <span class="badge badge-secondary">Ended</span>
                                    </td>
                                    <td>
                                        <?php 
                                            $has_banner_past = !empty($row['event_image']);
                                            $preview_img_past = '';
                                            if ($has_banner_past) {
                                                $raw_img = $row['event_image'];
                                                if (strpos($raw_img, 'data:image') === 0 || strpos($raw_img, 'http') === 0) {
                                                    $preview_img_past = $raw_img;
                                                } else {
                                                    $preview_img_past = 'data:image/jpeg;base64,' . base64_encode($raw_img);
                                                }
                                            } else {
                                                $preview_img_past = $placeholders[$row['category_id']] ?? $default_placeholder;
                                            }
                                        ?>
                                        <button class="btn btn-xs btn-primary view-participants-btn" 
                                                data-id="<?= $row['event_id'] ?>"
                                                data-title="<?= htmlspecialchars($row['title']) ?>">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <button class="btn btn-xs btn-info edit-event-btn" 
                                                data-id="<?= $row['event_id'] ?>"
                                                data-title="<?= htmlspecialchars($row['title']) ?>"
                                                data-venue="<?= htmlspecialchars($row['venue']) ?>"
                                                data-date="<?= $row['event_date'] ?>"
                                                data-capacity="<?= $row['max_participants'] ?>"
                                                data-category="<?= $row['category_id'] ?>"
                                                data-dept="<?= $row['dept_id'] ?>"
                                                data-description="<?= htmlspecialchars($row['description']) ?>"
                                                data-has-image="<?= $has_banner_past ? '1' : '0' ?>"
                                                data-image="<?= $preview_img_past ?>">
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
                                <td colspan="8" class="admin-empty-row">No past events found.</td>
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
            <form id="editEventForm" enctype="multipart/form-data">
                <input type="hidden" name="event_id" id="edit_event_id">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="remove_image" id="edit_remove_image" value="0">
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
                                <input type="text" class="form-control admin-input-flat edit-date-picker" name="event_date" id="edit_date" placeholder="DD/MM/YYYY" required>
                            </div>
                            <div class="form-group admin-form-spacing">
                                <label class="admin-form-label">Category</label>
                                <select class="form-control admin-input-flat" name="category_id" id="edit_category" required>
                                    <?php foreach ($categories as $id => $name): ?>
                                        <option value="<?= $id ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group admin-form-spacing">
                                <label class="admin-form-label">Department</label>
                                <select class="form-control admin-input-flat" name="dept_id" id="edit_dept" required>
                                    <option value="">-- Select Department --</option>
                                    <?php foreach ($dept_list as $dept): ?>
                                        <option value="<?= $dept['dept_id'] ?>"><?= htmlspecialchars($dept['dept_name']) ?></option>
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="admin-form-label">Event Banner Image</label>
                                <style>
                                    .img-preview-mod { 
                                        transition: all 0.3s ease; 
                                        cursor: zoom-in; 
                                        width: 100%; 
                                        height: 150px; 
                                        object-fit: cover; 
                                        border-radius: 8px; 
                                    }
                                    .img-preview-mod.expanded { 
                                        cursor: zoom-out; 
                                        height: auto; 
                                        max-height: 60vh; 
                                        object-fit: contain; 
                                        background: #333;
                                    }
                                </style>
                                <div id="editImagePreview" class="border p-2 mb-2 text-center" style="min-height: 150px; background: #f9f9f9; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image edit-preview-icon"></i>
                                    <p>No image set</p>
                                </div>
                                <div class="edit-image-content-simple">
                                    <input type="file" class="form-control admin-input-flat" id="edit_image_file" name="image_file" accept="image/*">
                                    <small class="admin-help-text">JPG, PNG, GIF, WebP. Max 16MB.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="editRemoveImageBtn" style="display:none;">
                                    <i class="fas fa-trash-alt"></i> Remove Image
                                </button>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEventConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header-secondary bg-danger">
                <h5 class="modal-title admin-modal-title text-white"><i class="fas fa-trash-alt mr-2"></i> Delete Event</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body text-center py-4">
                <input type="hidden" id="delete_event_id">
                <div class="display-4 text-danger mb-3"><i class="fas fa-exclamation-triangle"></i></div>
                <p>Are you sure you want to delete <strong id="delete_event_name_display"></strong>?</p>
                <p class="text-muted small">This action will permanently remove the event and all associated registrations. This cannot be undone.</p>
            </div>
            <div class="modal-footer admin-modal-footer">
                <button type="button" class="btn btn-link admin-cancel-link" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger admin-btn-wide">Delete Event</button>
            </div>
        </div>
    </div>
</div>

<!-- Participants Modal -->
<div class="modal fade" id="participantsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header-info bg-info text-white">
                <h5 class="modal-title admin-modal-title"><i class="fas fa-users mr-2"></i> Participants: <span id="modal_event_title"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <div class="table-responsive">
                    <table class="table table-hover admin-table" id="participantsTable">
                        <thead>
                            <tr>
                                <th>S.N.</th>
                                <th>User</th>
                                <th>Email/Contact</th>
                                <th>Dept/Role</th>
                                <th>Reg. Date</th>
                            </tr>
                        </thead>
                        <tbody id="participantsListBody">
                            <!-- Data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div id="noParticipantsMsg" class="text-center py-4" style="display:none;">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No participants have registered for this event yet.</p>
                </div>
            </div>
            <div class="modal-footer admin-modal-footer">
                <button type="button" class="btn btn-secondary admin-btn-wide" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const editModalEl = document.getElementById('editEventModal');
    const editModal = new bootstrap.Modal(editModalEl);
    const editDatePicker = flatpickr("#edit_date", { 
        dateFormat: "Y-m-d", 
        altInput: true, 
        altFormat: "d/m/Y",
        allowInput: true
    });

    // Image Expansion Logic
    $('#editImagePreview').on('click', 'img', function() { $(this).toggleClass('expanded'); });

    // Populate Delete logic
    $(document).on('click', '.delete-event-btn', function() {
        const eventId = $(this).data('id');
        const eventTitle = $(this).closest('tr').find('.admin-event-title').text() || 'this event';
        
        $('#delete_event_id').val(eventId);
        $('#delete_event_name_display').text(eventTitle);
        const delModal = new bootstrap.Modal(document.getElementById('deleteEventConfirmationModal'));
        delModal.show();
    });

    // Handle Delete Confirmation Submit
    $('#confirmDeleteBtn').on('click', function() {
        const eventId = $('#delete_event_id').val();
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

        $.ajax({
            url: '', 
            method: 'POST',
            data: { action: 'delete', event_id: eventId },
            success: function(response) {
                bootstrap.Modal.getInstance(document.getElementById('deleteEventConfirmationModal')).hide();
                btn.prop('disabled', false).html('Delete Event');
                
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $(`#event-row-${eventId}`).fadeOut(500, function() { $(this).remove(); });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                } catch(e) {
                    console.error("JSON Error:", e, response);
                }
            },
            error: function() {
                btn.prop('disabled', false).html('Delete Event');
                Swal.fire('Error!', 'Connection failed.', 'error');
            }
        });
    });

    // Populate Edit Modal
    $(document).on('click', '.edit-event-btn', function() {
        const btn = $(this);
        $('#edit_event_id').val(btn.data('id'));
        $('#edit_title').val(btn.data('title'));
        $('#edit_venue').val(btn.data('venue'));
        editDatePicker.setDate(btn.data('date'));
        $('#edit_capacity').val(btn.data('capacity'));
        $('#edit_category').val(btn.data('category'));
        $('#edit_dept').val(btn.data('dept'));
        $('#edit_description').val(btn.data('description'));
        
        // Reset image fields
        $('#edit_image_file').val('');
        $('#edit_remove_image').val('0');
        
        // Show current image preview
        const hasImage = btn.data('has-image') == '1';
        const img = btn.data('image');
        const previewBox = $('#editImagePreview');
        
        if (hasImage) {
            previewBox.html(`<img src="${img}" class="img-preview-mod">`);
            $('#editRemoveImageBtn').show();
        } else {
            previewBox.html('<i class="fas fa-image edit-preview-icon"></i><p>No banner uploaded (using placeholder)</p>');
            $('#editRemoveImageBtn').hide();
        }
        
        editModal.show();
    });

    // Image preview for file input
    $('#edit_image_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            if (file.size > 16 * 1024 * 1024) {
                Swal.fire('Error', 'File size exceeds 16MB limit.', 'error');
                $(this).val('');
                return;
            }
            const reader = new FileReader();
            reader.onload = e => $('#editImagePreview').html(`<img src="${e.target.result}" class="img-preview-mod">`);
            reader.readAsDataURL(file);
            $('#editRemoveImageBtn').show();
        }
    });

    // Remove image button
    $('#editRemoveImageBtn').on('click', function() {
        $('#edit_remove_image').val('1');
        $('#edit_image_file').val('');
        $('#editImagePreview').html('<i class="fas fa-image edit-preview-icon"></i><p>Image removed</p>');
        $(this).hide();
    });

    // View Participants Logic
    $(document).on('click', '.view-participants-btn', function() {
        const eventId = $(this).data('id');
        const eventTitle = $(this).data('title');
        $('#modal_event_title').text(eventTitle);
        
        const modal = new bootstrap.Modal(document.getElementById('participantsModal'));
        const listBody = $('#participantsListBody');
        const noMsg = $('#noParticipantsMsg');
        const table = $('#participantsTable');

        listBody.html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading participants...</td></tr>');
        noMsg.hide();
        table.show();
        modal.show();

        $.ajax({
            url: '',
            method: 'POST',
            data: { action: 'fetch_participants', event_id: eventId },
            success: function(response) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        if (data.participants.length > 0) {
                            let html = '';
                            data.participants.forEach((p, index) => {
                                html += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="${p.profile_image}" class="rounded-circle mr-2" style="width: 35px; height: 35px; object-fit: cover; border: 2px solid var(--primary-purple);">
                                                <div>
                                                    <div><strong>${p.name}</strong></div>
                                                    <small class="text-muted">ID: #${p.u_id}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>${p.email}</div>
                                            <small class="text-muted"><i class="fas fa-phone-alt mr-1"></i>${p.contact}</small>
                                        </td>
                                        <td>
                                            <div><span class="badge badge-outline-info">${p.dept}</span></div>
                                            <small class="badge badge-pill badge-light">${p.role}</small>
                                        </td>
                                        <td>${p.reg_date}</td>
                                    </tr>
                                `;
                            });
                            listBody.html(html);
                        } else {
                            table.hide();
                            noMsg.show();
                        }
                    } else {
                        listBody.html(`<tr><td colspan="6" class="text-center text-danger">${data.message}</td></tr>`);
                    }
                } catch(err) {
                    listBody.html('<tr><td colspan="6" class="text-center text-danger">Failed to parse response.</td></tr>');
                }
            },
            error: function() {
                listBody.html('<tr><td colspan="6" class="text-center text-danger">Connection failed.</td></tr>');
            }
        });
    });

    // Handle Edit Form Submission
    $('#editEventForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        editModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
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
                Swal.fire('Error!', 'Connection failed.', 'error');
            }
        });
    });
});
</script>

<?php require_once '../footer.php'; ?>
