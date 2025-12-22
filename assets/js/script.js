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
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault(); // Prevent normal form submit

    // Clear previous errors
    document.getElementById('errorMessage').textContent = '';

    // Collect form data
    const formData = new FormData(this);

    try {
        const response = await fetch('../api/register/index.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message); // Optional: show success message
            window.location.href = '../login/'; // Redirect to login
        } else {
            document.getElementById('errorMessage').textContent = result.message;
        }
    } catch (error) {
        document.getElementById('errorMessage').textContent = 'Network error. Please try again.';
    }
});

// Password visibility toggle for register page
document.querySelectorAll('.togglePassword').forEach(el => {
    el.addEventListener('click', () => {
        const input = el.previousElementSibling;
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        el.textContent = type === 'password' ? 'Show' : 'Hide';
    });
});
