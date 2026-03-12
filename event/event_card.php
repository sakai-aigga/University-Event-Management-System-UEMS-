<?php
/**
 * Reusable Event Card Component
 * Expected variables:
 * @var array $row The event data from database
 * @var array $cat_map Category mapping array
 */

$is_registered = isset($row['is_registered']) && $row['is_registered'];
$cat_name = isset($cat_map[$row['category_id']]) ? $cat_map[$row['category_id']] : 'Event';
$is_past = strtotime($row['event_date']) < strtotime(date('Y-m-d'));

// Check if fully booked
$max_cap = isset($row['max_participants']) ? (int)$row['max_participants'] : 0;
$current_part = isset($row['current_participants']) ? (int)$row['current_participants'] : 0;
$is_full = ($max_cap > 0 && $current_part >= $max_cap);

// Handle event image - use database image or curated category-based fallback
if (!empty($row['event_image'])) {
    $img = $row['event_image'];
    if (strpos($img, 'data:image') === 0 || strpos($img, 'http') === 0) {
        $event_image = htmlspecialchars($img);
    } else {
        $event_image = 'data:image/jpeg;base64,' . base64_encode($img);
    }
} else {
    // Curated high-quality placeholder images for different event types
    $placeholders = [
        1 => 'https://images.unsplash.com/photo-1523050853063-bd8012fbb2a0?q=80&w=1000&auto=format&fit=crop', // Academic
        2 => 'https://images.unsplash.com/photo-1540575861501-7c93b177ef96?q=80&w=1000&auto=format&fit=crop', // Workshop
        3 => 'https://images.unsplash.com/photo-1461896756186-009f97c72c9c?q=80&w=1000&auto=format&fit=crop', // Sports
        4 => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?q=80&w=1000&auto=format&fit=crop', // Cultural
    ];
    $event_image = isset($placeholders[$row['category_id']]) ? $placeholders[$row['category_id']] : 'https://images.unsplash.com/photo-1541339907198-e08756ebafe3?q=80&w=1000&auto=format&fit=crop';
}
$fallback_img = "https://images.unsplash.com/photo-1541339907198-e08756ebafe3?q=80&w=1000&auto=format&fit=crop";
?>

<div class="event-card" onclick="showEventDetails(<?php echo $row['event_id']; ?>)">
    <span class="free-badge">
        <?php echo htmlspecialchars($cat_name); ?>
        <?php if(!empty($row['dept_acronym'])): ?>
            | <?php echo htmlspecialchars($row['dept_acronym']); ?>
        <?php endif; ?>
    </span>
    
    <?php if ($is_registered): ?>
        <span class="registered-badge"><i class="fas fa-check-circle"></i> Registered</span>
    <?php elseif ($is_full && !$is_past): ?>
        <span class="registered-badge" style="background: #6c757d;"><i class="fas fa-users-slash"></i> Full</span>
    <?php endif; ?>

    <img src="<?php echo $event_image; ?>" class="event-img" alt="<?php echo htmlspecialchars($row['title']); ?>" onerror="this.src='https://media.gettyimages.com/id/158997850/vector/presenter.jpg?s=612x612&w=0&k=20&c=btE_wy6IQof6ZSDufIg_nElo7CiRV8-Ja5lNPUiecYo=';">
    
    <div class="event-content">
        <p class="date-tag"><?php echo date('d/m/Y', strtotime($row['event_date'])); ?></p>
        <h3 class="event-title"><?php echo htmlspecialchars($row['title']); ?></h3>
        <p class="event-location">📍 <?php echo htmlspecialchars($row['venue']); ?></p>
        
        <?php if (!$is_past): ?>
            <div class="card-footer-action" style="margin-top: auto;">
                <?php if (isset($_SESSION['u_id'])): ?>
                    <?php if ($is_registered): ?>
                        <button type="button" class="btn-register" style="background: #6c757d; cursor: default;" onclick="event.stopPropagation()">Registered</button>
                    <?php elseif ($is_full): ?>
                        <button type="button" class="btn-register" style="background: #e5e7eb; color: #6b7280; cursor: not-allowed;" onclick="event.stopPropagation()">Fully Booked</button>
                    <?php else: ?>
                        <button type="button" class="btn-register" onclick="event.stopPropagation(); showEventDetails(<?php echo $row['event_id']; ?>)">Register Now</button>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($is_full): ?>
                        <button type="button" class="btn-register" style="background: #e5e7eb; color: #6b7280; cursor: not-allowed;" onclick="event.stopPropagation()">Fully Booked</button>
                    <?php else: ?>
                        <button type="button" class="btn-register btn-blue" onclick="event.stopPropagation(); showEventDetails(<?php echo $row['event_id']; ?>, true)">Login to Register</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
