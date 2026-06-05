<?php
session_start();

// Если пользователь уже авторизован, перенаправляем
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = false;
$error_message = '';
$success = false;
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    $form_data = compact('login', 'fullname', 'phone', 'email');
    
    // Валидация данных
    $errors = [];
    
    if (empty($login)) {
        $errors[] = 'Логин обязателен для заполнения';
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors[] = 'Логин должен содержать только латиницу и цифры, минимум 6 символов';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен для заполнения';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов';
    }
    
    if (empty($fullname)) {
        $errors[] = 'ФИО обязательно для заполнения';
    } elseif (strlen($fullname) < 5) {
        $errors[] = 'Введите полное ФИО';
    }
    
    if (empty($phone)) {
        $errors[] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors[] = 'Телефон должен быть в формате +7(XXX)XXX-XX-XX';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($errors)) {
        include('db.php');
        
        // Проверка на существование логина
        $stmt = $con->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = true;
            $error_message = 'Пользователь с таким логином уже существует';
        } else {
            // Проверка на существование email
            $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = true;
                $error_message = 'Пользователь с таким email уже существует';
            } else {
                // Рекомендуется хешировать пароль
                // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Для совместимости с существующей системой пока оставляем как есть
                
                $stmt = $con->prepare("INSERT INTO users (login, password, fullname, phone, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $login, $password, $fullname, $phone, $email);
                
                if ($stmt->execute()) {
                    $success = true;
                    // Перенаправление через 2 секунды
                    header('refresh:2;url=login.php');
                } else {
                    $error = true;
                    $error_message = 'Ошибка при регистрации: ' . $con->error;
                }
                $stmt->close();
            }
        }
        $stmt->close();
    } else {
        $error = true;
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Пассажирам.РФ</title>
    <!-- Подключение шрифта PT Sans -->
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&family=PT+Sans:ital@1&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ===== ЦВЕТОВАЯ СХЕМА ===== */
        :root {
            --blue: #007bff;          /* Голубой */
            --dark-blue: #0d47a1;     /* Тёмно-синий */
            --gray: #6c757d;          /* Серый */
            --white: #ffffff;         /* Белый */
            --light-bg: #f8f9fa;      /* Светлый фон */
            --border-light: #dee2e6;
            --text-dark: #212529;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'PT Sans', sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Анимированные круги на фоне */
        .circle {
            position: fixed;
            border-radius: 50%;
            background: rgba(0, 123, 255, 0.05);
            animation: float 20s infinite linear;
            z-index: 0;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
            }
        }

        .container {
            max-width: 520px;
            width: 100%;
            margin: 0 auto;
            background: var(--white);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            animation: slideInUp 0.5s ease-out;
            position: relative;
            z-index: 1;
            border: 1px solid var(--border-light);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Логотип */
        .logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: var(--dark-blue);
            margin-bottom: 8px;
        }

        .logo p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 400;
        }

        /* Заголовок формы */
        .form-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .form-header h2 {
            color: var(--dark-blue);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Сообщение об ошибке */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            text-align: center;
            border-left: 4px solid #dc3545;
            font-size: 14px;
            font-weight: 400;
        }

        /* Сообщение об успехе */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            text-align: center;
            border-left: 4px solid #28a745;
            font-size: 15px;
        }

        /* Стили формы */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 14px;
        }

        .form-group label span {
            margin-right: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-size: 16px;
            font-family: 'PT Sans', sans-serif;
            font-weight: 400;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .form-group input:hover {
            border-color: var(--blue);
        }

        .form-group input.error {
            border-color: #dc3545;
            background: #fff5f5;
        }

        /* Подсказки */
        .hint {
            font-size: 12px;
            font-style: italic;
            color: var(--text-muted);
            margin-top: 6px;
            display: block;
        }

        /* Кнопка регистрации */
        .btn-register {
            width: 100%;
            padding: 14px;
            background: var(--blue);
            color: var(--white);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'PT Sans', sans-serif;
            font-weight: 700;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .btn-register:hover {
            background: var(--dark-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Ссылки */
        .form-footer {
            margin-top: 24px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--border-light);
        }

        .form-footer p {
            color: var(--text-muted);
            margin-bottom: 10px;
            font-size: 14px;
        }

        .login-link {
            color: var(--blue);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease;
        }

        .login-link:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }

        .back-home {
            display: inline-block;
            margin-top: 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-style: italic;
            transition: color 0.2s ease;
        }

        .back-home:hover {
            color: var(--blue);
        }

        /* Адаптивность */
        @media (max-width: 550px) {
            .container {
                padding: 24px 20px;
            }
            
            .logo h1 {
                font-size: 24px;
            }
            
            .form-header h2 {
                font-size: 20px;
            }
            
            .btn-register {
                padding: 12px;
            }
            
            .form-group input {
                padding: 10px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🚍 Пассажирам.РФ</h1>
            <p>Запись на курсы водителей пассажирских перевозок</p>
        </div>

        <div class="form-header">
            <h2>Создание аккаунта</h2>
            <p>Заполните форму для записи на обучение</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                ⚠️ <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                ✅ Регистрация успешно завершена!<br>
                <small>Перенаправление на страницу входа...</small>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="fullname">
                    <span>👤</span> ФИО
                </label>
                <input type="text" id="fullname" name="fullname" 
                       value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>"
                       placeholder="Иванов Иван Иванович" required>
                <span class="hint">Ваше полное имя для документов</span>
            </div>

            <div class="form-group">
                <label for="phone">
                    <span>📱</span> Телефон
                </label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                       placeholder="+7(XXX)XXX-XX-XX" 
                       pattern="\+7\(\d{3}\)\d{3}-\d{2}-\d{2}" required>
                <span class="hint">Формат: +7(XXX)XXX-XX-XX</span>
            </div>

            <div class="form-group">
                <label for="email">
                    <span>📧</span> Email
                </label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                       placeholder="example@mail.com" required>
                <span class="hint">На этот адрес будут приходить уведомления о записи</span>
            </div>

            <div class="form-group">
                <label for="login">
                    <span>🔑</span> Логин
                </label>
                <input type="text" id="login" name="login" 
                       value="<?php echo htmlspecialchars($form_data['login'] ?? ''); ?>"
                       placeholder="ivan123" 
                       pattern="[a-zA-Z0-9]{6,}" required>
                <span class="hint">Только латиница и цифры, минимум 6 символов</span>
            </div>

            <div class="form-group">
                <label for="password">
                    <span>🔒</span> Пароль
                </label>
                <input type="password" id="password" name="password" 
                       placeholder="Минимум 8 символов" minlength="8" required>
                <span class="hint" id="passwordHint">Минимум 8 символов</span>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <span>✅</span> Подтверждение пароля
                </label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Повторите пароль" required>
                <span class="hint" id="confirmHint"></span>
            </div>

            <button type="submit" class="btn-register" id="submitBtn">
                📝 Зарегистрироваться и записаться
            </button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            <p>Уже есть аккаунт? <a href="login.php" class="login-link">Войти →</a></p>
            <a href="index.php" class="back-home">← Вернуться на главную</a>
        </div>
    </div>

    <script>
        // Клиентская валидация
        const form = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const confirmHint = document.getElementById('confirmHint');
        const passwordHint = document.getElementById('passwordHint');
        const submitBtn = document.getElementById('submitBtn');
        
        // Проверка пароля в реальном времени
        if (password) {
            password.addEventListener('input', function() {
                const value = this.value;
                if (value.length >= 8) {
                    passwordHint.innerHTML = '✅ Пароль надёжный';
                    passwordHint.style.color = '#28a745';
                } else {
                    passwordHint.innerHTML = '⚠️ Минимум 8 символов';
                    passwordHint.style.color = '#dc3545';
                }
                
                if (confirmPassword.value) {
                    checkPasswordsMatch();
                }
            });
        }
        
        // Проверка совпадения паролей
        function checkPasswordsMatch() {
            if (password.value === confirmPassword.value && password.value.length >= 8) {
                confirmHint.innerHTML = '✅ Пароли совпадают';
                confirmHint.style.color = '#28a745';
                return true;
            } else if (confirmPassword.value.length > 0) {
                confirmHint.innerHTML = '❌ Пароли не совпадают';
                confirmHint.style.color = '#dc3545';
                return false;
            }
            return false;
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', checkPasswordsMatch);
        }
        
        // Валидация телефона
        const phone = document.getElementById('phone');
        if (phone) {
            phone.addEventListener('input', function(e) {
                let value = this.value;
                if (value.length === 1 && value !== '+') {
                    this.value = '+' + value;
                }
            });
        }
        
        // Валидация перед отправкой
        if (form) {
            form.addEventListener('submit', function(e) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    showInlineError('Пароли не совпадают');
                    confirmPassword.style.borderColor = '#dc3545';
                    return false;
                }
                
                if (password.value.length < 8) {
                    e.preventDefault();
                    showInlineError('Пароль должен содержать минимум 8 символов');
                    password.style.borderColor = '#dc3545';
                    return false;
                }
                
                const phonePattern = /^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
                if (!phonePattern.test(phone.value)) {
                    e.preventDefault();
                    showInlineError('Введите телефон в формате +7(XXX)XXX-XX-XX');
                    phone.style.borderColor = '#dc3545';
                    return false;
                }
                
                const loginPattern = /^[a-zA-Z0-9]{6,}$/;
                const login = document.getElementById('login');
                if (!loginPattern.test(login.value)) {
                    e.preventDefault();
                    showInlineError('Логин должен содержать только латиницу и цифры, минимум 6 символов');
                    login.style.borderColor = '#dc3545';
                    return false;
                }
                
                submitBtn.innerHTML = '⏳ Регистрация...';
                submitBtn.disabled = true;
            });
        }
        
        function showInlineError(message) {
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            const formHeader = document.querySelector('.form-header');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `⚠️ ${message}`;
            formHeader.insertAdjacentElement('afterend', errorDiv);
            
            setTimeout(() => {
                errorDiv.style.opacity = '0';
                setTimeout(() => errorDiv.remove(), 300);
            }, 3000);
        }
        
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#dee2e6';
            });
        });
        
        // Создание анимированных кругов на фоне
        function createCircles() {
            for (let i = 0; i < 8; i++) {
                const circle = document.createElement('div');
                circle.className = 'circle';
                const size = Math.random() * 80 + 40;
                circle.style.width = size + 'px';
                circle.style.height = size + 'px';
                circle.style.left = Math.random() * 100 + '%';
                circle.style.bottom = '-' + size + 'px';
                circle.style.animationDuration = Math.random() * 15 + 10 + 's';
                circle.style.animationDelay = Math.random() * 5 + 's';
                document.body.appendChild(circle);
            }
        }
        
        createCircles();
    </script>
</body>
</html>