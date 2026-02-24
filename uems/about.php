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
    <title>About UEMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .about-hero {
            background: var(--primary-gradient);
            color: white;
            padding: 80px 8%;
            text-align: center;
            border-radius: 0 0 50px 50px;
        }
        .about-hero h1 {
            font-size: 42px;
            margin-bottom: 20px;
        }
        .about-content {
            max-width: 1000px;
            margin: -40px auto 60px;
            background: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
        }
        .about-section {
            margin-bottom: 40px;
        }
        .about-section h2 {
            color: var(--primary-purple);
            margin-bottom: 15px;
            font-size: 28px;
            border-left: 5px solid var(--pink-accent);
            padding-left: 15px;
        }
        .about-section p {
            line-height: 1.8;
            color: #555;
            font-size: 16px;
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .team-member {
            text-align: center;
            padding: 25px 20px;
            background: var(--bg-light);
            border-radius: 20px;
            transition: 0.3s;
            border: 1px solid transparent;
        }
        .team-member:hover {
            transform: translateY(-10px);
            background: white;
            box-shadow: var(--card-shadow);
            border-color: #eee;
        }
        .member-img {
            width: 100px;
            height: 100px;
            background: var(--primary-gradient);
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .team-member h4 {
            font-size: 18px;
            color: var(--text-dark);
            margin-bottom: 5px;
            font-weight: 600;
        }
        .team-member p {
            font-size: 14px;
            color: var(--pink-accent);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        @media (max-width: 768px) {
            .about-hero h1 { font-size: 32px; }
            .about-content { padding: 30px; margin-top: -20px; border-radius: 20px; }
            .about-section h2 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?> 

    <section class="about-hero">
        <h1>Discover UEMS</h1>
        <p>Your ultimate gateway to campus events and student collaboration.</p>
    </section>

    <main class="about-content">
        <article class="about-section">
            <h2>What is UEMS?</h2>
            <p>
                The <strong>University Event Management System (UEMS)</strong> is a state-of-the-art digital ecosystem built to centralize and simplify how university events are managed and experienced. Our platform provides a seamless bridge between organizers and attendees, ensuring that no opportunity for growth, learning, or fun is ever missed.
            </p>
        </article>

        <article class="about-section">
            <h2>Who is it for?</h2>
            <p>
                UEMS is designed with inclusivity at its core, catering to:
            </p>
            <ul style="list-style: none; margin-top: 15px;">
                <li style="margin-bottom: 15px; display: flex; gap: 15px; align-items: flex-start;">
                    <i class="fas fa-graduation-cap" style="color: var(--pink-accent); margin-top: 5px;"></i>
                    <span><strong>Students:</strong> To discover workshops, seminars, and festivals that align with their personal and professional journey.</span>
                </li>
                <li style="margin-bottom: 15px; display: flex; gap: 15px; align-items: flex-start;">
                    <i class="fas fa-calendar-alt" style="color: var(--pink-accent); margin-top: 5px;"></i>
                    <span><strong>Organizers:</strong> To master the art of event planning with robust tools for registration tracking and promotion.</span>
                </li>
                <li style="margin-bottom: 15px; display: flex; gap: 15px; align-items: flex-start;">
                    <i class="fas fa-user-shield" style="color: var(--pink-accent); margin-top: 5px;"></i>
                    <span><strong>Administrators:</strong> To maintain harmony and quality across all campus-wide initiatives through a centralized oversight panel.</span>
                </li>
            </ul>
        </article>

        <article class="about-section">
            <h2>Meet the Developers</h2>
            <p>UEMS is the result of passion and collaborative engineering by a team of visionary student developers.</p>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-img"><i class="fas fa-user-astronaut"></i></div>
                    <h4>Govinda Bashak</h4>
                    <p>Core Developer</p>
                </div>
                <div class="team-member">
                    <div class="member-img"><i class="fas fa-code"></i></div>
                    <h4>Sajin Ghimire</h4>
                    <p>Core Developer</p>
                </div>
                <div class="team-member">
                    <div class="member-img"><i class="fas fa-terminal"></i></div>
                    <h4>Salan Maharjan</h4>
                    <p>Core Developer</p>
                </div>
                <div class="team-member">
                    <div class="member-img"><i class="fas fa-rocket"></i></div>
                    <h4>Shristi Shrestha</h4>
                    <p>Core Developer</p>
                </div>
            </div>
        </article>
    </main>

    <?php include "../includes/footer.php"; ?> 
</body>
</html>