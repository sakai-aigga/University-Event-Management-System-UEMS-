<?php
ob_start();
require_once '../includes/db-config.php';

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
                <span id="live-indicator" style="font-size:0.78rem; color:#6c757d;">
                    <i class="fas fa-circle text-success" style="font-size:0.6rem;"></i> Live
                </span>
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
    return d.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
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
        const markReadBtn = isRead ? '' : `
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
                <button class="btn btn-xs btn-danger delete-notif-btn" data-id="${row.id}" title="Delete">
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

    // Delete
    $('.delete-notif-btn').off('click').on('click', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Inquiry?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '', method: 'POST',
                    data: { action: 'delete', id: id },
                    success: function(res) {
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
                    }
                });
            }
        });
    });
}

$(document).ready(function() {
    // Immediate load
    fetchNotifications();

    // Start live polling
    pollingTimer = setInterval(fetchNotifications, POLL_INTERVAL);
});
</script>

<?php require_once 'footer.php'; ?>
