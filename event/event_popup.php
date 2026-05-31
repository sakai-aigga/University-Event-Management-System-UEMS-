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
                <style>
                    .detail-modal-img { transition: all 0.3s ease; cursor: zoom-in !important; }
                    .detail-modal-img.expanded { 
                        height: auto !important; 
                        max-height: 70vh !important; 
                        object-fit: contain !important; 
                        cursor: zoom-out !important;
                        background: #000;
                    }
                    /* Auth Choice Styling */
                    .auth-choice-container { text-align: center; }
                    .auth-choice-title { margin-bottom: 12px; color: var(--primary-purple); font-weight: 600; }
                    .auth-choice-text { margin-bottom: 25px; color: #666; font-size: 14.5px; }
                    .auth-btns-stack { display: flex; flex-direction: column; gap: 14px; max-width: 380px; margin: 0 auto; padding: 0 5px; }
                    .btn-auth-choice {
                        text-decoration: none;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 12px;
                        height: 52px;
                        border-radius: 14px;
                        font-weight: 600;
                        font-size: 16px;
                        cursor: pointer;
                        border: none;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        width: 100%;
                    }
                    .btn-choice-signup { background: var(--pink-accent); color: white; }
                    .btn-choice-signup:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(255, 0, 122, 0.3); filter: brightness(1.05); }
                    .btn-choice-login { background: #4f46e5; color: white; }
                    .btn-choice-login:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3); filter: brightness(1.05); }
                    .btn-choice-cancel { background: transparent; border: 1.5px solid #e5e7eb; color: #6b7280; font-size: 14px; margin-top: 5px; height: 48px; }
                    .btn-choice-cancel:hover { background: #f9fafb; color: #374151; border-color: #d1d5db; }
                    
                    /* Registration Form Tweaks */
                    .reg-form-back-btn {
                        width: 100%;
                        margin-top: 18px;
                        border: 1.5px solid #e5e7eb;
                        height: 48px;
                        border-radius: 14px;
                        background: #fff;
                        color: #6b7280;
                        font-weight: 600;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.2s;
                    }
                    .reg-form-back-btn:hover { background: #f9fafb; border-color: #d1d5db; color: #374151; }
                    .actual-reg-title { margin-bottom: 20px; color: var(--primary-purple); font-weight: 600; }
                </style>
                <img src="" id="modalImg" class="detail-modal-img" alt="Event" onclick="this.classList.toggle('expanded')" title="Click to view full image">
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
                        <span id="modalCap">Available Seats</span>
                    </div>
                </div>
                <div class="detail-modal-description">
                    <p id="modalDesc">Description goes here...</p>
                </div>
            </div>

            <div id="registrationFormContainer" style="display: none; padding: 20px 0;">
                <?php if (!isset($_SESSION['u_id'])): ?>
                    <!-- Auth Choice Selection -->
                    <div id="authChoiceSection" class="auth-choice-container">
                        <h3 class="auth-choice-title">Registration Required</h3>
                        <p class="auth-choice-text">You must be logged in to register for events. Please choose an option below:</p>
                        <div class="auth-btns-stack">
                            <?php 
                                $isEventSub = (strpos($_SERVER['REQUEST_URI'], '/event/') !== false);
                                $regPath = $isEventSub ? '../register/' : 'register/';
                            ?>
                            <a href="<?php echo $regPath; ?>" class="btn-auth-choice btn-choice-signup">
                                <i class="fas fa-user-plus"></i> Sign Up / Create Account
                            </a>
                            <button type="button" class="btn-auth-choice btn-choice-login" onclick="showPopupLoginForm()">
                                <i class="fas fa-sign-in-alt"></i> Already Registered? Login
                            </button>
                            <button type="button" class="btn-auth-choice btn-choice-cancel" onclick="toggleRegForm(false)">
                                Not Now, Maybe Later
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="actualRegistrationForm" style="<?php echo !isset($_SESSION['u_id']) ? 'display: none;' : ''; ?>">
                    <h3 class="actual-reg-title">
                        <?php echo isset($_SESSION['u_id']) ? 'Complete Registration' : 'Login to Register'; ?>
                    </h3>
                    <form id="ajaxRegForm">
                        <input type="hidden" name="event_id" id="regEventId">
                        <?php if (!isset($_SESSION['u_id'])): ?>
                            <p style="margin-bottom: 20px; font-size: 14px; color: #666;">Enter your credentials to continue with registration:</p>
                            <div class="form-group">
                                <label>Email Address</label>
                                <div class="input-wrapper">
                                    <input type="email" name="email" placeholder="Enter email" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-wrapper pass-container">
                                    <input type="password" name="password" placeholder="Enter password" required>
                                    <span class="show-toggle togglePassword">Show</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <p style="margin-bottom: 20px;">Registering as <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></p>
                        <?php endif; ?>
                        <button type="submit" id="submitRegBtn" class="btn-pink" style="width: 100%; height: 50px; border-radius: 14px; font-weight: 600;">Confirm Registration</button>
                        <button type="button" onclick="<?php echo !isset($_SESSION['u_id']) ? 'showAuthChoice()' : 'toggleRegForm(false)'; ?>" class="reg-form-back-btn">
                            <i class="fas fa-arrow-left"></i> <?php echo !isset($_SESSION['u_id']) ? 'Back to Options' : 'Cancel'; ?>
                        </button>
                    </form>
                </div>
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

    function showEventDetails(eventId, startWithRegForm = false) {
        currentEventId = eventId;
        document.getElementById('regEventId').value = eventId;
        const modal = document.getElementById('eventDetailModal');
        const isEventFolder = window.location.pathname.includes('/event/');
        const apiUrl = isEventFolder ? '../api/events/get-events.php' : 'api/events/get-events.php';
        
        // Reset states
        toggleRegForm(startWithRegForm);
        document.getElementById('regMessage').style.display = 'none';

        fetch(`${apiUrl}?id=${eventId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const event = data.event;
                    document.getElementById('modalTitle').innerText = event.title;
                    document.getElementById('modalDate').innerText = new Date(event.event_date).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    document.getElementById('modalVenue').innerText = event.venue;
                    const deptInfo = event.dept_name ? `Hosted by: ${event.dept_name}` : 'General Event';
                    document.getElementById('modalCategory').innerText = `${event.category_name || 'General'} | ${deptInfo}`;
                    const availableSeats = (event.max_participants || 0) - (event.current_participants || 0);
                    document.getElementById('modalCap').innerText = (availableSeats > 0 ? availableSeats : 0) + ' Seats Available';
                    document.getElementById('modalDesc').innerText = event.description || 'No description available.';
                    
                    // Use the image provided by the API (either BLOB data or dynamic fallback)
                    let imgSrc = event.event_image || 'https://images.unsplash.com/photo-1541339907198-e08756ebafe3?q=80&w=1000&auto=format&fit=crop';
                    document.getElementById('modalImg').src = imgSrc;
                    
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
                    } else if (event.max_participants > 0 && event.current_participants >= event.max_participants) {
                        regBtn.style.display = 'inline-block';
                        regBtn.innerText = 'Fully Booked';
                        regBtn.disabled = true;
                        regBtn.style.opacity = '0.7';
                        regBtn.style.background = '#e5e7eb';
                        regBtn.style.color = '#6b7280';
                        regBtn.style.cursor = 'not-allowed';
                        unregBtn.style.display = 'none';
                    } else {
                        regBtn.style.display = 'inline-block';
                        regBtn.innerText = 'Register For Event';
                        regBtn.disabled = false;
                        regBtn.style.opacity = '1';
                        regBtn.style.background = 'var(--pink-accent)';
                        regBtn.style.color = 'white';
                        regBtn.style.cursor = 'pointer';
                        unregBtn.style.display = 'none';
                    }
                    
                    if (startWithRegForm && event.max_participants > 0 && event.current_participants >= event.max_participants) {
                        toggleRegForm(false);
                    }
                    
                    modal.classList.add('show');
                    // Prevent layout shifting by compensating for scrollbar width
                    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
                    document.body.style.paddingRight = `${scrollbarWidth}px`;
                    document.body.style.overflow = 'hidden';
                }
            });
    }

    function toggleRegForm(show) {
        document.getElementById('mainModalContent').style.display = show ? 'none' : 'block';
        document.getElementById('registrationFormContainer').style.display = show ? 'block' : 'none';
        document.getElementById('modalFooterActions').style.display = show ? 'none' : 'flex';
        
        // Reset to choice view if showing registration container and user is not logged in
        if (show) {
            const choice = document.getElementById('authChoiceSection');
            const form = document.getElementById('actualRegistrationForm');
            if (choice && form) {
                choice.style.display = 'block';
                form.style.display = 'none';
            }
        }
    }

    function showPopupLoginForm() {
        const choice = document.getElementById('authChoiceSection');
        const form = document.getElementById('actualRegistrationForm');
        if (choice && form) {
            choice.style.display = 'none';
            form.style.display = 'block';
        }
    }

    function showAuthChoice() {
        const choice = document.getElementById('authChoiceSection');
        const form = document.getElementById('actualRegistrationForm');
        if (choice && form) {
            choice.style.display = 'block';
            form.style.display = 'none';
        }
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
                }, 500);
            } else {
                btn.innerText = 'Unregister';
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            const msgEle = document.getElementById('regMessage');
            msgEle.innerText = 'Connection or server error. Please try again.';
            msgEle.className = 'message error';
            msgEle.style.display = 'block';
            btn.innerText = 'Unregister';
            btn.disabled = false;
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
                }, 500);
            } else {
                btn.innerText = 'Confirm Registration';
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            const msgEle = document.getElementById('regMessage');
            msgEle.innerText = 'Connection or server error. Please try again.';
            msgEle.className = 'message error';
            msgEle.style.display = 'block';
            btn.innerText = 'Confirm Registration';
            btn.disabled = false;
        });
    };

    function closeModal() {
        document.getElementById('eventDetailModal').classList.remove('show');
        document.body.style.overflow = 'auto';
        document.body.style.paddingRight = '0';
    }

    window.onclick = e => {
        if (e.target == document.getElementById('eventDetailModal')) closeModal();
    };

    // Password Toggle for the Registration (Login) form
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('togglePassword')) {
            const container = e.target.parentElement;
            const input = container.querySelector('input');
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
                e.target.textContent = input.type === 'password' ? 'Show' : 'Hide';
            }
        }
    });
</script>
