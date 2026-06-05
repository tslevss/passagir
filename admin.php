<?php
include('db.php');
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Обработка выхода
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Допустимые статусы для курсов обучения
$valid_statuses = ['Новая', 'Идет обучение', 'Обучение завершено'];
$status_updated = false;

// Обработка изменения статуса заявки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';

    // Валидация статуса
    if (!in_array($status, $valid_statuses, true)) {
        die('Недопустимый статус заявки');
    }

    // Использование подготовленных выражений
    $stmt = $con->prepare("UPDATE request SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $request_id);

    if (!$stmt->execute()) {
        die('Ошибка обновления: ' . $con->error);
    } else {
        $status_updated = true;
    }
}

// Получение заявок с пагинацией (10 заявок на страницу)
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$query = $con->query("
    SELECT request.*, users.login, users.fullname,
           COUNT(*) OVER() as total_count
    FROM request
    INNER JOIN users ON request.user_id = users.id
    ORDER BY request.date DESC
    LIMIT $limit OFFSET $offset
");

if (!$query) die('Ошибка запроса: ' . $con->error);

// Подсчёт статистики одним запросом
$stats_query = $con->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new_requests,
        SUM(CASE WHEN status = 'Идет обучение' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'Обучение завершено' THEN 1 ELSE 0 END) as completed
    FROM request
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора — Пассажирам.РФ</title>
    <!-- Подключение шрифта PT Sans -->
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&family=PT+Sans:ital@1&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --blue: #007bff;          /* Голубой */
            --dark-blue: #0d47a1;     /* Тёмно-синий */
            --gray: #6c757d;          /* Серый */
            --white: #ffffff;         /* Белый */
            --light-bg: #f8f9fa;      /* Светлый фон */
            --border-light: #dee2e6;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --border-radius: 12px;
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'PT Sans', sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* Шапка */
        .header {
            background: var(--white);
            padding: 25px 30px;
            border-bottom: 2px solid var(--blue);
        }

        .header h1 {
            color: var(--dark-blue);
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 400;
        }

        /* Навигация */
        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: var(--light-bg);
            border-bottom: 1px solid var(--border-light);
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'PT Sans', sans-serif;
            font-weight: 700;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--blue);
            color: var(--white);
        }

        .btn-outline {
            background: transparent;
            color: var(--blue);
            border: 1.5px solid var(--blue);
        }

        .btn-outline:hover {
            background: var(--blue);
            color: var(--white);
            transform: translateY(-1px);
        }

        .btn-primary:hover {
            background: var(--dark-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        /* Статистика */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 25px 30px;
            background: var(--light-bg);
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid var(--blue);
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Список заявок */
        .requests-container {
            padding: 0 30px 30px;
        }

        .request-item {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid var(--border-light);
            transition: all 0.2s ease;
        }

        .request-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-color: var(--blue);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .user-info h3 {
            color: var(--dark-blue);
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 18px;
        }

        .user-info p {
            color: var(--text-muted);
            font-weight: 400;
            font-size: 14px;
        }

        .request-id {
            background: var(--light-bg);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            color: var(--dark-blue);
            font-size: 13px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
            margin-left: 10px;
        }

        .status-new {
            background: #fff3cd;
            color: #856404;
        }

        .status-progress {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .detail-item {
            padding: 12px;
            background: var(--light-bg);
            border-radius: var(--border-radius);
        }

        .detail-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 400;
        }

        .detail-value {
            font-size: 15px;
            color: var(--text-dark);
            margin-top: 5px;
            font-weight: 400;
        }

        /* Форма изменения статуса */
        .status-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-light);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 14px;
        }

        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'PT Sans', sans-serif;
            font-weight: 400;
            transition: all 0.2s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .btn-save {
            width: 100%;
            padding: 10px;
            background: var(--blue);
            color: var(--white);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'PT Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .btn-save:hover {
            background: var(--dark-blue);
            transform: translateY(-1px);
        }

        /* Пагинация */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 30px 0;
            padding-bottom: 30px;
        }

        .page-link {
            padding: 6px 14px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark-blue);
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .page-link:hover,
        .page-link.active {
            background: var(--blue);
            color: var(--white);
            border-color: var(--blue);
        }

        /* Пустое состояние */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--light-bg);
            border-radius: var(--border-radius);
        }

        .empty-state i {
            font-size: 48px;
            color: var(--blue);
            margin-bottom: 15px;
        }

        .empty-state h3 {
            color: var(--dark-blue);
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 20px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Уведомление */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 14px 22px;
            background: var(--blue);
            color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            z-index: 1000;
            animation: slideInRight 0.3s ease-out, fadeOut 0.3s ease-out 2.5s forwards;
            font-weight: 700;
            font-size: 14px;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 8px;
            }

            .nav-bar {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .request-item {
                padding: 18px;
            }

            .request-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-bus"></i> Панель администратора</h1>
            <p class="subtitle">Управление заявками на обучение водителей пассажирских перевозок</p>
        </div>

        <div class="nav-bar">
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-home"></i> Главная
            </a>
            <a href="?logout=1" class="btn btn-outline" onclick="return confirm('Выйти из аккаунта?')">
                <i class="fas fa-sign-out-alt"></i> Выход
            </a>
        </div>

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" style="color: var(--blue);"><?= $stats['total'] ?></div>
                <div class="stat-label">Всего заявок</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #856404;"><?= $stats['new_requests'] ?></div>
                <div class="stat-label">🆕 Новые</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #0c5460;"><?= $stats['in_progress'] ?></div>
                <div class="stat-label">📚 Идет обучение</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #155724;"><?= $stats['completed'] ?></div>
                <div class="stat-label">✅ Обучение завершено</div>
            </div>
        </div>

        <!-- Список заявок -->
        <div class="requests-container">
            <?php
            if ($query->num_rows === 0) {
            ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Заявок пока нет</h3>
                    <p>Когда пользователи оставят заявки на обучение, они появятся здесь</p>
                </div>
            <?php } else {
                while ($request = $query->fetch_assoc()) {
                    // Определяем класс для статуса
                    $status_class = match($request['status']) {
                        'Новая' => 'status-new',
                        'Идет обучение' => 'status-progress',
                        'Обучение завершено' => 'status-completed',
                        default => 'status-new'
                    };
            ?>
                <div class="request-item">
                    <div class="request-header">
                        <div class="user-info">
                            <h3><i class="fas fa-user"></i> <?= htmlspecialchars($request['login']) ?></h3>
                            <p><?= htmlspecialchars($request['fullname']) ?></p>
                        </div>
                        <div>
                            <span class="request-id">Заявка №<?= htmlspecialchars($request['id']) ?></span>
                            <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span>
                        </div>
                    </div>

                    <div class="request-details">
                        <div class="detail-item">
                            <div class="detail-label"><i class="far fa-calendar-alt"></i> Дата подачи</div>
                            <div class="detail-value"><?= htmlspecialchars($request['date']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-graduation-cap"></i> Направление обучения</div>
                            <div class="detail-value"><?= htmlspecialchars($request['curses'] ?? '—') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-credit-card"></i> Форма оплаты</div>
                            <div class="detail-value"><?= htmlspecialchars($request['payment'] ?? '—') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-comment"></i> Доп. информация</div>
                            <div class="detail-value"><?= htmlspecialchars($request['review'] ?? '—') ?></div>
                        </div>
                    </div>

                    <!-- Форма изменения статуса -->
                    <div class="status-form">
                        <form method="POST" class="status-update-form">
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">

                            <div class="form-group">
                                <label class="form-label" for="status_<?= $request['id'] ?>">
                                    <i class="fas fa-tag"></i> Изменить статус заявки:
                                </label>
                                <select name="status" id="status_<?= $request['id'] ?>" class="form-select">
                                    <option value="Новая" <?= $request['status'] == 'Новая' ? 'selected' : '' ?>>
                                        🆕 Новая
                                    </option>
                                    <option value="Идет обучение" <?= $request['status'] == 'Идет обучение' ? 'selected' : '' ?>>
                                        📚 Идет обучение
                                    </option>
                                    <option value="Обучение завершено" <?= $request['status'] == 'Обучение завершено' ? 'selected' : '' ?>>
                                        ✅ Обучение завершено
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            <?php
                }
            }
            ?>
        </div>

        <!-- Пагинация -->
        <?php if ($stats['total'] > $limit): ?>
            <div class="pagination">
                <?php
                $total_pages = ceil($stats['total'] / $limit);
                for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="?page=<?= $i ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Уведомление об успехе -->
    <?php if ($status_updated): ?>
        <div class="notification">
            <i class="fas fa-check-circle"></i> Статус заявки успешно обновлён!
        </div>
    <?php endif; ?>

    <script>
        // Обработка отправки форм статуса
        document.querySelectorAll('.status-update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('.btn-save');
                const originalText = submitBtn.innerHTML;

                // Блокировка кнопки на время обработки
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';

                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 2000);
            });
        });

        // Плавная прокрутка к уведомлениям
        const notification = document.querySelector('.notification');
        if (notification) {
            notification.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            setTimeout(() => {
                if (notification) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        }
    </script>
</body>
</html>