<?php
// Database connection
$host = 'localhost';
$dbname = 'user_registration';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Initialize error message
    $error_message = null;
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    }
    
    // Check if passwords match
    else if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    }
    
    // Check password length
    else if (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    }
    
    // Check if email already exists
    else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error_message = "Email already exists. Please use a different email address.";
        }
    }
    
    // If no errors, insert user into database
    if ($error_message === null) {
        try {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashed_password]);
            
            $success_message = "Registration successful! You can now log in.";
        } catch (PDOException $e) {
            $error_message = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Registration System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #d3eaad; /* Light green background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container-wrapper {
            background-color: #eaf5d6;
            border-radius: 24px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1000px;
            padding: 30px;
            display: flex;
            overflow: hidden;
        }
        
        .illustration {
            flex: 1;
            display: none;
        }
        
        .illustration-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 16px;
        }
        
        @media (min-width: 992px) {
            .illustration {
                display: block;
                background-color: #eaf5d6;
                border-radius: 16px;
                margin-right: 20px;
                position: relative;
            }
        }
        
        .container {
            background-color: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            margin-left: auto;
        }
        
        .brand {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
        }
        
        .brand-name {
            color: #333;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .brand-icon {
            width: 18px;
            height: 18px;
            background-color: #94d66a;
            margin-right: 8px;
            border-radius: 2px;
            transform: rotate(45deg);
        }
        
        .form-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            background-color: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-control:focus {
            border-color: #94d66a;
            outline: none;
            box-shadow: 0 0 0 3px rgba(148, 214, 106, 0.2);
        }
        
        .submit-btn {
            width: 100%;
            background-color: #94d66a;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            background-color: #86c35f;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #888;
            font-size: 16px;
            padding: 4px;
        }
        
        .terms {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: #666;
        }
        
        .terms a {
            color: #94d66a;
            text-decoration: none;
        }
        
        .strength-meter {
            height: 4px;
            width: 100%;
            background-color: #eee;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .strength-meter-fill {
            height: 100%;
            width: 0;
            border-radius: 4px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .strength-text {
            font-size: 12px;
            margin-top: 5px;
            color: #888;
        }
        
        .strength-weak .strength-meter-fill {
            background-color: #e74c3c;
            width: 33.33%;
        }
        
        .strength-medium .strength-meter-fill {
            background-color: #f39c12;
            width: 66.66%;
        }
        
        .strength-strong .strength-meter-fill {
            background-color: #2ecc71;
            width: 100%;
        }
        
        /* Modern toast notification styles */
        .toast-notification {
            position: fixed;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            padding: 16px 25px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            z-index: 1000;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            max-width: 90%;
            text-align: center;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
        }
        
        .toast-notification.error {
            background-color: #fff;
            color: #e74c3c;
            border-left-color: #e74c3c;
        }
        
        .toast-notification.success {
            background-color: #fff;
            color: #2ecc71;
            border-left-color: #2ecc71;
        }
        
        .toast-notification.show {
            opacity: 1;
            top: 30px;
        }
        
        .toast-icon {
            font-size: 1.2rem;
        }
        
        .toast-icon.error {
            color: #e74c3c;
        }
        
        .toast-icon.success {
            color: #2ecc71;
        }
        
        /* Animation for toast close */
        @keyframes toast-progress {
            0% { width: 100%; }
            100% { width: 0%; }
        }
        
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 0 0 12px 12px;
            overflow: hidden;
        }
        
        .toast-progress::after {
            content: '';
            position: absolute;
            height: 100%;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.2);
            animation: toast-progress 3s linear forwards;
        }
    </style>
</head>
<body>
    <!-- Toast notification container -->
    <div id="toast-notification" class="toast-notification">
        <div class="toast-progress"></div>
    </div>
    
    <div class="container-wrapper">
        <div class="illustration">
            <img src="image.png" alt="Illustration of a person working" class="illustration-image">
        </div>
        
        <div class="container">
            <div class="brand">
                <div class="brand-name">
                    <div class="brand-icon"></div>
                    mapanao
                </div>
            </div>
            
            <h1 class="form-title">Create account</h1>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <input type="email" id="email" name="email" class="form-control" placeholder="Email address" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <div class="password-container">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength-container" id="passwordStrength">
                        <div class="strength-meter">
                            <div class="strength-meter-fill"></div>
                        </div>
                        <div class="strength-text"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="password-container">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                        <button type="button" class="password-toggle" id="toggleConfirmPassword">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Create account</button>
            </form>
            
            <div class="terms">
                By creating an account you agree to Messimo's<br>
                <a href="#">Terms of Services</a> and <a href="#">Privacy Policy</a>.
            </div>
        </div>
    </div>

    <script>
        // Function to show toast notification
        function showToast(message, type) {
            const toastElement = document.getElementById('toast-notification');
            let icon = '';
            
            if (type === 'error') {
                icon = '<i class="toast-icon error fa-solid fa-circle-exclamation"></i>';
                toastElement.className = 'toast-notification error';
            } else {
                icon = '<i class="toast-icon success fa-solid fa-circle-check"></i>';
                toastElement.className = 'toast-notification success';
            }
            
            toastElement.innerHTML = icon + message + '<div class="toast-progress"></div>';
            
            // Force reflow to ensure transition works
            void toastElement.offsetWidth;
            
            // Show the toast
            toastElement.classList.add('show');
            
            // Hide after 3 seconds (matches the progress bar animation)
            setTimeout(() => {
                toastElement.classList.remove('show');
            }, 3000);
        }
        
        // Show error message if exists
        <?php if(isset($error_message)): ?>
            showToast("<?php echo addslashes($error_message); ?>", "error");
        <?php endif; ?>
        
        // Show success message if exists
        <?php if(isset($success_message)): ?>
            showToast("<?php echo addslashes($success_message); ?>", "success");
        <?php endif; ?>

        // Toggle password visibility with eye icon
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const toggleIcon = this.querySelector('i');
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
        
        // Password strength indicator with bar
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthContainer = document.getElementById('passwordStrength');
            const strengthMeterFill = strengthContainer.querySelector('.strength-meter-fill');
            const strengthText = strengthContainer.querySelector('.strength-text');
            
            // Remove previous strength classes
            strengthContainer.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
            
            if (password.length === 0) {
                strengthText.textContent = '';
                strengthMeterFill.style.width = '0';
                return;
            }
            
            // Check password strength
            let strength = 0;
            
            // Length check
            if (password.length >= 8) {
                strength += 1;
            }
            
            // Complexity checks
            if (/[A-Z]/.test(password)) {
                strength += 1;
            }
            
            if (/[0-9]/.test(password)) {
                strength += 1;
            }
            
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 1;
            }
            
            // Set strength meter
            if (strength <= 2) {
                strengthContainer.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
            } else if (strength === 3) {
                strengthContainer.classList.add('strength-medium');
                strengthText.textContent = 'Good password';
            } else {
                strengthContainer.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
            }
        });
    </script>
</body>
</html>