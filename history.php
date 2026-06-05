<?php
session_start();
if(!isset($_SESSION['user_id'])) die('Чтобы посмотреть историю заявок, надо войти в аккаунт.');
include('db.php');

// Код изменения отзыва
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int)$_SESSION['user_id'];
    $request_id = (int)$_POST['request_id'];
    $con->query("UPDATE request SET review='$review' WHERE id='$request_id' AND user_id='$user_id'");
    echo '<div class="success-message">✓ Отзыв успешно сохранён!</div>';
}

// Код истории заявок
$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if(!$query) die('query error: ' . $con->error); 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заявки — Пассажирам.РФ</title>
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
            max-width: 900px;
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

        /* Кнопка на главную */
        .btn-home {
            display: inline-block;
            background: var(--blue);
            color: var(--white);
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 24px;
            transition: all 0.2s ease;
            font-weight: 700;
            font-size: 14px;
        }

        .btn-home:hover {
            background: var(--dark-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        h1 {
            text-align: center;
            margin-bottom: 28px;
            color: var(--dark-blue);
            font-size: 28px;
            font-weight: 700;
        }

        /* Сообщение об успехе */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #28a745;
            font-size: 14px;
            font-weight: 400;
            animation: slideInRight 0.4s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Карточка заявки */
        .request {
            border: 1px solid var(--border-light);
            margin: 20px 0;
            padding: 20px;
            border-radius: 12px;
            background: var(--white);
            transition: all 0.2s ease;
        }

        .request:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border-color: var(--blue);
        }

        .request h2 {
            margin-top: 0;
            color: var(--dark-blue);
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-light);
        }

        .request b {
            color: var(--text-dark);
            font-weight: 700;
        }

        .request p {
            margin: 8px 0;
            font-weight: 400;
            font-size: 15px;
        }

        /* Статусы заявок */
        .status-new {
            color: #856404;
            font-weight: 700;
            background: #fff3cd;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .status-processing {
            color: #0c5460;
            font-weight: 700;
            background: #d1ecf1;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .status-completed {
            color: #155724;
            font-weight: 700;
            background: #d4edda;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .status-cancelled {
            color: #721c24;
            font-weight: 700;
            background: #f8d7da;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        /* Форма отзыва */
        .review-form {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px dashed var(--border-light);
        }

        .review-form form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .review-form input[type="text"] {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'PT Sans', sans-serif;
            font-weight: 400;
            transition: all 0.2s ease;
        }

        .review-form input[type="text"]:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .review-form button {
            padding: 10px 20px;
            background: var(--blue);
            color: var(--white);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'PT Sans', sans-serif;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .review-form button:hover {
            background: var(--dark-blue);
            transform: translateY(-1px);
        }

        /* Отзыв */
        .review-text {
            margin-top: 12px;
            padding: 10px 14px;
            background: var(--light-bg);
            border-radius: 8px;
            color: var(--text-dark);
            font-weight: 400;
            font-size: 14px;
            border-left: 3px solid var(--blue);
        }

        .review-text b {
            color: var(--dark-blue);
        }

        /* Пустое состояние */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 400;
        }

        .empty-state a {
            color: var(--blue);
            text-decoration: none;
            font-weight: 700;
        }

        .empty-state a:hover {
            text-decoration: underline;
        }

        /* Кнопка создания заявки */
        .create-button {
            text-align: center;
            margin-top: 30px;
        }

        .create-button a {
            background: var(--blue);
            color: var(--white);
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .create-button a:hover {
            background: var(--dark-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        /* Адаптивность */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .request h2 {
                font-size: 18px;
            }
            
            .review-form form {
                flex-direction: column;
            }
            
            .review-form input[type="text"] {
                width: 100%;
            }
            
            .review-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-home">🏠 На главную</a>
        
        <h1>📋 Мои заявки на обучение</h1>
        
        <?php
        $i = 0;
        if($query->num_rows == 0) {
            echo '<div class="empty-state">🎓 У вас пока нет заявок на обучение.<br><br>✍️ <a href="create.php">Создать новую заявку</a></div>';
        }
        while($request = $query->fetch_assoc()) {
            $i++; 
            
            // Определяем класс статуса
            $status_class = 'status-new';
            $status_text = htmlspecialchars($request['status']);
            if($status_text == 'Новая') $status_class = 'status-new';
            elseif($status_text == 'В обработке') $status_class = 'status-processing';
            elseif($status_text == 'Завершено') $status_class = 'status-completed';
            elseif($status_text == 'Отменено') $status_class = 'status-cancelled';
            
            echo '
            <div class="request">
                <h2>📄 Заявка #' . $request['id'] . '</h2>
                <p><b>📅 Дата подачи:</b> ' . htmlspecialchars($request['date']) . '</p>
                <p><b>🚌 Направление обучения:</b> ' . htmlspecialchars($request['curses']) . '</p>
                <p><b>💳 Форма оплаты:</b> ' . htmlspecialchars($request['payment']) . '</p>
                <p><b>📊 Статус:</b> <span class="' . $status_class . '">' . $status_text . '</span></p>';
            
            // Если есть отзыв, показываем его
            if(!empty($request['review'])) {
                echo '<div class="review-text"><b>⭐ Ваш отзыв:</b> ' . htmlspecialchars($request['review']) . '</div>';
            }
            
            // Если статус "Завершено" - показываем форму для отзыва
            if($request['status'] === 'Завершено') {
                echo '
                <div class="review-form">
                    <form action="" method="POST">
                        <input type="hidden" name="request_id" value="' . $request['id'] . '">
                        <input type="text" name="review" placeholder="✍️ Оставьте отзыв о качестве обучения..." value="' . htmlspecialchars($request['review'] ?? '') . '">
                        <button type="submit">⭐ Оставить отзыв</button>
                    </form>
                </div>';
            }
            echo '</div>';
        }
        ?>
        
        <div class="create-button">
            <a href="create.php">🎓 Записаться на курсы</a>
        </div>
    </div>
</body>
</html>