<!-- Login Modal Component -->
<div id="loginModal" class="detail-modal login-modal-wrapper">
    <div class="detail-modal-content">
        <div class="detail-modal-header" style="background: var(--primary-gradient); color: white; padding: 25px; position: relative; border-radius: 20px 20px 0 0;">
            <h2 style="margin: 0; font-size: 24px;">Welcome Back</h2>
            <span class="close-detail-modal" onclick="closeLoginModal()" style="position: absolute; right: 25px; top: 25px; color: white; font-size: 28px; cursor: pointer;">&times;</span>
        </div>
        <div class="detail-modal-body" style="padding: 30px;">
            <div id="loginModalMessage" class="message" style="display: none; margin-bottom: 15px;"></div>
            
            <div class="login-form-container" style="box-shadow: none; padding: 0; background: transparent; width: 100%; max-width: 100%;">
                <p style="margin-bottom: 20px; font-size: 14px; color: #666; text-align: center;">Please login to your account to send a message.</p>
                <form id="modalLoginForm">
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" placeholder="Enter email address" required style="width: 100%; padding: 12px; border-radius: 10px; border: 1.5px solid #ddd;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" placeholder="Enter your password" required style="width: 100%; padding: 12px; border-radius: 10px; border: 1.5px solid #ddd;">
                        </div>
                    </div>
                    
                    <button type="submit" id="modalLoginBtn" class="btn-login" style="width: 100%; padding: 14px; background: var(--primary-gradient); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer;">Login Account</button>
                    <div class="register-link" style="margin-top: 20px; text-align: center; font-size: 14px;">
                        Don't have an account? <a href="<?= BASE_URL ?>/register/" style="color: var(--pink-accent); text-decoration: none; font-weight: 600;">Register Now</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .login-modal-wrapper.detail-modal {
        display: none;
        position: fixed;
        z-index: 9999; /* Ensure it stays on top */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        align-items: center;
        justify-content: center;
    }
    .login-modal-wrapper.detail-modal.show {
        display: flex;
    }
</style>

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
