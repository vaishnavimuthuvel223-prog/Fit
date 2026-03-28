<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Redirect to login
header('Location: login.php');
exit;

if (false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'signup') {
        $name = trim($_POST['name']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = trim($_POST['password']);

        if (!$name || !$email || !$password) {
            $errors[] = 'Please fill all fields for sign up.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
                $stmt->execute([$name, $email, $hash]);
                $user_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare('INSERT INTO goals (user_id) VALUES (?)');
                $stmt->execute([$user_id]);
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                header('Location: dashboard.php');
                exit;
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = trim($_POST['password']);

        if (!$email || !$password) {
            $errors[] = 'Please fill all fields for login.';
        } else {
            $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $errors[] = 'Invalid credentials.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: dashboard.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fitness Tracker - Login / Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="auth-page">
<div class="loader" id="global-loader"><div class="spinner"></div></div>
<section class="auth-container glass">
    <h1>Fitness Tracker</h1>
    <p>Login or Signup to start your journey</p>
    <?php if (!empty($errors)): ?>
        <div class="alert" id="alert-msg"><?= htmlspecialchars(implode('<br>', $errors)) ?></div>
    <?php endif; ?>
    <div class="auth-grid">
        <form method="post" class="card fade-in">
            <h2>Login</h2>
            <input type="hidden" name="action" value="login">
            <label>Email<input type="email" name="email" required></label>
            <label>Password<input type="password" name="password" minlength="6" required></label>
            <button type="submit" class="btn">Sign In</button>
        </form>
        <form method="post" class="card fade-in" style="animation-delay: 0.15s;">
            <h2>Sign Up</h2>
            <input type="hidden" name="action" value="signup">
            <label>Name<input type="text" name="name" required></label>
            <label>Email<input type="email" name="email" required></label>
            <label>Password<input type="password" name="password" minlength="6" required></label>
            <button type="submit" class="btn btn-accent">Create Account</button>
        </form>
    </div>
</section>
<script src="script.js"></script>
</body>
</html>
