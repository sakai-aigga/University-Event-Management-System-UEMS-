// Handle login form submission
document.getElementById('loginForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    const response = await fetch('../api/login/index.php', {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    if (result.success) {
        if (result.role === 'admin') {
            window.location.href = '/UEMS/University-Event-Management-System-UEMS-/admin-panel/';
        } else {
            window.location.href = '/UEMS/University-Event-Management-System-UEMS-/index.php';
        }
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

// Password visibility toggle for register/login page
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.togglePassword').forEach(el => {
        el.onclick = () => {
            const container = el.parentElement; // the input-wrapper
            const input = container.querySelector('input'); // get the input inside
            input.type = input.type === 'password' ? 'text' : 'password';
            el.textContent = input.type === 'password' ? 'Show' : 'Hide';
        };
    });
});
