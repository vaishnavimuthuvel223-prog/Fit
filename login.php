<?php
require_once 'db.php';
redirectIfLogged();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $errors[] = 'Email and password are required.';
    } else {
        $stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $success = true;
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Invalid password.';
            }
        } else {
            $errors[] = 'Email not found.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="loader" id="pageLoader">
    <div class="spinner"></div>
</div>

<div class="login-container">
    <!-- Background Image -->
    <div class="bg-image" style="background-image: url('assets/fitness.jpg')"></div>
    <div class="bg-overlay"></div>
    
    <!-- Login Box - Left Side -->
    <div class="login-box slide-in-left">
        <!-- Logo -->
        <div class="logo-section bounce-in">
            <div class="logo-icon">💪</div>
            <h1>FitPulse</h1>
            <p>Pro Fitness Tracker</p>
        </div>

        <div class="form-section">
            <h2 class="form-title">Welcome Back</h2>
            <p class="form-subtitle">Sign in to your fitness journey</p>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error shake">
                    <?php foreach ($errors as $error): ?>
                        <div class="error-item">
                            <span class="error-icon">✗</span>
                            <span><?= safe($error) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form id="loginForm" method="POST" class="auth-form">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" required class="form-input">
                    <label class="form-label">Email</label>
                    <span class="form-focus-line"></span>
                </div>

                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required class="form-input">
                    <label class="form-label">Password</label>
                    <span class="form-focus-line"></span>
                </div>

                <a href="#" class="forgot-link">Forgot Password?</a>

                <button type="submit" class="btn btn-primary pulse-glow">
                    <span>Sign In</span>
                    <span class="btn-shine"></span>
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <span>or continue with</span>
            </div>

            <!-- Social Login -->
            <div class="social-login">
                <button class="btn btn-social google-btn" onclick="event.preventDefault(); alert('Google login coming soon')">
                    <span>🔵</span>
                </button>
                <button class="btn btn-social facebook-btn" onclick="event.preventDefault(); alert('Facebook login coming soon')">
                    <span>📘</span>
                </button>
                <button class="btn btn-social apple-btn" onclick="event.preventDefault(); alert('Apple login coming soon')">
                    <span>🍎</span>
                </button>
            </div>

            <!-- Signup Link -->
            <p class="auth-link">
                Don't have an account? <a href="signup.php" class="signup-link">Sign Up</a>
            </p>
        </div>
    </div>

    <!-- Right side floating elements -->
    <div class="floating-elements">
        <div class="float-card float-1">
            <span class="card-icon">🏃</span>
            <span class="card-text">Track Activity</span>
        </div>
        <div class="float-card float-2">
            <span class="card-icon">📊</span>
            <span class="card-text">See Progress</span>
        </div>
        <div class="float-card float-3">
            <span class="card-icon">🎯</span>
            <span class="card-text">Achieve Goals</span>
        </div>
    </div>
</div>

<script src="js/script.js"></script>
<script>
    const loginBox = document.querySelector('.login-box');
    const bgImage = document.querySelector('.bg-image');

    function hideLoader() {
        const loader = document.getElementById('pageLoader');
        if (!loader) return;
        loader.classList.add('hidden');
    }

    window.addEventListener('load', () => {
        console.info('Login page loaded.');
        hideLoader();
        if (bgImage) bgImage.classList.add('active-bounce');
    });

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(hideLoader, 800);

        if (loginBox) {
            loginBox.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                this.style.transform = `perspective(1200px) rotateY(${x * 10}deg) rotateX(${-y * 8}deg)`;
            });

            loginBox.addEventListener('mouseleave', function() {
                this.style.transform = 'perspective(1200px) rotateY(0deg) rotateX(0deg)';
            });
        }
    });

    // Parallax effect on floating badges
    document.addEventListener('mousemove', function(e) {
        const floatingElements = document.querySelectorAll('.float-card');
        const x = (e.clientX / window.innerWidth - 0.5) * 20;
        const y = (e.clientY / window.innerHeight - 0.5) * 20;

        floatingElements.forEach((el, index) => {
            const intensity = (index + 1) / 3;
            el.style.transform = `translate(${x * intensity}px, ${y * intensity}px) scale(1.01)`;
        });
    });

    // Add slow carousel glow
    setInterval(() => {
        document.querySelectorAll('.float-card').forEach(el => {
            el.style.boxShadow = `0 18px 40px rgba(255, 255, 255, ${0.12 + Math.random() * 0.15})`;
        });
    }, 900);
</script>
</body>
</html>
