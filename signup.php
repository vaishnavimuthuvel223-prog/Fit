<?php
require_once 'db.php';
redirectIfLogged();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    // Validation
    if (!$name || !$email || !$password || !$confirm) {
        $errors[] = 'All fields are required.';
    } else if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } else if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = 'Email already registered.';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;

                // Create default goals
                $stmt = $conn->prepare('INSERT INTO goals (user_id) VALUES (?)');
                $stmt->bind_param('i', $user_id);
                $stmt->execute();

                session_start();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $success = true;
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
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
    <title>Sign Up - FitPulse</title>
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
    
    <!-- Signup Box - Left Side -->
    <div class="login-box slide-in-left">
        <!-- Logo -->
        <div class="logo-section bounce-in">
            <div class="logo-icon">💪</div>
            <h1>FitPulse</h1>
            <p>Pro Fitness Tracker</p>
        </div>

        <div class="form-section">
            <h2 class="form-title">Join FitPulse</h2>
            <p class="form-subtitle">Start your fitness journey today</p>

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

            <!-- Signup Form -->
            <form id="signupForm" method="POST" class="auth-form">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Full Name" required class="form-input">
                    <label class="form-label">Full Name</label>
                    <span class="form-focus-line"></span>
                </div>

                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" required class="form-input">
                    <label class="form-label">Email</label>
                    <span class="form-focus-line"></span>
                </div>

                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required class="form-input" minlength="6">
                    <label class="form-label">Password (min. 6 chars)</label>
                    <span class="form-focus-line"></span>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm" placeholder="Confirm Password" required class="form-input">
                    <label class="form-label">Confirm Password</label>
                    <span class="form-focus-line"></span>
                </div>

                <button type="submit" class="btn btn-primary pulse-glow">
                    <span>Create Account</span>
                    <span class="btn-shine"></span>
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <span>or continue with</span>
            </div>

            <!-- Social Login -->
            <div class="social-login">
                <button class="btn btn-social google-btn" onclick="event.preventDefault(); alert('Google signup coming soon')">
                    <span>🔵</span>
                </button>
                <button class="btn btn-social facebook-btn" onclick="event.preventDefault(); alert('Facebook signup coming soon')">
                    <span>📘</span>
                </button>
                <button class="btn btn-social apple-btn" onclick="event.preventDefault(); alert('Apple signup coming soon')">
                    <span>🍎</span>
                </button>
            </div>

            <!-- Login Link -->
            <p class="auth-link">
                Already have an account? <a href="login.php" class="signup-link">Sign In</a>
            </p>
        </div>
    </div>

    <!-- Right side floating elements -->
    <div class="floating-elements">
        <div class="float-card float-1">
            <span class="card-icon">📱</span>
            <span class="card-text">Mobile App</span>
        </div>
        <div class="float-card float-2">
            <span class="card-icon">⚡</span>
            <span class="card-text">Get Started</span>
        </div>
        <div class="float-card float-3">
            <span class="card-icon">🏆</span>
            <span class="card-text">Win Rewards</span>
        </div>
    </div>
</div>

<script src="js/script.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('pageLoader');
        setTimeout(() => {
            loader.classList.add('hidden');
        }, 600);
    });
</script>
</body>
</html>
