<?php
// Simulate fetching user data
$userName = "User Name";
$university = "University Name";
$profilePic = "../assets/images/profile_p.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUEMS Profile</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo-area">
        <a href="../index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
            <img src="../assets/images/UEMS_logo.png" class="header-logo" alt="KUEMS">
            <span class="logo-text">KUEMS</span>
        </a>
    </div>
        <nav>
            <a href="#">Events</a>
            <a href="#">About</a>
            <a href="#">Blog</a>
            <a href="#">Registration</a>
            <a href="#">Contact</a>
            <img src="<?php echo $profilePic; ?>" alt="Profile" class="header-logo" style="width: 50px; border-radius: 60%; object-fit: cover;">
        </nav>
    </header>

    <!-- Hero / Profile Section -->
    <section class="hero-section">
        <div class="register-card">
            <!-- Left Info -->
            <div class="register-info">
                <img src="<?php echo $profilePic; ?>" alt="Profile Picture" style="display: block; margin: 0 auto; width: 150px; border-radius: 50%; object-fit: cover;">
                <h2>Hello, <br> <?php echo $userName; ?></h2>
                <p>Welcome to your profile. Here you can update your information and manage your account settings.</p>
                <button class="home-btn-login">LOG OUT</button>
            </div>

            <!-- Form / Details -->
            <div class="form-outer-box">
                <div class="form-group">
                    <label>Profile Name:</label>
                    <div class="input-wrapper">
                        <input type="text" value="<?php echo $userName; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>University:</label>
                    <div class="input-wrapper">
                        <input type="text" value="<?php echo $university; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <button class="btn-register">Update Profile</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-logo">
                <h2>KUEMS</h2>
                <p>KUEMS is Kathmandu University's vibrant event hub that lets students, faculty, and staff discover, track, and immerse in campus moments.</p>
            </div>
            <div>
                <h3>Location</h3>
                <p>School of Education<br>Hattiban, Lalitpur, Nepal</p>
            </div>
            <div>
                <h3>Contact</h3>
                <p>+977-1-5912524<br>admin@kuosed.edu.np</p>
            </div>
        </div>
    </footer>
</body>
</html>
