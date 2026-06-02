<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Конференции.РФ — бронирование помещений для конференций</title>
  <meta name="description" content="Система онлайн-бронирования залов и площадок для проведения конференций">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <div class="nav">
      <a href="index.php" class="logo">Конференции<span>.РФ</span></a>
      <input type="checkbox" id="nav-toggle" class="nav-toggle">
      <label for="nav-toggle" class="nav-toggle-label">&#9776;</label>
      <div class="nav-buttons">
        <?php if (!isset($_SESSION['user_id'])): ?>
          <a href="login.php" class="btn btn-outline">Войти</a>
          <a href="register.php" class="btn btn-primary">Регистрация</a>
        <?php elseif ($is_admin): ?>
          <a href="admin.php" class="btn btn-primary">Администратор</a>
          <a href="?logout=1" class="btn btn-exit">Выход</a>
        <?php else: ?>
          <a href="history.php" class="btn btn-outline">Мои бронирования</a>
          <a href="create.php" class="btn btn-primary">Новая заявка</a>
          <a href="?logout=1" class="btn btn-exit">Выход</a>
        <?php endif; ?>
      </div>
    </div>
</header>

<main class="main-container">
  <div class="slideshow-container">
    <div class="mySlides">
      <img src="assets/au150.png" alt="Современная аудитория">
      <div class="slide-text">Аудитория на 150 мест — полное техническое оснащение</div>
    </div>

    <div class="mySlides">
      <img src="assets/ko.jpg" alt="Коворкинг-пространство">
      <div class="slide-text">Коворкинг: гибкое пространство для воркшопов и нетворкинга</div>
    </div>

    <div class="mySlides">
      <img src="assets/kfrc.jpg" alt="Конференц-зал">
      <div class="slide-text">Конференц-зал премиум-класса с современной акустикой</div>
    </div>

    <div class="mySlides">
      <img src="assets/zal.jpg" alt="Зал для пленарных заседаний">
      <div class="slide-text">Зал заседаний: идеально для всероссийских конференций</div>
    </div>

    <button class="prev" onclick="plusSlides(-1)">❮</button>
    <button class="next" onclick="plusSlides(1)">❯</button>
  </div>

  <div class="dot-container">
    <span class="dot" onclick="currentSlide(1)"></span>
    <span class="dot" onclick="currentSlide(2)"></span>
    <span class="dot" onclick="currentSlide(3)"></span>
    <span class="dot" onclick="currentSlide(4)"></span>
  </div>

  <section class="features-section">
    <div class="features-title">
      <h1>Выберите площадку для вашей конференции</h1>
      <p>Современные залы, технологичные коворкинги и аудитории — бронируйте онлайн</p>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <div class="card-img-wrapper">
          <img src="assets/kfrc.jpg" alt="Конференц-зал">
          <div class="card-badge">до 200 мест</div>
        </div>
        <div class="card-content">
          <span class="card-type">Конференц-зал</span>
          <h3 class="card-title">Деловые мероприятия</h3>
          <p class="card-description">Проектор, акустика, трибуна, флипчарты. Идеально для научных докладов и дискуссий.</p>
          <a href="create.php" class="btn btn-primary card-action-btn">Забронировать зал</a>
        </div>
      </div>

      <div class="feature-card">
        <div class="card-img-wrapper">
          <img src="assets/ko.jpg" alt="Коворкинг">
          <div class="card-badge">до 50 мест</div>
        </div>
        <div class="card-content">
          <span class="card-type">Коворкинг</span>
          <h3 class="card-title">Свободная планировка</h3>
          <p class="card-description">Свободное пространство для работы в малых группах, воркшопов и сессий. Wi-Fi, модульная мебель.</p>
          <a href="create.php" class="btn btn-primary card-action-btn">Забронировать коворкинг</a>
        </div>
      </div>

      <div class="feature-card">
        <div class="card-img-wrapper">
          <img src="assets/au150.png" alt="Аудитория">
          <div class="card-badge">до 150 мест</div>
        </div>
        <div class="card-content">
          <span class="card-type">Аудитория</span>
          <h3 class="card-title">Обучающие сессии</h3>
          <p class="card-description">Учебное помещение на 100+ мест с маркерной доской и проектором для мастер-классов и тренингов.</p>
          <a href="create.php" class="btn btn-primary card-action-btn">Забронировать аудиторию</a>
        </div>
      </div>
    </div>
  </section>

  <div class="info-banner">
    <div>
      <p><strong>Система бронирования площадок</strong><br>Простая подача заявок, выбор свободных помещений и оперативное согласование администратором.</p>
    </div>
    <div class="badge">Удобно • Быстро • Надежно</div>
  </div>
</main>

<footer class="footer">
  © 2026 Конференции.РФ — информационная система бронирования помещений для проведения всероссийских конференций.
</footer>

<script>
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

  if (n > slides.length) { slideIndex = 1; }
  if (n < 1) { slideIndex = slides.length; }

  for (let i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (let i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }

  if (slides[slideIndex-1]) slides[slideIndex-1].style.display = "block";
  if (dots[slideIndex-1]) dots[slideIndex-1].className += " active";
}

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