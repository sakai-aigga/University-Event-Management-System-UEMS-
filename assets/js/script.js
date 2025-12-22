// Handle login form submission
document.getElementById('loginForm')?.addEventListener('submit', async function(e) {
    e.preventDefault(); // Prevent normal form submit

    const formData = new FormData(this);
    const response = await fetch('../api/login/index.php', {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    if (result.success) {
        window.location.href = '../profile/'; // Redirect on success
    } else {
        document.getElementById('errorMessage').textContent = result.message;
    }
});

// Handle register form submission
document.getElementById('registerForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const response = await fetch('../api/register/index.php', {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    if (result.success) {
        alert(result.message); // e.g., "Registration successful"
        window.location.href = '../login/'; // Go to login after register
    } else {
        document.getElementById('errorMessage').textContent = result.message;
    }
});

// Password visibility toggle for register page
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = this.previousElementSibling;
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.textContent = type === 'password' ? 'Show' : 'Hide';
});