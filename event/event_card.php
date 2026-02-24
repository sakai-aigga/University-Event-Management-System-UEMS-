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
?>

<div class="event-card" onclick="showEventDetails(<?php echo $row['event_id']; ?>)">
    <span class="free-badge"><?php echo htmlspecialchars($cat_name); ?></span>
    
    <?php if ($is_registered): ?>
        <span class="registered-badge"><i class="fas fa-check-circle"></i> Registered</span>
    <?php elseif ($is_full && !$is_past): ?>
        <span class="registered-badge" style="background: #6c757d;"><i class="fas fa-users-slash"></i> Full</span>
    <?php endif; ?>

    <img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&q=80&w=500" class="event-img" alt="<?php echo htmlspecialchars($row['title']); ?>">
    
    <div class="event-content">
        <p class="date-tag"><?php echo date('M d, Y', strtotime($row['event_date'])); ?></p>
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
                        <a href="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'login/' : '../login/'; ?>" class="btn-register btn-blue" onclick="event.stopPropagation()">Login to Register</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
