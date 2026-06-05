<?php
session_start();

// Выход из системы
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Проверяем, установлен ли ключ admin в сессии
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Пассажирам.РФ — запись на курсы водителей пассажирских перевозок</title>
  <!-- Подключение шрифта PT Sans -->
  <link href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&family=PT+Sans:ital@1&display=swap" rel="stylesheet">
  <style>
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

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'PT Sans', sans-serif;
      background-color: var(--light-bg);
      color: var(--text-dark);
      line-height: 1.5;
      min-height: 100vh;
    }

    /* ===== ШАПКА ===== */
    .header {
      background: var(--white);
      border-bottom: 3px solid var(--blue);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 16px 24px;
    }

    .logo {
      font-size: 24px;
      font-weight: 700;
      color: var(--dark-blue);
      text-decoration: none;
      letter-spacing: 0.5px;
      transition: color 0.2s ease;
    }

    .logo:hover {
      color: var(--blue);
    }

    .nav-buttons {
      display: flex;
      gap: 12px;
    }

    .nav-buttons a {
      padding: 8px 20px;
      border-radius: 6px;
      font-size: 16px;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    /* Кнопки */
    .btn-login, .btn-register, .btn-admin, .btn-lk, .btn-create, .btn-exit {
      background: transparent;
      border: 1.5px solid var(--blue);
      color: var(--blue);
    }

    .btn-login:hover, .btn-register:hover, .btn-admin:hover, 
    .btn-lk:hover, .btn-create:hover, .btn-exit:hover {
      background: var(--blue);
      color: var(--white);
    }

    /* ===== СЛАЙДЕР (карусель) ===== */
    .slideshow-container {
      max-width: 1000px;
      position: relative;
      margin: 32px auto;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      background: var(--white);
    }

    .mySlides {
      display: none;
    }

    .fade {
      animation: fadeIn 1.2s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0.4; }
      to { opacity: 1; }
    }

    .mySlides img {
      width: 100%;
      height: 420px;
      object-fit: cover;
    }

    .slide-text {
      position: absolute;
      bottom: 20px;
      left: 20px;
      background: rgba(13, 71, 161, 0.85);
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 18px;
      font-weight: 700;
      color: var(--white);
    }

    /* Стрелки слайдера */
    .prev, .next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(0, 0, 0, 0.5);
      color: var(--white);
      border: none;
      cursor: pointer;
      padding: 12px 18px;
      font-size: 18px;
      border-radius: 50%;
      transition: 0.2s;
      font-weight: 700;
    }

    .prev { left: 12px; }
    .next { right: 12px; }

    .prev:hover, .next:hover {
      background: var(--blue);
    }

    /* Точки */
    .dot-container {
      text-align: center;
      padding: 16px 0;
    }

    .dot {
      cursor: pointer;
      height: 12px;
      width: 12px;
      margin: 0 6px;
      background-color: var(--gray);
      border-radius: 50%;
      display: inline-block;
      transition: 0.2s;
    }

    .dot.active, .dot:hover {
      background-color: var(--blue);
    }

    /* ===== СЕКЦИЯ ПРЕИМУЩЕСТВ ===== */
    .features-section {
      max-width: 1200px;
      margin: 48px auto;
      padding: 0 24px;
    }

    .features-title {
      text-align: center;
      font-size: 36px;
      font-weight: 700;
      color: var(--dark-blue);
      margin-bottom: 40px;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }

    .feature-card {
      background: var(--white);
      padding: 28px 20px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border: 1px solid var(--border-light);
    }

    .feature-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .feature-card h3 {
      font-size: 24px;
      font-weight: 700;
      color: var(--dark-blue);
      margin-bottom: 12px;
    }

    .feature-card p {
      font-size: 16px;
      color: var(--text-dark);
      line-height: 1.5;
    }

    /* ===== ПОДВАЛ ===== */
    .footer {
      text-align: center;
      padding: 24px;
      background: var(--white);
      border-top: 1px solid var(--border-light);
      font-size: 12px;
      font-style: italic;
      color: var(--text-muted);
      margin-top: 40px;
    }

    /* ===== АДАПТИВНОСТЬ ===== */
    @media (max-width: 768px) {
      .nav {
        flex-direction: column;
        gap: 12px;
      }
      
      .mySlides img {
        height: 260px;
      }
      
      .slide-text {
        font-size: 14px;
        bottom: 12px;
        left: 12px;
      }
      
      .prev, .next {
        padding: 8px 12px;
        font-size: 14px;
      }

      .features-title {
        font-size: 28px;
      }

      .feature-card h3 {
        font-size: 20px;
      }

      .feature-card p {
        font-size: 14px;
      }
    }
  </style>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>

