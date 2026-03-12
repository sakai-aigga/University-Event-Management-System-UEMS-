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
document.getElementById('registerForm')?.addEventListener('submit', async function(e) {
    e.preventDefault(); // Prevent normal form submit

    // Clear previous errors
    document.getElementById('errorMessage').textContent = '';

    // Collect form data
    const formData = new FormData(this);

    // Contact validation
    const contact = formData.get('contact');
    const contactRegex = /^[9][6-8][0-9]{8}$/; // Nepal style: 98xxxxxxxx or 97xxxxxxxx
    if (contact && !contactRegex.test(contact)) {
        document.getElementById('errorMessage').textContent = 'Please enter a valid 10-digit mobile number starting with 9.';
        return;
    }

    try {
        const response = await fetch('../api/register/index.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Registration Successful!',
                text: result.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '../login/';
            });
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
