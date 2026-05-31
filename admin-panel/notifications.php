<?php
ob_start();
require_once '../includes/db-config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authorization check before handling AJAX requests or rendering
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    if (isset($_GET['action']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    header('Location: ../index.php');
    exit;
}

// ─── AJAX: Fetch notifications as JSON ──────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $sql = "SELECT * FROM contact_submissions ORDER BY is_read ASC, submitted_at DESC";
    $result = $conn->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($rows);
    exit;
}

// ─── AJAX: Actions (mark_read / delete) ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => 'Invalid action'];

    if ($_POST['action'] === 'mark_read' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $response = $stmt->execute() ? ['success' => true] : ['success' => false, 'message' => $conn->error];
        ob_clean(); header('Content-Type: application/json');
        echo json_encode($response); exit;
    }

    if ($_POST['action'] === 'mark_unread' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE contact_submissions SET is_read = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $response = $stmt->execute() ? ['success' => true] : ['success' => false, 'message' => $conn->error];
        ob_clean(); header('Content-Type: application/json');
        echo json_encode($response); exit;
    }

    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $response = $stmt->execute() ? ['success' => true] : ['success' => false, 'message' => $conn->error];
        ob_clean(); header('Content-Type: application/json');
        echo json_encode($response); exit;
    }
}

require_once 'header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card admin-card">
            <div class="card-header admin-card-header-flex">
                <h3 class="card-title">
                    <i class="fas fa-bell mr-2" style="color: var(--primary-purple);"></i>
                    User Inquiries &amp; Notifications
                </h3>
            </div>
            <div class="card-body admin-table-wrapper table-responsive">
                <table class="table table-hover admin-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th style="max-width:350px;">Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="notifications-tbody">
                        <tr><td colspan="6" class="admin-empty-row"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const POLL_INTERVAL = 5000; // 5 seconds
let pollingTimer = null;

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function renderRows(data) {
    const tbody = document.getElementById('notifications-tbody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="admin-empty-row">No notifications found.</td></tr>';
        return;
    }

    let html = '';
    data.forEach(row => {
        const isRead = parseInt(row.is_read) === 1;
        const rowClass = isRead ? '' : 'table-active font-weight-700';
        const statusBadge = isRead
            ? '<span class="badge badge-secondary">Read</span>'
            : '<span class="badge badge-danger">New</span>';
        const markReadBtn = isRead ? `
            <button class="btn btn-xs btn-outline-secondary mark-unread-btn me-1" data-id="${row.id}" title="Mark as Unread">
                <i class="fas fa-envelope"></i>
            </button>` : `
            <button class="btn btn-xs btn-success mark-read-btn me-1" data-id="${row.id}" title="Mark as Read">
                <i class="fas fa-check"></i>
            </button>`;

        html += `
        <tr id="notif-row-${row.id}" class="${rowClass}" style="${isRead ? 'opacity:0.75;' : ''}">
            <td>${statusBadge}</td>
            <td style="white-space:nowrap;">${formatDate(row.submitted_at)}</td>
            <td>${escHtml(row.name || 'Unknown')}</td>
            <td>${escHtml(row.email || '—')}</td>
            <td style="max-width:350px; white-space:normal; line-height:1.5; font-size:0.93rem;">${escHtml(row.message || '').replace(/\n/g,'<br>')}</td>
            <td style="white-space:nowrap;">
                ${markReadBtn}
                <button class="btn btn-xs btn-danger delete-notif-btn" data-id="${row.id}" data-name="${escHtml(row.name || 'Unknown')}" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteNotifModal">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
    bindActions();
}

function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function fetchNotifications() {
    $.getJSON('?action=fetch', function(data) {
        renderRows(data);
    });
}

function bindActions() {
    // Mark as read
    $('.mark-read-btn').off('click').on('click', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '', method: 'POST',
            data: { action: 'mark_read', id: id },
            success: function(res) {
                if (res.success) {
                    fetchNotifications();
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            }
        });
    });

    // Mark as unread
    $('.mark-unread-btn').off('click').on('click', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '', method: 'POST',
            data: { action: 'mark_unread', id: id },
            success: function(res) {
                if (res.success) {
                    fetchNotifications();
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            }
        });
    });

    // Delete — open modal and store data
    $('.delete-notif-btn').off('click').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#delete_notif_id').val(id);
        $('#delete_notif_name').text(name);
    });
}

$(document).ready(function() {
    // Immediate load
    fetchNotifications();

    // Start live polling
    pollingTimer = setInterval(fetchNotifications, POLL_INTERVAL);

    // Handle Delete Notification form submission
    $('#deleteNotifForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#delete_notif_id').val();
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteNotifModal'));

        $.ajax({
            url: '', method: 'POST',
            data: { action: 'delete', id: id },
            success: function(res) {
                modal.hide();
                if (res.success) {
                    $(`#notif-row-${id}`).fadeOut(400, function() {
                        $(this).remove();
                        if ($('#notifications-tbody tr').length === 0) {
                            $('#notifications-tbody').html('<tr><td colspan="6" class="admin-empty-row">No notifications found.</td></tr>');
                        }
                    });
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            },
            error: function() {
                modal.hide();
                Swal.fire('Error!', 'Connection failed.', 'error');
            }
        });
    });
});
</script>

<!-- Delete Notification Modal -->
<div class="modal fade" id="deleteNotifModal" tabindex="-1" aria-labelledby="deleteNotifModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteNotifForm">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteNotifModalLabel">Delete Inquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="delete_notif_id">
                    <p>Are you sure you want to delete the inquiry from <strong id="delete_notif_name"></strong>?</p>
                    <p class="text-danger small"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Inquiry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
