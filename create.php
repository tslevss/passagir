<?php
session_start();
if (!isset($_SESSION['user_id'])) die('Чтобы записаться на курсы, надо войти в аккаунт.');

$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review = $_POST['review'];
    $date = $_POST['date'];
    $venue = $_POST['venue'];
    $payment = $_POST['payment'];
    $status = 'Новая'; // Статус устанавливается автоматически
    
    include('db.php');
    
    // Для безопасности в реальном проекте используйте подготовленные выражения (prepared statements)
    $user_id = (int)$_SESSION['user_id']; // Защита от SQL-инъекций
    $review = $con->real_escape_string($review);
    $venue = $con->real_escape_string($venue);
    $payment = $con->real_escape_string($payment);
    
    $query = $con->query("INSERT INTO request (review, date, curses, payment, user_id, status) 
                          VALUES ('$review', '$date', '$venue', '$payment', '$user_id', '$status')");
    
    if (!$query) {
        $error = true;
        $error_msg = 'Ошибка: ' . $con->error;
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись на курсы — Пассажирам.РФ</title>
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
        }

        .container {
            max-width: 550px;
            margin: 0 auto;
            background: var(--white);
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            animation: slideInUp 0.5s ease-out;
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

        /* Стили для кнопок навигации */
        .nav-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            justify-content: center;
        }

        .btn-nav {
            display: inline-block;
            padding: 10px 20px;
            background: var(--blue);
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            transition: all 0.2s ease;
            font-weight: 700;
            font-size: 14px;
        }

        .btn-nav:hover {
            background: var(--dark-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .btn-nav:active {
            transform: translateY(0);
        }

        h1 {
            text-align: center;
            margin-bottom: 24px;
            color: var(--dark-blue);
            font-size: 28px;
            font-weight: 700;
        }

        /* Стили формы */
        form {
            animation: formFadeIn 0.4s ease-out 0.1s both;
        }

        @keyframes formFadeIn {
            from {
                opacity: 0;
                transform: scale(0.98);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 14px;
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            font-family: 'PT Sans', sans-serif;
            font-weight: 400;
            transition: all 0.2s ease;
            background: var(--white);
        }

        form input:focus,
        form select:focus,
        form textarea:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        form input:hover,
        form select:hover,
        form textarea:hover {
            border-color: var(--blue);
        }

        form textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Кнопка отправки */
        form button {
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
        }

        form button:hover {
            background: var(--dark-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        form button:active {
            transform: translateY(0);
        }

        /* Сообщения об успехе/ошибке */
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

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            text-align: center;
            border-left: 4px solid #dc3545;
            font-size: 14px;
        }

        .success-message a,
        .error-message a {
            color: inherit;
            font-weight: 700;
            text-decoration: underline;
            transition: color 0.2s ease;
        }

        .success-message a:hover,
        .error-message a:hover {
            color: var(--blue);
        }

        /* Эффект загрузки для кнопки */
        form button.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        form button.loading::after {
            content: '';
            display: inline-block;
            width: 18px;
            height: 18px;
            margin-left: 10px;
            border: 2px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Адаптивность */
        @media (max-width: 600px) {
            .container {
                padding: 24px 20px;
                margin: 0 15px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .nav-buttons {
                flex-direction: column;
            }
            
            form input,
            form select,
            form textarea {
                padding: 10px;
                font-size: 14px;
            }
            
            form button {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Кнопки навигации -->
        <div class="nav-buttons">
            <a href="index.php" class="btn-nav">🏠 Главная</a>
            <a href="history.php" class="btn-nav">📋 Мои заявки</a>
        </div>
        
        <h1>🚍 Запись на курсы вождения</h1>

        <?php if ($success): ?>
            <div class="success-message">
                ✅ Заявка на обучение успешно отправлена!<br><br>
                <a href="history.php">📋 Перейти к истории моих заявок →</a>
                <br><br>
                Наш менеджер свяжется с вами в ближайшее время для уточнения деталей.
            </div>
        <?php elseif ($error): ?>
            <div class="error-message">
                ❌ Ошибка при отправке заявки: <?php echo htmlspecialchars($error_msg); ?><br>
                <a href="javascript:history.back()">◀ Попробовать снова</a>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" id="requestForm">
            
            <label for="venue">🚌 Выберите направление обучения</label>
            <select id="venue" name="venue" required>
                <option value="Водитель автобуса">🚌 Водитель автобуса (категория D)</option>
                <option value="Водитель электробуса">⚡ Водитель электробуса</option>
                <option value="Водитель трамвая">🚋 Водитель трамвая</option>
            </select>

            <label for="date">📅 Желаемая дата начала обучения</label>
            <input id="date" type="datetime-local" name="date" required>

            <label for="payment">💳 Форма оплаты</label>
            <select id="payment" name="payment" required>
                <option value="наличные">💵 Наличные (в кассу учебного центра)</option>
                <option value="перевод">🏦 Безналичный перевод по номеру счета</option>
                <option value="карта">💳 Банковской картой онлайн</option>
            </select>

            <label for="review">📝 Дополнительная информация</label>
            <textarea id="review" name="review" placeholder="Укажите наличие водительского стажа, желаемый график обучения, особые пожелания..."></textarea>
             
            <button type="submit" id="submitBtn">📝 Отправить заявку на обучение</button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        // Анимация при отправке формы
        const form = document.getElementById('requestForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                // Добавляем класс загрузки на кнопку
                submitBtn.classList.add('loading');
                submitBtn.textContent = 'Отправка';
            });
        }

        // Анимация при фокусе на полях
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transition = 'all 0.2s ease';
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.style.transform = 'scale(1)';
                }
            });
        });
    </script>
</body>
</html>