<?php
include('../includes/db.php'); 
session_start();

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user with role = admin only
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php"); // Redirect to admin dashboard
        exit();
    } else {
        $error_message = "Invalid credentials or not an admin.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - JOB Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/deep-theme.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }
        
        .login-container {
            background: rgba(214, 140, 29, 0.35);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(12px);
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: slideFadeIn 0.8s ease-out;
            border: 1px solid rgba(100, 100, 160, 0.15);
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(100,100,200,0.05) 0%, transparent 60%);
            z-index: -1;
            animation: rotate 15s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes slideFadeIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            color: var(--text-color);
            font-size: 2.5em;
            margin-bottom: 25px;
            letter-spacing: 1px;
            font-family: var(--heading-font);
            font-weight: 700;
            position: relative;
            display: inline-block;
        }

        h2::after {
            content: '';
            position: absolute;
            width: 60%;
            height: 3px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.7), transparent);
            bottom: -10px;
            left: 20%;
            border-radius: 4px;
        }

        .icon-container {
            margin-bottom: 20px;
            font-size: 3em;
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.2em;
        }

        input[type="email"],
        input[type="password"] {
            width: calc(100% - 55px);
            padding: 15px 15px 15px 40px;
        }

        .register-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 25px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 30px;
            background: rgba(40, 40, 80, 0.3);
            backdrop-filter: blur(5px);
        }

        .register-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            background: rgba(60, 60, 100, 0.4);
        }

        .error-message {
            background: rgba(220, 20, 60, 0.2);
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 3px solid crimson;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="icon-container">
            <i class='bx bx-shield-quarter'></i>
        </div>
        <h2>ADMIN LOGIN</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <i class='bx bx-envelope'></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            
            <div class="input-group">
                <i class='bx bx-lock-alt'></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" name="login">
                <i class='bx bx-log-in'></i> Login
            </button>
        </form>
        
        <a href="register.php" class="register-button">
            <i class='bx bx-user-plus'></i> Register as Admin
        </a>
    </div>
</body>
</html>
