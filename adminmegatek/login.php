<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel PT Megatek Industrial Persada</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #004080;
            --primary-light: #0066cc;
            --primary-dark: #002d5c;
            --secondary: #333333;
            --accent: #e6b800;
            --light: #f5f5f5;
            --danger: #d32f2f;
            --success: #2e7d32;
            --warning: #f57c00;
            --info: #0288d1;
            --gray: #757575;
            --light-gray: #e0e0e0;
            --border-radius: 8px;
            --box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f9f9f9;
            color: var(--secondary);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, 
                rgba(0, 64, 128, 0.05) 0%, 
                rgba(0, 102, 204, 0.05) 100%),
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="%23004080" opacity="0.03" width="100" height="100"/><path fill="%230066cc" opacity="0.05" d="M50,0 L100,50 L50,100 L0,50 Z"/></svg>');
            background-size: 200px;
            padding: 20px;
        }

        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            min-height: 700px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            animation: fadeIn 0.8s ease-out;
        }

        /* Left Panel - Branding & Info */
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><circle fill="white" opacity="0.05" cx="50" cy="50" r="40"/></svg>');
            background-size: 300px;
            animation: float 20s infinite linear;
        }

        .company-logo {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 32px;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .logo-text h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .logo-text h2 {
            font-size: 18px;
            font-weight: 400;
            opacity: 0.9;
        }

        .welcome-text {
            margin-bottom: 50px;
            position: relative;
            z-index: 1;
        }

        .welcome-text h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .welcome-text p {
            font-size: 16px;
            line-height: 1.8;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .features-list {
            list-style: none;
            margin-top: 30px;
            position: relative;
            z-index: 1;
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .features-list i {
            color: var(--accent);
            font-size: 18px;
        }

        .login-footer {
            margin-top: auto;
            position: relative;
            z-index: 1;
            font-size: 14px;
            opacity: 0.8;
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Right Panel - Login Form */
        .login-right {
            flex: 1;
            background: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .login-header h2 {
            font-size: 32px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-header p {
            color: var(--gray);
            font-size: 16px;
        }

        .login-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 30px;
            box-shadow: 0 10px 30px rgba(0, 64, 128, 0.2);
        }

        /* Form Styles */
        .login-form {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px;
            padding-left: 50px;
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            background-color: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 64, 128, 0.1);
        }

        .form-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 18px;
            transition: var(--transition);
        }

        .form-control:focus + .form-icon {
            color: var(--primary);
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 18px;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        /* Remember Me & Forgot Password */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
        }

        .forgot-password:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        /* Button Styles */
        .btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(0, 64, 128, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 64, 128, 0.3);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            margin-top: 15px;
        }

        .btn-secondary:hover {
            background-color: rgba(0, 64, 128, 0.05);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: var(--gray);
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: var(--light-gray);
        }

        .divider span {
            padding: 0 15px;
            font-size: 14px;
        }

        /* Alternative Login */
        .alternative-login {
            text-align: center;
            margin-top: 30px;
        }

        .social-login {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }

        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            color: var(--secondary);
            font-size: 18px;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .social-btn:hover {
            transform: translateY(-3px);
            background-color: var(--primary);
            color: white;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                max-width: 600px;
                min-height: auto;
            }
            
            .login-left, .login-right {
                padding: 40px 30px;
            }
            
            .login-left {
                order: 2;
            }
            
            .login-right {
                order: 1;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .login-left, .login-right {
                padding: 30px 20px;
            }
            
            .company-logo {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .logo-text h1 {
                font-size: 28px;
            }
            
            .welcome-text h3 {
                font-size: 24px;
            }
            
            .login-header h2 {
                font-size: 28px;
            }
            
            .form-options {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0% { background-position: 0 0; }
            100% { background-position: 300px 300px; }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .shake {
            animation: shake 0.5s ease-in-out;
        }

        /* Loading Spinner */
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Alert Message */
        .alert {
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: fadeIn 0.3s ease-out;
            display: none;
        }

        .alert-danger {
            background-color: rgba(211, 47, 47, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        .alert-success {
            background-color: rgba(46, 125, 50, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }

        .alert-info {
            background-color: rgba(2, 136, 209, 0.1);
            border-left: 4px solid var(--info);
            color: var(--info);
        }

        .alert i {
            font-size: 20px;
        }

        /* Session Info */
        .session-info {
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-top: 30px;
            font-size: 13px;
            color: var(--gray);
            border-left: 3px solid var(--primary);
        }

        .session-info i {
            color: var(--primary);
            margin-right: 8px;
        }

        /* Security Tips */
        .security-tips {
            margin-top: 40px;
            padding: 20px;
            background-color: #f0f7ff;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--info);
        }

        .security-tips h4 {
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .security-tips ul {
            list-style: none;
            padding-left: 0;
        }

        .security-tips li {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
        }

        .security-tips i {
            color: var(--info);
            font-size: 12px;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Panel - Branding & Info -->
        <div class="login-left">
            <div class="company-logo">
                <div class="logo-icon">
                    <i class="fas fa-industry"></i>
                </div>
                <div class="logo-text">
                    <h1>Megatek</h1>
                    <h2>Industrial Persada</h2>
                </div>
            </div>

            <div class="welcome-text">
                <h3>Selamat Datang di Admin Panel</h3>
            </div>

            <div class="login-footer">
                <p>&copy; 2025 PT Megatek Industrial Persada. Semua hak dilindungi.</p>
                <p>Your Trusted Industrial Partner</p>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Masuk ke Akun</h2>
                <p>Silakan masuk dengan kredensial Anda</p>
            </div>

            <!-- Alert Messages -->
            <div class="alert alert-danger" id="errorAlert">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorMessage">Username atau password salah</span>
            </div>

            <div class="alert alert-success" id="successAlert">
                <i class="fas fa-check-circle"></i>
                <span id="successMessage">Login berhasil! Mengalihkan...</span>
            </div>

            <!-- Login Form -->
            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="username">Username atau Email</label>
                    <div class="form-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" id="username" class="form-control" placeholder="Masukkan username atau email" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="form-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <input type="password" id="password" class="form-control" placeholder="Masukkan password" required>
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" class="remember-checkbox" id="rememberMe">
                        <span>Ingat saya</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <span id="btnText">Masuk ke Sistem</span>
                    <div class="spinner" id="loginSpinner" style="display: none;"></div>
                </button>

            </form>

            <!-- Security Tips -->
            <div class="security-tips">
                <h4><i class="fas fa-shield-alt"></i> Tips Keamanan:</h4>
                <ul>
                    <li><i class="fas fa-circle"></i> Jangan bagikan kredensial login Anda</li>
                    <li><i class="fas fa-circle"></i> Pastikan Anda logout setelah menggunakan sistem</li>
                    <li><i class="fas fa-circle"></i> Gunakan password yang kuat dan unik</li>
                    <li><i class="fas fa-circle"></i> Periksa URL sebelum login (pastikan HTTPS)</li>
                </ul>
            </div>

        </div>
    </div>

    <script>
        // DOM Elements
        const loginForm = document.getElementById('loginForm');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const rememberMe = document.getElementById('rememberMe');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const loginSpinner = document.getElementById('loginSpinner');
        const errorAlert = document.getElementById('errorAlert');
        const successAlert = document.getElementById('successAlert');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const demoLoginBtn = document.getElementById('demoLoginBtn');

        // Demo credentials (in real app, these would be validated server-side)
        const demoCredentials = {
            username: 'admin_megatek',
            password: 'megatek2025'
        };

        // Valid credentials (in real app, this would be server-side validation)
        const validCredentials = [
            { username: 'admin', password: 'admin123' },
            { username: 'megatek_admin', password: 'Industrial2025' },
            { username: 'admin@megatek.com', password: 'Admin@Megatek2025' },
            demoCredentials
        ];

        // Toggle password visibility
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.title = 'Sembunyikan password';
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.title = 'Tampilkan password';
            }
        });

        // Auto-focus username field on page load
        window.addEventListener('load', function() {
            usernameInput.focus();
            
            // Check if there are saved credentials
            const savedUsername = localStorage.getItem('megatek_username');
            const savedRemember = localStorage.getItem('megatek_remember');
            
            if (savedUsername && savedRemember === 'true') {
                usernameInput.value = savedUsername;
                rememberMe.checked = true;
                passwordInput.focus();
            }
        });

        // Handle demo login
        demoLoginBtn.addEventListener('click', function() {
            usernameInput.value = demoCredentials.username;
            passwordInput.value = demoCredentials.password;
            rememberMe.checked = true;
            
            // Show info message
            showAlert('info', 'Menggunakan akun demo. Password akan terisi otomatis.');
            
            // Auto-submit after 1 second
            setTimeout(() => {
                loginForm.dispatchEvent(new Event('submit'));
            }, 1000);
        });

        // Show alert message
        function showAlert(type, message) {
            // Hide all alerts first
            errorAlert.style.display = 'none';
            successAlert.style.display = 'none';
            
            // Show specific alert
            if (type === 'error') {
                errorMessage.textContent = message;
                errorAlert.style.display = 'flex';
                // Add shake animation to form
                loginForm.classList.add('shake');
                setTimeout(() => {
                    loginForm.classList.remove('shake');
                }, 500);
            } else if (type === 'success') {
                successMessage.textContent = message;
                successAlert.style.display = 'flex';
            } else if (type === 'info') {
                // Create temporary info alert
                const infoAlert = document.createElement('div');
                infoAlert.className = 'alert alert-info';
                infoAlert.innerHTML = `
                    <i class="fas fa-info-circle"></i>
                    <span>${message}</span>
                `;
                infoAlert.style.marginBottom = '25px';
                
                // Insert before form
                loginForm.parentNode.insertBefore(infoAlert, loginForm);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    infoAlert.remove();
                }, 3000);
            }
        }

        // Validate login credentials
        function validateLogin(username, password) {
            // Simple validation (in real app, this would be server-side)
            if (!username.trim() || !password.trim()) {
                return { success: false, message: 'Username dan password harus diisi' };
            }
            
            // Check against valid credentials
            const isValid = validCredentials.some(cred => 
                (cred.username === username || cred.email === username) && cred.password === password
            );
            
            if (!isValid) {
                return { 
                    success: false, 
                    message: 'Username atau password salah. Silakan coba lagi.' 
                };
            }
            
            return { success: true, message: 'Login berhasil!' };
        }

        // Handle form submission
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            const remember = rememberMe.checked;
            
            // Show loading state
            btnText.textContent = 'Memproses...';
            loginSpinner.style.display = 'inline-block';
            loginBtn.disabled = true;
            
            // Simulate API call delay
            setTimeout(() => {
                // Validate credentials
                const validation = validateLogin(username, password);
                
                if (validation.success) {
                    // Save to localStorage if "Remember me" is checked
                    if (remember) {
                        localStorage.setItem('megatek_username', username);
                        localStorage.setItem('megatek_remember', 'true');
                    } else {
                        localStorage.removeItem('megatek_username');
                        localStorage.removeItem('megatek_remember');
                    }
                    
                    // Save session data (in real app, this would be server-side session)
                    sessionStorage.setItem('megatek_logged_in', 'true');
                    sessionStorage.setItem('megatek_user', username);
                    sessionStorage.setItem('megatek_login_time', new Date().toISOString());
                    
                    // Show success message
                    showAlert('success', 'Login berhasil! Mengalihkan ke dashboard...');
                    
                    // Redirect to dashboard after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    // Show error message
                    showAlert('error', validation.message);
                    
                    // Reset form state
                    btnText.textContent = 'Masuk ke Sistem';
                    loginSpinner.style.display = 'none';
                    loginBtn.disabled = false;
                    
                    // Clear password field on error
                    passwordInput.value = '';
                    passwordInput.focus();
                }
            }, 1500); // Simulate network delay
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+Enter to submit form
            if (e.ctrlKey && e.key === 'Enter') {
                loginForm.dispatchEvent(new Event('submit'));
            }
            
            // Escape to clear form
            if (e.key === 'Escape') {
                usernameInput.value = '';
                passwordInput.value = '';
                usernameInput.focus();
            }
            
            // Tab navigation enhancement
            if (e.key === 'Tab') {
                // Add visual feedback for focused elements
                setTimeout(() => {
                    const focused = document.activeElement;
                    if (focused && focused.classList.contains('form-control')) {
                        focused.parentElement.classList.add('focused');
                    }
                }, 10);
            }
        });

        // Add focus/blur effects to form inputs
        const formInputs = document.querySelectorAll('.form-control');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });

        // Auto-detect browser autofill
        setInterval(() => {
            if (usernameInput.value && !usernameInput.classList.contains('autofilled')) {
                usernameInput.classList.add('autofilled');
            }
            if (passwordInput.value && !passwordInput.classList.contains('autofilled')) {
                passwordInput.classList.add('autofilled');
            }
        }, 100);

        // Add hover effect to login button
        loginBtn.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(-3px)';
            }
        });
        
        loginBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });

        // Social button hover effects
        const socialButtons = document.querySelectorAll('.social-btn');
        socialButtons.forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.1)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
            
            btn.addEventListener('click', function() {
                const title = this.getAttribute('title');
                showAlert('info', `${title} - Fitur ini akan segera tersedia`);
            });
        });

        // Forgot password link
        document.querySelector('.forgot-password').addEventListener('click', function(e) {
            e.preventDefault();
            showAlert('info', 'Fitur reset password akan membuka formulir pemulihan akun');
            
            // Simulate opening forgot password modal
            setTimeout(() => {
                alert('Untuk reset password, silakan hubungi administrator sistem di:\n\nEmail: admin@megatek-industri.com\nTelepon: (021) 1234-5678');
            }, 500);
        });

        // Prevent form submission on Enter key in non-submit contexts
        usernameInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.ctrlKey) {
                e.preventDefault();
                passwordInput.focus();
            }
        });
        
        passwordInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.ctrlKey) {
                e.preventDefault();
                loginForm.dispatchEvent(new Event('submit'));
            }
        });

        // Log page visit for security monitoring
        console.log('%c🔒 Admin Login Access Attempt', 'color: #004080; font-weight: bold; font-size: 14px;');
        console.log('%cTimestamp: ' + new Date().toLocaleString(), 'color: #666;');
        console.log('%cBrowser: ' + navigator.userAgent, 'color: #666;');
        console.log('%c⚠️  Warning: This is a secured system. Unauthorized access is prohibited.', 'color: #d32f2f; font-weight: bold;');
    </script>
</body>
</html>