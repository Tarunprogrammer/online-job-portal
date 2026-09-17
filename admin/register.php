<?php
include('../includes/db.php');  // Database connection
session_start();

if (isset($_POST['register'])) {
    $username = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'admin'; // Default role for users

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $error_message = "Email is already registered!";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $role]);
            $user_id = $conn->lastInsertId();

            // Redirect to admin dashboard
            header("Location: ../admin/dashboard.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - JOB Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/deep-theme.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 30px 0;
        }
        
        .register-container {
            background: rgba(18, 18, 30, 0.35);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(12px);
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 500px;
            text-align: center;
            animation: slideFadeIn 0.8s ease-out;
            border: 1px solid rgba(100, 100, 160, 0.15);
            position: relative;
            overflow: hidden;
            margin: 20px auto;
        }

        .register-container::before {
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

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: calc(100% - 55px);
            padding: 15px 15px 15px 40px;
        }

        .login-link {
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

        .login-link:hover {
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
        
        .success-message {
            background: rgba(40, 167, 69, 0.2);
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 3px solid #28a745;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.querySelector('input[name="password"]');
            const confirmField = document.querySelector('input[name="confirm_password"]');
            const form = document.querySelector('form');
            
            // Check if passwords match when typing in confirm field
            confirmField.addEventListener('keyup', function() {
                if (passwordField.value !== confirmField.value) {
                    confirmField.style.borderColor = 'crimson';
                    confirmField.style.boxShadow = '0 0 8px rgba(220, 20, 60, 0.4)';
                } else {
                    confirmField.style.borderColor = '#28a745';
                    confirmField.style.boxShadow = '0 0 8px rgba(40, 167, 69, 0.4)';
                }
            });
            
            // Check if passwords match on form submit
            form.addEventListener('submit', function(e) {
                if (passwordField.value !== confirmField.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                }
            });
        });
    </script>
</head>
<body>
    <div class="register-container">
        <div class="icon-container">
            <i class='bx bx-shield-plus'></i>
        </div>
        <h2>Admin Registration</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <i class='bx bx-user'></i>
                <input type="text" name="name" placeholder="Full Name" required value="<?= $username ?? '' ?>">
            </div>
            
            <div class="input-group">
                <i class='bx bx-envelope'></i>
                <input type="email" name="email" placeholder="Email Address" required value="<?= $email ?? '' ?>">
            </div>
            
            <div class="input-group">
                <i class='bx bx-lock-alt'></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <div class="input-group">
                <i class='bx bx-check-shield'></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            
            <button type="submit" name="register">
                <i class='bx bx-user-plus'></i> Register
            </button>
        </form>
        
        <a href="login.php" class="login-link">
            <i class='bx bx-log-in'></i> Already have an account? Login
        </a>
    </div>
</body>
</html>