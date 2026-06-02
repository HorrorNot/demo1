<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die('Чтобы забронировать помещение для конференции, необходимо войти в аккаунт.');
}

$success = false;
$error = false;
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review = $_POST['review'];
    $date = $_POST['date'];
    $venue = $_POST['venue'];
    $payment = $_POST['payment'];
    $status = 'Новая';
    
    include('db.php');
    
    $user_id = (int)$_SESSION['user_id'];
    $review = $con->real_escape_string($review);
    $venue = $con->real_escape_string($venue);
    $payment = $con->real_escape_string($payment);
    
    $formatted_date = date('Y-m-d H:i:s', strtotime($date));
    
    $query = $con->query("INSERT INTO request (review, date, curses, payment, user_id, status) 
                          VALUES ('$review', '$formatted_date', '$venue', '$payment', '$user_id', '$status')");
    
    if (!$query) {
        $error = true;
        $error_msg = 'Ошибка базы данных: ' . $con->error;
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
    <title>Бронирование помещения — Конференции.РФ</title>
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
            <a href="history.php" class="btn btn-outline">Мои бронирования</a>
            <a href="index.php?logout=1" class="btn btn-exit">Выход</a>
          </div>
        </div>
    </header>

    <div class="main-container" style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 150px);">
        <div class="form-container" style="max-width: 580px; margin: 24px auto;">

        <div class="form-title">
            <h2>Бронирование помещения</h2>
            <p>Заполните форму для выбора площадки проведения мероприятия</p>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                Заявка успешно отправлена на согласование!<br><br>
                <a href="history.php" class="btn btn-primary" style="font-size: 14px;">Перейти к бронированиям</a>
            </div>
        <?php elseif ($error): ?>
            <div class="error-message">
                Ошибка при отправке: <?php echo htmlspecialchars($error_msg); ?><br>
                <a href="javascript:history.back()" style="color: inherit; font-weight: bold;">◀ Назад</a>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" id="requestForm">
            
            <div class="form-group">
                <label style="margin-bottom: 12px;">Выберите помещение</label>
                <div class="room-selector-deck">
                    <label class="room-select-card">
                        <input type="radio" name="venue" value="Аудитория" checked required>
                        <div class="select-card-inner">
                            <h4>Аудитория</h4>
                            <p>до 150 мест, проектор, трибуна для выступлений</p>
                        </div>
                        <div class="select-card-border"></div>
                    </label>

                    <label class="room-select-card">
                        <input type="radio" name="venue" value="Коворкинг" required>
                        <div class="select-card-inner">
                            <h4>Коворкинг</h4>
                            <p>до 50 мест, Wi-Fi 6, модульная планировка</p>
                        </div>
                        <div class="select-card-border"></div>
                    </label>

                    <label class="room-select-card">
                        <input type="radio" name="venue" value="Кинозал" required>
                        <div class="select-card-inner">
                            <h4>Кинозал</h4>
                            <p>до 80 мест, панорамный экран, акустика</p>
                        </div>
                        <div class="select-card-border"></div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="date">Дата и время начала</label>
                <input id="date" type="datetime-local" name="date" required>
                <span class="hint">Укажите в формате: ДД.ММ.ГГГГ ЧЧ:ММ</span>
            </div>

            <div class="form-group">
                <label style="margin-bottom: 10px;">Способ оплаты</label>
                <div class="segmented-control">
                    <div class="segment-item">
                        <input type="radio" id="pay_cash" name="payment" value="наличные" checked required>
                        <label for="pay_cash" class="segment-label">Наличные в кассу</label>
                    </div>
                    <div class="segment-item">
                        <input type="radio" id="pay_bank" name="payment" value="перевод" required>
                        <label for="pay_bank" class="segment-label">Безналичный перевод</label>
                    </div>
                    <div class="segment-item">
                        <input type="radio" id="pay_card" name="payment" value="карта" required>
                        <label for="pay_card" class="segment-label">Банковская карта</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="review">Особые требования</label>
                <textarea id="review" name="review" placeholder="Количество участников, флипчарты, микрофоны или особые пожелания..." required></textarea>
            </div>
             
            <button type="submit" class="btn btn-primary" style="width: 100%;" id="submitBtn">
                Отправить заявку
            </button>
        </form>
        <?php endif; ?>
    </div>
    </div>

    <script>
        const form = document.getElementById('requestForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = 'Отправка заявки...';
                submitBtn.disabled = true;
            });
        }

        const dateInput = document.getElementById('date');
        if (dateInput) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            dateInput.min = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    </script>
</body>
</html>