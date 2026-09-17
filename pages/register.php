<?php
include('../includes/db.php');  // Database connection
session_start();

if (isset($_POST['register'])) {
    $username = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'user';
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $error_message = "Email is already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $role]);
            $user_id = $conn->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            header("Location: ../pages/profile.php?id=" . $user_id);
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
    <title>JOB Portal - Register</title>
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
        input[type="password"],
        input[type="tel"],
        select {
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
</head>
<body>
    <div class="register-container">
        <div class="icon-container">
            <i class='bx bx-user-plus'></i>
        </div>
        <h2>Create Account</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?= $error_message ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?= $success_message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="input-group">
                <i class='bx bx-user'></i>
                <input type="text" name="full_name" placeholder="Full Name" required value="<?= $full_name ?? '' ?>">
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
                <i class='bx bx-lock-alt'></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            
            <button type="submit" name="register">
                <i class='bx bx-user-check'></i> Register
            </button>
        </form>
        
        <a href="login.php" class="login-link">
            <i class='bx bx-log-in'></i> Already have an account? Login
        </a>
    </div>
</body>
<script>
    // Real-time password validation
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.querySelector('input[name="password"]');
        const confirmInput = document.querySelector('input[name="confirm_password"]');
        const registerButton = document.querySelector('button[name="register"]');
        
        function validatePasswords() {
            if (confirmInput.value && passwordInput.value !== confirmInput.value) {
                confirmInput.style.borderColor = '#ff3860';
                confirmInput.style.boxShadow = '0 0 0 2px rgba(255, 56, 96, 0.25)';
                registerButton.disabled = true;
                
                // Create or update error message
                let errorSpan = document.getElementById('password-error');
                if (!errorSpan) {
                    errorSpan = document.createElement('span');
                    errorSpan.id = 'password-error';
                    errorSpan.style.color = '#ff3860';
                    errorSpan.style.fontSize = '0.8rem';
                    errorSpan.style.display = 'block';
                    errorSpan.style.marginTop = '-10px';
                    errorSpan.style.marginBottom = '10px';
                    confirmInput.parentNode.insertAdjacentElement('afterend', errorSpan);
                }
                errorSpan.textContent = 'Passwords do not match';
            } else {
                confirmInput.style.borderColor = '';
                confirmInput.style.boxShadow = '';
                registerButton.disabled = false;
                
                // Remove error message if it exists
                const errorSpan = document.getElementById('password-error');
                if (errorSpan) {
                    errorSpan.remove();
                }
            }
        }
        
        // Add event listeners
        confirmInput.addEventListener('input', validatePasswords);
        passwordInput.addEventListener('input', validatePasswords);
    });
</script>
</html>
