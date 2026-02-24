<!-- Event Detail Modal Component -->
<div id="eventDetailModal" class="detail-modal">
    <div class="detail-modal-content">
        <div class="detail-modal-header">
            <h2 id="modalTitle">Event Title</h2>
            <span class="close-detail-modal" onclick="closeModal()">&times;</span>
        </div>
        <div class="detail-modal-body">
            <div id="regMessage" class="message" style="display: none; margin-bottom: 15px;"></div>
            
            <div id="mainModalContent">
                <img src="" id="modalImg" class="detail-modal-img" alt="Event">
                <div class="detail-modal-info-grid">
                    <div class="detail-info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="modalDate">Date</span>
                    </div>
                    <div class="detail-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span id="modalVenue">Venue</span>
                    </div>
                    <div class="detail-info-item">
                        <i class="fas fa-tag"></i>
                        <span id="modalCategory">Category</span>
                    </div>
                    <div class="detail-info-item">
                        <i class="fas fa-users"></i>
                        <span id="modalCap">Capacity</span>
                    </div>
                </div>
                <div class="detail-modal-description">
                    <p id="modalDesc">Description goes here...</p>
                </div>
            </div>

            <div id="registrationFormContainer" style="display: none; padding: 20px 0;">
                <h3 style="margin-bottom: 15px; color: var(--primary-purple);">Complete Registration</h3>
                <form id="ajaxRegForm">
                    <input type="hidden" name="event_id" id="regEventId">
                    <?php if (!isset($_SESSION['u_id'])): ?>
                        <p style="margin-bottom: 20px; font-size: 14px; color: #666;">Please login to register for this event:</p>
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-wrapper">
                                <input type="email" name="email" placeholder="Enter email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-wrapper">
                                <input type="password" name="password" placeholder="Enter password" required>
                            </div>
                        </div>
                    <?php else: ?>
                        <p style="margin-bottom: 20px;">Registering as <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></p>
                    <?php endif; ?>
                    <button type="submit" id="submitRegBtn" class="btn-pink" style="width: 100%;">Confirm Registration</button>
                    <button type="button" onclick="toggleRegForm(false)" class="btn-detail-modal-close" style="width: 100%; margin-top: 10px; border: 1px solid #ddd;">Cancel</button>
                </form>
            </div>
        </div>
        <div class="detail-modal-footer" id="modalFooterActions" style="display: flex; justify-content: flex-end; gap: 10px; align-items: center;">
            <button type="button" id="modalRegLink" onclick="toggleRegForm(true)" class="btn-register" style="width: auto; padding: 10px 30px; margin-top: 0; display: inline-block;">Register For Event</button>
            <button type="button" id="modalUnregBtn" onclick="unregisterEvent()" class="btn-register" style="width: auto; padding: 10px 30px; margin-top: 0; display: none; background: #ef4444; color: white; border: none;">Unregister</button>
            <button class="btn-detail-modal-close" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<script>
    let currentEventId = null;

    function showEventDetails(eventId) {
        currentEventId = eventId;
        const modal = document.getElementById('eventDetailModal');
        const isEventFolder = window.location.pathname.includes('/event/');
        const apiUrl = isEventFolder ? '../api/events/get-events.php' : 'api/events/get-events.php';
        
        // Reset states
        toggleRegForm(false);
        document.getElementById('regMessage').style.display = 'none';

        fetch(`${apiUrl}?id=${eventId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const event = data.event;
                    document.getElementById('modalTitle').innerText = event.title;
                    document.getElementById('modalDate').innerText = new Date(event.event_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    document.getElementById('modalVenue').innerText = event.venue;
                    document.getElementById('modalCategory').innerText = event.category_name || 'General';
                    document.getElementById('modalCap').innerText = (event.max_participants || 'N/A') + ' Max';
                    document.getElementById('modalDesc').innerText = event.description || 'No description available.';
                    document.getElementById('modalImg').src = 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&q=80&w=600';
                    document.getElementById('regEventId').value = event.event_id;
                    
                    const regBtn = document.getElementById('modalRegLink');
                    const unregBtn = document.getElementById('modalUnregBtn');
                    const eventDate = new Date(event.event_date);
                    const today = new Date();
                    today.setHours(0,0,0,0);
                    
                    if (eventDate < today) {
                        regBtn.style.display = 'none';
                        unregBtn.style.display = 'none';
                    } else if (event.is_registered) {
                        regBtn.style.display = 'inline-block';
                        regBtn.innerText = 'Registered';
                        regBtn.disabled = true;
                        regBtn.style.opacity = '0.7';
                        regBtn.style.background = '#6c757d';
                        unregBtn.style.display = 'inline-block';
                    } else {
                        regBtn.style.display = 'inline-block';
                        regBtn.innerText = 'Register For Event';
                        regBtn.disabled = false;
                        regBtn.style.opacity = '1';
                        regBtn.style.background = 'var(--pink-accent)';
                        unregBtn.style.display = 'none';
                    }
                    
                    modal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
            });
    }

    function toggleRegForm(show) {
        document.getElementById('mainModalContent').style.display = show ? 'none' : 'block';
        document.getElementById('registrationFormContainer').style.display = show ? 'block' : 'none';
        document.getElementById('modalFooterActions').style.display = show ? 'none' : 'flex';
    }

    function unregisterEvent() {
        if (!confirm('Are you sure you want to unregister from this event?')) return;
        
        const btn = document.getElementById('modalUnregBtn');
        btn.innerText = 'Removing...';
        btn.disabled = true;

        const isEventFolder = window.location.pathname.includes('/event/');
        const unregApi = isEventFolder ? 'unregister.php' : 'event/unregister.php';

        const formData = new FormData();
        formData.append('event_id', currentEventId);

        fetch(unregApi, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            const msgEle = document.getElementById('regMessage');
            msgEle.innerText = data.message;
            msgEle.className = `message ${data.success ? 'success' : 'error'}`;
            msgEle.style.display = 'block';

            if (data.success) {
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                btn.innerText = 'Unregister';
                btn.disabled = false;
            }
        });
    }

    document.getElementById('ajaxRegForm').onsubmit = function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitRegBtn');
        btn.innerText = 'Processing...';
        btn.disabled = true;

        const isEventFolder = window.location.pathname.includes('/event/');
        const regApi = isEventFolder ? 'register.php' : 'event/register.php';
        
        fetch(regApi, {
            method: 'POST',
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(data => {
            const msgEle = document.getElementById('regMessage');
            msgEle.innerText = data.message;
            msgEle.className = `message ${data.success ? 'success' : 'error'}`;
            msgEle.style.display = 'block';

            if (data.success) {
                setTimeout(() => {
                    location.reload(); // Reload to update all statuses
                }, 1500);
            } else {
                btn.innerText = 'Confirm Registration';
                btn.disabled = false;
            }
        });
    };

    function closeModal() {
        document.getElementById('eventDetailModal').classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    window.onclick = e => {
        if (e.target == document.getElementById('eventDetailModal')) closeModal();
    };
</script>
