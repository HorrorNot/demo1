<?php
session_start();
include('db.php');

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
$error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if (empty($login) || empty($password)) {
        $error = true;
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        $stmt = $con->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = true;
            $error_message = 'Неверный логин или пароль';
        } else {
            $user = $result->fetch_assoc();
            $password_valid = false;
            
            if (password_verify($password, $user['password'])) {
                $password_valid = true;
            } elseif ($password === $user['password']) {
                $password_valid = true;
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed, $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            if (!$password_valid) {
                $error = true;
                $error_message = 'Неверный логин или пароль';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_fullname'] = $user['fullname'];
                
                if ($user['login'] == 'Admin26') {
                    $_SESSION['admin'] = true;
                    header('Location: admin.php');
                } else {
                    header('Location: create.php');
                }
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Конференции.РФ</title>
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
    <div class="main-container" style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 150px); padding: 24px 16px;">
        <div class="form-container" style="max-width: 500px; margin: 0 auto;">
            <div class="form-title">
                <h2>Конференции.РФ</h2>
                <p>Бронирование помещений для всероссийских конференций</p>
            </div>

        <div class="form-title">
            <h3>Вход в систему</h3>
            <p>Войдите, чтобы забронировать аудиторию, коворкинг или кинозал</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                Ошибка: <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login"
                       value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>"
                       placeholder="Ваш логин" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password"
                       placeholder="Введите пароль" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;" id="submitBtn">
                Войти в кабинет
            </button>
        </form>

        <div class="form-footer">
            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться →</a></p>
            <a href="index.php" style="display: inline-block; margin-top: 10px; color: var(--gray-mid); font-size: 13px;">← На главную</a>
        </div>
    </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = 'Вход...';
                submitBtn.disabled = true;
            });
        }
    </script>
</body>
</html>