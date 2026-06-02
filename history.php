<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    die('Чтобы посмотреть историю бронирований, необходимо войти в аккаунт.');
}
include('db.php');

$review_saved = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int)$_SESSION['user_id'];
    $request_id = (int)$_POST['request_id'];
    $con->query("UPDATE request SET review='$review' WHERE id='$request_id' AND user_id='$user_id'");
    $review_saved = true;
}

$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if (!$query) {
    die('Ошибка запроса: ' . $con->error); 
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои бронирования — Конференции.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="header">
      <div class="nav">
        <a href="index.php" class="logo">Конференции<span>.РФ</span></a>
        <div class="nav-buttons">
          <a href="index.php" class="btn btn-outline">На главную</a>
          <a href="create.php" class="btn btn-primary">Новое бронирование</a>
          <a href="index.php?logout=1" class="btn btn-exit">Выход</a>
        </div>
      </div>
    </header>

    <div class="history-container">

        <h1 style="text-align: center; margin: 30px 0 10px;">Личный кабинет организатора</h1>
        <p style="text-align: center; color: var(--gray-mid); margin-bottom: 30px;">Просмотр статуса поданных заявок и управление отзывами</p>

        <?php if ($review_saved): ?>
            <div class="success-message">
                Ваш отзыв о мероприятии успешно сохранен! Спасибо за обратную связь.
            </div>
        <?php endif; ?>

        <div class="slideshow-container">
          <div class="mySlides">
            <img src="assets/kfrc.jpg" alt="Презентация">
            <div class="slide-text">Проводите всероссийские научные конференции на высшем уровне</div>
          </div>

          <div class="mySlides">
            <img src="assets/mu.jpg" alt="Лекция">
            <div class="slide-text">Современные мультимедийные решения для ваших выступлений</div>
          </div>

          <div class="mySlides">
            <img src="assets/ko.jpg" alt="Обсуждение">
            <div class="slide-text">Круглые столы и бизнес-встречи в комфортных коворкингах</div>
          </div>

          <div class="mySlides">
            <img src="assets/zal.jpg" alt="Президиум">
            <div class="slide-text">Полный спектр услуг сопровождения вашего бронирования</div>
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

        <div style="margin-top: 40px;">
            <h2 style="margin-bottom: 20px;">Мои поданные заявки</h2>
            
            <?php
            if ($query->num_rows == 0) {
                echo '<div class="request-card" style="text-align: center; padding: 40px 20px; color: var(--gray-mid);">
                        У вас пока нет бронирований.<br><br>
                        <a href="create.php" class="btn btn-primary">Забронировать помещение</a>
                      </div>';
            }
            while ($request = $query->fetch_assoc()) {
                $status = htmlspecialchars($request['status']);
                
                $tag_class = 'new';
                if ($status === 'Мероприятие назначено') $tag_class = 'assigned';
                elseif ($status === 'Мероприятие завершено') $tag_class = 'completed';
                
                $venue = htmlspecialchars($request['curses']);
                
                echo '
                <div class="request-card">
                    <div class="request-card-header">
                        <h3>Заявка на бронирование #' . $request['id'] . '</h3>
                        <span class="status-tag ' . $tag_class . '">' . $status . '</span>
                    </div>
                    <div class="request-card-body">
                        <p><b>Дата и время:</b> ' . htmlspecialchars($request['date']) . '</p>
                        <p><b>Площадка:</b> ' . $venue . '</p>
                        <p><b>Оплата:</b> ' . htmlspecialchars($request['payment']) . '</p>';
                
                if (!empty($request['review'])) {
                    echo '<div class="review-display"><b>Ваш комментарий/отзыв:</b> ' . htmlspecialchars($request['review']) . '</div>';
                }
                
                if ($status !== 'Новая') {
                    echo '
                    <form action="" method="POST" class="review-input-group">
                        <input type="hidden" name="request_id" value="' . $request['id'] . '">
                        <input type="text" name="review" placeholder="Оставьте отзыв о качестве обслуживания..." value="' . htmlspecialchars($request['review'] ?? '') . '" required>
                        <button type="submit" class="btn btn-outline">Сохранить</button>
                    </form>';
                } else {
                    echo '<p style="font-size: 13px; color: var(--gray-mid); margin-top: 10px; font-style: italic; opacity: 0.85;">
                            * Возможность оставить отзыв появится после подтверждения заявки администратором
                          </p>';
                }
                
                echo '</div></div>';
            }
            ?>
        </div>
    </div>

    <footer class="footer" style="margin-top: 50px;">
      © 2026 Конференции.РФ — личный кабинет организатора.
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