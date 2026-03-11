<?php
// Securely include navigation and session handling
include '../../includes/db-config.php';
include '../header.php';

// Fetch Departments for dropdown
$depts_result = $conn->query("SELECT * FROM departments ORDER BY dept_name ASC");
$departments = [];
if ($depts_result) {
    while ($row = $depts_result->fetch_assoc()) {
        $departments[] = $row;
    }
}

$message = "";
$msg_type = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $title            = $_POST['title'];
    $description      = $_POST['description'];
    $u_id             = $_SESSION['u_id'];
    $category_id      = (int)$_POST['category_id'];
    $dept_id          = !empty($_POST['dept_id']) ? (int)$_POST['dept_id'] : null;
    $max_participants = (int)$_POST['max_participants'];
    $event_date       = $_POST['event_date'];
    $venue            = $_POST['venue'];
    $is_published     = 1;
    $event_image      = "";

    // Handle Image Upload
    if (!empty($_FILES['image_file']['name'])) {
        // Handle file upload
        $file_name = $_FILES['image_file']['name'];
        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_size = $_FILES['image_file']['size'];
        $file_type = $_FILES['image_file']['type'];
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file_type, $allowed_types)) {
            $message = "Invalid file type. Please upload a JPG, PNG, GIF, or WebP image.";
            $msg_type = "error";
        } elseif ($file_size > $max_size) {
            $message = "File size exceeds 5MB limit.";
            $msg_type = "error";
        } else {
            // Read file content for BLOB storage
            $img_data = file_get_contents($file_tmp);
            if ($img_data !== false) {
                $event_image = $img_data;
                $message = "";
                $msg_type = "";
            } else {
                $message = "Failed to process image file.";
                $msg_type = "error";
            }
        }
    }

    // Server-side validation
    if (empty($title) || empty($event_date) || empty($venue)) {
        $message  = "Please fill in all required fields.";
        $msg_type = "warning";
    } elseif ($msg_type !== "error") {
        // Database Insertion using send_long_data for BLOB safety
        $sql = "INSERT INTO event (u_id, title, description, category_id, dept_id, is_published, max_participants, event_date, venue, event_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $null = NULL; // Prepared statement requires a variable for null/blob placeholder
        $stmt->bind_param("issiiiissb", $u_id, $title, $description, $category_id, $dept_id, $is_published, $max_participants, $event_date, $venue, $null);
        
        if (!empty($event_image)) {
            $stmt->send_long_data(9, $event_image); // 9 is the index of event_image (0-indexed)
        }

        if ($stmt->execute()) {
            $message  = "New Event '<strong>" . htmlspecialchars($title) . "</strong>' has been created successfully!";
            $msg_type = "success";
        } else {
            $message  = "System Error: Unable to create event. " . $stmt->error;
            $msg_type = "error";
        }
        $stmt->close();
    }
}
?>

