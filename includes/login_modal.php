<!-- Login Modal Component -->
<div id="loginModal" class="detail-modal">
    <div class="detail-modal-content">
        <div class="detail-modal-header">
            <h2>Welcome Back</h2>
            <span class="close-detail-modal" onclick="closeLoginModal()">&times;</span>
        </div>
        <div class="detail-modal-body">
            <div id="loginModalMessage" class="message" style="display: none; margin-bottom: 15px;"></div>
            
            <div class="login-form-container" style="box-shadow: none; padding: 0; background: transparent;">
                <p style="margin-bottom: 20px; font-size: 14px; color: #666; text-align: center;">Please login to your account to send a message.</p>
                <form id="modalLoginForm">
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" placeholder="Enter email address" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <button type="submit" id="modalLoginBtn" class="btn-login">Login Account</button>
                    <div class="register-link" style="margin-top: 15px; text-align: center;">
                        Don't have an account? <a href="<?= BASE_URL ?>/register/" style="color: var(--pink-accent); text-decoration: none; font-weight: 600;">Register Now</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openLoginModal() {
        const modal = document.getElementById('loginModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeLoginModal() {
        document.getElementById('loginModal').classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    // Modal background click to close
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('loginModal');
        if (e.target === modal) {
            closeLoginModal();
        }
    });

    document.getElementById('modalLoginForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('modalLoginBtn');
        const msgBox = document.getElementById('loginModalMessage');
        
        btn.innerText = 'Logging in...';
        btn.disabled = true;
        
        const formData = new FormData(this);
        try {
            const response = await fetch('<?= BASE_URL ?>/api/login/index.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                msgBox.textContent = 'Login successful! Redirecting...';
                msgBox.className = 'message success';
                msgBox.style.display = 'block';
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                msgBox.textContent = result.message;
                msgBox.className = 'message error';
                msgBox.style.display = 'block';
                btn.innerText = 'Login Account';
                btn.disabled = false;
            }
        } catch (error) {
            msgBox.textContent = 'Something went wrong. Please try again.';
            msgBox.className = 'message error';
            msgBox.style.display = 'block';
            btn.innerText = 'Login Account';
            btn.disabled = false;
        }
    });
</script>
