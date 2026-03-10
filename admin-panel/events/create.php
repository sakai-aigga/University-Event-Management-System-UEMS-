<?php
// Securely include navigation and session handling
include '../../includes/db-config.php';
include '../header.php';

$message = "";
$msg_type = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $title            = mysqli_real_escape_string($conn, $_POST['title']);
    $description      = mysqli_real_escape_string($conn, $_POST['description']);
    $category_id      = (int)$_POST['category_id'];
    $max_participants = (int)$_POST['max_participants'];
    $event_date       = $_POST['event_date'];
    $venue            = mysqli_real_escape_string($conn, $_POST['venue']);
    $u_id             = $_SESSION['u_id'];
    $is_published     = 1;

    // Server-side validation
    if (empty($title) || empty($event_date) || empty($venue)) {
        $message  = "Please fill in all required fields.";
        $msg_type = "warning";
    } else {
        // Database Insertion
        $sql = "INSERT INTO event (u_id, title, description, category_id, is_published, max_participants, event_date, venue) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issiiiss", $u_id, $title, $description, $category_id, $is_published, $max_participants, $event_date, $venue);

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
            
            <form method="POST" action="" id="createEventForm">
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
                                <label for="event_date" class="admin-form-label-required">Event Schedule <span class="required-star">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt admin-icon-primary"></i></span>
                                    </div>
                                    <input type="date" class="form-control" id="event_date" name="event_date" required min="<?= date('Y-m-d') ?>">
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