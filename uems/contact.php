<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Desk</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .contact-hero {
            background: var(--primary-gradient);
            color: white;
            padding: 80px 8%;
            text-align: center;
            border-radius: 0 0 50px 50px;
        }
        .contact-hero h1 {
            font-size: 42px;
            margin-bottom: 20px;
        }
        .contact-main-wrapper {
            max-width: 1100px;
            margin: -60px auto 60px;
            position: relative;
            z-index: 10;
            padding: 0 20px;
        }
        .contact-container {
            background: white;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-wrap: wrap;
            overflow: hidden;
        }
        .contact-sidebar {
            flex: 1 1 350px;
            background: var(--dark-purple);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .contact-sidebar h2 {
            font-size: 28px;
            margin-bottom: 30px;
            border-left: 5px solid var(--pink-accent);
            padding-left: 15px;
        }
        .contact-method {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }
        .contact-method i {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--pink-accent);
        }
        .contact-method div h4 {
            font-size: 14px;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .contact-method div p {
            font-size: 16px;
            font-weight: 500;
        }
        .contact-form-side {
            flex: 1 1 500px;
            padding: 50px;
            background: white;
        }
        .contact-form-side h3 {
            font-size: 24px;
            color: var(--text-dark);
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .contact-hero h1 { font-size: 32px; }
            .contact-sidebar, .contact-form-side { padding: 30px; }
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?> 

    <section class="contact-hero">
        <h1>Help Desk</h1>
        <p>Submit inquiries, report issues, or contact the administrative team directly.</p>
    </section>

    <main class="contact-main-wrapper">
        <div class="contact-container">
            <!-- Sidebar with info -->
            <aside class="contact-sidebar">
                <h2>Contact Information</h2>
                <div class="contact-method">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email Us</h4>
                        <p>info@uems.edu</p>
                    </div>
                </div>
                <div class="contact-method">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <h4>Call Us</h4>
                        <p>+977 - 9999999999</p>
                    </div>
                </div>
                <div class="contact-method">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Visit Us</h4>
                        <p>Pulwari, Hattiban, Lalitpur</p>
                    </div>
                </div>
            </aside>

            <!-- Form section -->
            <section class="contact-form-side">
                <h3>Send us a Message</h3>
                <form id="contactForm">
                    <div class="form-group">
                        <label>Full Name</label>
                        <div class="input-wrapper">
                            <input type="text" name="name" placeholder="Enter your name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <div class="input-wrapper">
                            <textarea name="message" placeholder="How can we help you?" rows="4" required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">Send Message</button>
                </form>
                <div id="contactMessage" style="margin-top: 20px; text-align: center; font-weight: 600;"></div>
            </section>
        </div>
    </main>

    <?php include "../includes/login_modal.php"; ?>

    <script>
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

        // Auto-submit if there's a pending message after login
        document.addEventListener('DOMContentLoaded', () => {
            const pending = sessionStorage.getItem('pendingContactMessage');
            if (pending && isLoggedIn) {
                const data = JSON.parse(pending);
                const form = document.getElementById('contactForm');
                if (form) {
                    form.elements['name'].value = data.name;
                    form.elements['message'].value = data.message;
                    
                    // Attempt to send automatically
                    sendContactMessage(data);
                }
            }
        });

        document.getElementById('contactForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                message: formData.get('message')
            };

            if (!isLoggedIn) {
                // Save data and prompt login
                sessionStorage.setItem('pendingContactMessage', JSON.stringify(data));
                if (typeof openLoginModal === 'function') {
                    openLoginModal();
                    const modalMsg = document.getElementById('loginModalMessage');
                    if (modalMsg) {
                        modalMsg.textContent = 'Please login to send your message. Your input will be saved.';
                        modalMsg.className = 'message info';
                        modalMsg.style.display = 'block';
                        modalMsg.style.color = 'var(--dark-purple)';
                    }
                } else {
                    alert('Please login to send a message.');
                    window.location.href = '../login/';
                }
                return;
            }

            sendContactMessage(data);
        });

        async function sendContactMessage(data) {
            const msgBox = document.getElementById('contactMessage');
            const submitBtn = document.querySelector('#contactForm button[type="submit"]');
            
            msgBox.style.color = "#3b82f6";
            msgBox.textContent = 'Sending message...';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Sending...';
            }

            const formData = new FormData();
            for (const key in data) {
                formData.append(key, data[key]);
            }

            try {
                const response = await fetch('../api/contact.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    msgBox.style.color = "#10b981";
                    msgBox.textContent = result.message || 'Thank you! Your message has been sent successfully.';
                    document.getElementById('contactForm')?.reset();
                    sessionStorage.removeItem('pendingContactMessage');
                } else {
                    msgBox.style.color = "#ef4444";
                    msgBox.textContent = result.message || 'Error sending message.';
                }
            } catch (error) {
                msgBox.style.color = "#ef4444";
                msgBox.textContent = 'Network error. Please try again later.';
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Send Message';
                }
            }
        }
    </script>
    
    <script src="../assets/js/script.js"></script>
    <?php include "../includes/footer.php"; ?> 
</body>
</html>