<header class="header">
  <div class="nav">
    <a href="index.php" class="logo">🚍 Пассажирам.РФ</a>
    <div class="nav-buttons">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php" class="btn-login">Войти</a>
        <a href="register.php" class="btn-register">Регистрация</a>
      <?php elseif ($is_admin): ?>
        <a href="admin.php" class="btn-admin">Панель администратора</a>
        <a href="?logout=1" class="btn-exit">Выход</a>
      <?php elseif (isset($_SESSION['user_id'])): ?>
        <a href="history.php" class="btn-lk">Мои заявки</a>
        <a href="create.php" class="btn-create">Новая заявка</a>
        <a href="?logout=1" class="btn-exit">Выход</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Слайдер с обучением пассажирским перевозкам (исправленные рабочие картинки) -->
<div class="slideshow-container">
  <div class="mySlides fade">
    <img src="https://www.m24.ru/b/d/nBkSUhL2g1kgms6wPqzZvc62gYT28pj21CLFh_fH_nKUPXuaDyXTjHou4MVO6BCVoZKf9GqVe5Q_CPawk214LyWK9G1N5ho=bCJb6WxPBmMVFYvl90sZCg.jpg" alt="Автобус в городе">
    <div class="slide-text">🚌 Курсы водителей автобусов</div>
  </div>

  <div class="mySlides fade">
    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/KAMAZ-6282._Электробус._Москва_07.08.2025.jpg/960px-KAMAZ-6282._Электробус._Москва_07.08.2025.jpg" alt="Современный электробус">
    <div class="slide-text">⚡ Электробусы — транспорт будущего</div>
  </div>

  <div class="mySlides fade">
    <img src="https://гэт.рус/images/news/1439537842.jpg" alt="Трамвай на рельсах">
    <div class="slide-text">🚋 Вождение трамвая с нуля</div>
  </div>

  <div class="mySlides fade">
    <img src="https://avatars.mds.yandex.net/i?id=0d8f871da94e9d66f5eac92d4dd27fbe_l-5869113-images-thumbs&n=13" alt="Обучение в классе">
    <div class="slide-text">📚 Теория ПДД и практика</div>
  </div>

  <a class="prev" onclick="plusSlides(-1)">❮</a>
  <a class="next" onclick="plusSlides(1)">❯</a>
</div>

<div class="dot-container">
  <span class="dot" onclick="currentSlide(1)"></span>
  <span class="dot" onclick="currentSlide(2)"></span>
  <span class="dot" onclick="currentSlide(3)"></span>
  <span class="dot" onclick="currentSlide(4)"></span>
</div>

<!-- Основной контент — преимущества курсов -->
<section class="features-section">
  <h1 class="features-title">Почему выбирают «Пассажирам.РФ»?</h1>
  
  <div class="features-grid">
    <div class="feature-card">
      <h3>🚍 Обучение на автобус</h3>
      <p>Полный курс подготовки водителей категории «D» с опытными инструкторами и современным автопарком.</p>
    </div>
    
    <div class="feature-card">
      <h3>⚡ Электробусы — новый профиль</h3>
      <p>Освойте управление экологичным транспортом будущего. Востребованная специальность в мегаполисах.</p>
    </div>
    
    <div class="feature-card">
      <h3>🚋 Вождение трамвая</h3>
      <p>Практические занятия на действующих маршрутах. Выдаём свидетельство установленного образца.</p>
    </div>
  </div>
</section>

<footer class="footer">
  © 2025 Пассажирам.РФ — запись на курсы водителей пассажирского транспорта в вашем городе
</footer>

<script>
// Слайдер
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");

  if (n > slides.length) { slideIndex = 1 }
  if (n < 1) { slideIndex = slides.length }

  for (let i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (let i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }

  slides[slideIndex-1].style.display = "block";
  dots[slideIndex-1].className += " active";
}

// Автосмена каждые 3 секунды
let slideInterval = setInterval(() => plusSlides(1), 3000);

const container = document.querySelector('.slideshow-container');
if (container) {
  container.addEventListener('mouseenter', () => clearInterval(slideInterval));
  container.addEventListener('mouseleave', () => {
    slideInterval = setInterval(() => plusSlides(1), 3000);
  });
}
</script>
</body>
</html>