<!-- Main Content Section -->
<div class="row">
    <div class="col-lg-10 admin-create-container">
        
        <!-- UI Header -->
        <div class="admin-create-header">
            <h2 class="admin-create-title">
                <i class="fas fa-plus-square"></i> 
                Administrative Event Creation
            </h2>
            <p class="admin-create-subtitle">Administrative event creation</p>
        </div>

        <div class="card admin-create-card">
            <div class="card-header admin-create-card-header">
                <h3 class="admin-create-card-title">
                    <i class="fas fa-info-circle"></i>
                    Event Configuration
                </h3>
            </div>
            
            <form method="POST" action="" id="createEventForm" enctype="multipart/form-data">
                <div class="card-body admin-form-body">
                    <div class="row">
                        <!-- Left Column: Primary Details -->
                        <div class="col-md-7">
                            <div class="form-group admin-form-spacing">
                                <label for="title" class="admin-form-label-required">Event Title <span class="required-star">*</span></label>
                                <input type="text" class="form-control admin-input-lg" id="title" name="title" placeholder="e.g. Annual Tech Symposium 2026" required>
                                <small class="admin-help-text">A clear, engaging title that represents the event.</small>
                            </div>

                            <div class="form-group admin-form-group-spaced">
                                <label for="description" class="admin-form-label-required">Event Description</label>
                                <textarea class="form-control" id="description" name="description" rows="8" placeholder="Outline the event objectives, agenda, and requirements..." required></textarea>
                            </div>

                            <!-- Image Upload Section -->
                            <div class="form-group admin-form-group-spaced">
                                <label class="admin-form-label-required">Event Banner Image</label>
                                <div class="image-upload-container">
                                    <div class="image-preview-box" id="imagePreview">
                                        <i class="fas fa-image preview-icon"></i>
                                        <p>Image Preview</p>
                                    </div>
                                </div>

                                <!-- Single File Upload -->
                                <div class="image-upload-content-simple">
                                    <div class="custom-file-upload">
                                        <input type="file" class="form-control-file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp">
                                        <small class="admin-help-text">Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Metadata & Logistics -->
                        <div class="col-md-5">
                            <div class="form-group admin-form-spacing">
                                <label for="category_id" class="admin-form-label-required">Event Category <span class="required-star">*</span></label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="" disabled selected>-- Select Official Category --</option>
                                    <option value="1">🎓 General / Academic</option>
                                    <option value="2">🔬 Workshop / Seminar</option>
                                    <option value="3">⚽ Sports & Athletics</option>
                                    <option value="4">🎨 Cultural & Arts</option>
                                </select>
                            </div>

                            <div class="form-group admin-form-group-spaced">
                                <label for="dept_id" class="admin-form-label-required">Assigned Department <span class="required-star">*</span></label>
                                <select class="form-control" id="dept_id" name="dept_id" required>
                                    <option value="" disabled selected>-- Select Department --</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['dept_id'] ?>"><?= htmlspecialchars($dept['dept_name']) ?> (<?= htmlspecialchars($dept['acronym']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="admin-help-text">Which department is hosting this event?</small>
                            </div>

                            <div class="form-group admin-form-group-spaced">
                                <label for="event_date" class="admin-form-label-required">Event Schedule <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt admin-icon-primary"></i></span>
                                    </div>
                                    <input type="text" class="form-control date-picker" id="event_date" name="event_date" placeholder="DD/MM/YYYY" required min="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <div class="form-group admin-form-group-spaced">
                                <label for="max_participants" class="admin-form-label-required">Capacity <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-users admin-icon-primary"></i></span>
                                    </div>
                                    <input type="number" class="form-control" id="max_participants" name="max_participants" required min="1" placeholder="Max participants">
                                </div>
                            </div>

                            <div class="form-group admin-form-group-spaced">
                                <label for="venue" class="admin-form-label-required">Assigned Venue <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt admin-icon-danger"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="venue" name="venue" placeholder="e.g. Block C, Hall 4" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="admin-form-footer">
                    <a href="index.php" class="admin-discard-link"><i class="fas fa-times-circle"></i> Discard Draft</a>
                    <div>
                        <button type="reset" class="btn btn-outline-secondary admin-btn-spaced">Clear Form</button>
                        <button type="submit" class="btn btn-primary admin-btn-submit">
                            <i class="fas fa-rocket"></i> Launch Event
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation and Alert Integration -->
<script>
    $(document).ready(function() {
        // Initialize Flatpickr for DD/MM/YYYY date formatting
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",        // backend format
            altInput: true,             // show formatted string instead
            altFormat: "d/m/Y",         // visual format requested
            minDate: "today",           // no past events
            allowInput: true
        });

        // Image preview functionality
        $('#image_file').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').html(`<img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px;"><button type="button" class="remove-image-btn" id="removeImageBtn" title="Remove Image"><i class="fas fa-times"></i></button>`);
                };
                reader.readAsDataURL(file);
            } else {
                $('#imagePreview').html('<i class="fas fa-image preview-icon"></i><p>Image Preview</p>');
            }
        });

        // Handle Image Removal
        $(document).on('click', '#removeImageBtn', function(e) {
            e.preventDefault();
            $('#image_file').val('');
            $('#imagePreview').html('<i class="fas fa-image preview-icon"></i><p>Image Preview</p>');
        });

        // Handle Form Reset
        $('#createEventForm').on('reset', function() {
            setTimeout(() => {
                $('#imagePreview').html('<i class="fas fa-image preview-icon"></i><p>Image Preview</p>');
            }, 10);
        });

        // Handle Form Submission with Confirmation
        $('#createEventForm').on('submit', function(e) {
            e.preventDefault();
            const form = this;

            Swal.fire({
                title: 'Launch this Event?',
                text: "Are you sure you want to publish this event to the platform? It will be immediately visible to students.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Launch Now!',
                cancelButtonText: 'Review Details',
                background: '#fff',
                customClass: {
                    popup: 'animate__animated animate__fadeInDown'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Actually submit the form
                }
            });
        });

        // Handle Post-Submission Success/Error Messages
        <?php if ($message): ?>
            Swal.fire({
                icon: '<?= $msg_type ?>',
                title: '<?= $msg_type === "success" ? "Event Published!" : ($msg_type === "warning" ? "Notice" : "Execution Error") ?>',
                html: '<?= $message ?>',
                confirmButtonColor: '#007bff',
                background: '#fff',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                if ('<?= $msg_type ?>' === 'success') {
                    window.location.href = 'index.php'; // Redirect admins to events list
                }
            });
        <?php endif; ?>
    });
</script>

<?php include '../footer.php'; ?>