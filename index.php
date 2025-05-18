<!-- File: index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduSync</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Desktop-First Styles */
        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
            --light-bg: #f8f9fa;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
            line-height: 1.6;
        }

        /* Navigation */
        .navbar {
            background: var(--primary-color);
            padding: 1rem 5%;
            position: fixed;
            width: 90%;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--accent-color);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-color), #3a566f);
            min-height: 80vh;
            color: white;
            display: flex;
            align-items: center;
            padding: 6rem 5% 4rem;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .hero-title {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            max-width: 800px;
        }

        .hero-tagline {
            font-size: 1.5rem;
            margin-bottom: 2.5rem;
            max-width: 600px;
            opacity: 0.9;
        }

        /* Features Grid */
        .features {
            padding: 4rem 5%;
            background: var(--light-bg);
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        /* Call to Action */
        .cta-section {
            text-align: center;
            padding: 4rem 5%;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 2rem;
            background: var(--accent-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: transform 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="#" class="brand">EduSync</a>
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="login.php">Login</a>
            <a href="register.php" class="cta-button">Get Started</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">EduSync</h1>
            <p class="hero-tagline">
                Advanced E-learning Platform for</br>
                Electronics & Communication Engineering Discipline</br>
                Khulna University
            </p>
            <div class="cta-section">
                <a href="register.php" class="cta-button">
                    <i class="fas fa-user-plus"></i>
                    Create Free Account
                </a>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-chalkboard-teacher feature-icon"></i>
                <h3>Expert Lectures</h3>
                <p>Access course materials and video lectures from KU ECE faculty members</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-file-upload feature-icon"></i>
                <h3>Digital Submissions</h3>
                <p>Submit assignments and projects through our secure portal</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-chart-line feature-icon"></i>
                <h3>Performance Tracking</h3>
                <p>Monitor your academic progress with real-time analytics</p>
            </div>
        </div>
    </section>
</body>
</html>