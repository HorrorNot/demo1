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
    
    $errors = [];
    
    if (empty($login)) {
        $errors[] = 'Логин обязателен';
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors[] = 'Логин: только латиница и цифры, от 6 символов';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Пароль должен быть от 8 символов';
    }
    
    if (empty($fullname)) {
        $errors[] = 'ФИО обязательно';
    } elseif (strlen($fullname) < 5) {
        $errors[] = 'Введите полное ФИО';
    }
    
    if (empty($phone)) {
        $errors[] = 'Телефон обязателен';
    } elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors[] = 'Телефон должен быть в формате +7(XXX)XXX-XX-XX';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }
    
    if (empty($errors)) {
        $stmt = $con->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = true;
            $error_message = 'Пользователь с таким логином уже существует';
            $stmt->close();
        } else {
            $stmt->close();
            
            $stmt2 = $con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            if ($result2->num_rows > 0) {
                $error = true;
                $error_message = 'Пользователь с таким email уже существует';
                $stmt2->close();
            } else {
                $stmt2->close();
                
                $stmt3 = $con->prepare("INSERT INTO users (login, password, fullname, phone, email) VALUES (?, ?, ?, ?, ?)");
                $stmt3->bind_param("sssss", $login, $password, $fullname, $phone, $email);
                
                if ($stmt3->execute()) {
                    $success = true;
                    header('refresh:2;url=login.php');
                } else {
                    $error = true;
                    $error_message = 'Ошибка регистрации: ' . $con->error;
                }
                $stmt3->close();
            }
        }
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
    <title>Регистрация — Конференции.РФ</title>
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
            <a href="login.php" class="btn btn-outline">Войти</a>
            <a href="register.php" class="btn btn-primary">Регистрация</a>
          </div>
        </div>
    </header>

    <div class="main-container" style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 150px); padding: 24px 16px;">
        <div class="form-container" style="max-width: 540px; margin: 0 auto;">
            <div class="form-title">
                <h2>Конференции.РФ</h2>
                <p>Бронирование помещений для всероссийских конференций</p>
            </div>

        <div class="form-title">
            <h3>Регистрация организатора</h3>
            <p>Создайте аккаунт, чтобы бронировать площадки</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                Ошибка: <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                Регистрация успешно завершена!<br>
                <small>Сейчас вы будете перенаправлены на страницу входа...</small>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="fullname">ФИО</label>
                <input type="text" id="fullname" name="fullname"
                       value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>"
                       placeholder="Иванов Иван Иванович" required>
                <span class="hint" id="fullnameHint">Введите ваше полное ФИО</span>
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone"
                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                       placeholder="+7(XXX)XXX-XX-XX"
                       pattern="\+7\(\d{3}\)\d{3}-\d{2}-\d{2}" required>
                <span class="hint" id="phoneHint">Формат: +7(XXX)XXX-XX-XX</span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                       placeholder="example@mail.ru" required>
                <span class="hint" id="emailHint">На данный адрес придет бронирование</span>
            </div>

            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login"
                       value="<?php echo htmlspecialchars($form_data['login'] ?? ''); ?>"
                       placeholder="Не менее 6 символов"
                       pattern="[a-zA-Z0-9]{6,}" required>
                <span class="hint" id="loginHint">Только латинские буквы и цифры</span>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password"
                       placeholder="Не менее 8 символов" minlength="8" required>
                <span class="hint" id="passwordHint">Пароль должен быть от 8 символов</span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Подтверждение</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Повторите пароль" required>
                <span class="hint" id="confirmHint">Пароли должны совпадать</span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;" id="submitBtn">
                Зарегистрироваться
            </button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            <p>Уже зарегистрированы? <a href="login.php">Войти в кабинет →</a></p>
            <a href="index.php" style="display: inline-block; margin-top: 10px; color: var(--gray-mid); font-size: 13px;">← На главную</a>
        </div>
    </div>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const confirmHint = document.getElementById('confirmHint');
        const passwordHint = document.getElementById('passwordHint');
        const phone = document.getElementById('phone');
        const phoneHint = document.getElementById('phoneHint');
        const login = document.getElementById('login');
        const loginHint = document.getElementById('loginHint');
        const submitBtn = document.getElementById('submitBtn');
        
        if (password) {
            password.addEventListener('input', function() {
                if (this.value.length >= 8) {
                    passwordHint.textContent = 'Пароль надежен';
                    passwordHint.style.color = '#28A745';
                } else {
                    passwordHint.textContent = 'Не менее 8 символов';
                    passwordHint.style.color = '#dc3545';
                }
                if (confirmPassword.value) checkMatch();
            });
        }
        
        function checkMatch() {
            if (password.value === confirmPassword.value && password.value.length >= 8) {
                confirmHint.textContent = 'Пароли совпадают';
                confirmHint.style.color = '#28A745';
                return true;
            } else {
                confirmHint.textContent = 'Пароли не совпадают';
                confirmHint.style.color = '#dc3545';
                return false;
            }
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', checkMatch);
        }
        
        if (phone) {
            phone.addEventListener('input', function() {
                const pattern = /^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
                if (pattern.test(this.value)) {
                    phoneHint.textContent = 'Верный формат';
                    phoneHint.style.color = '#28A745';
                } else {
                    phoneHint.textContent = 'Формат +7(XXX)XXX-XX-XX';
                    phoneHint.style.color = '#dc3545';
                }
            });
        }

        if (login) {
            login.addEventListener('input', function() {
                const pattern = /^[a-zA-Z0-9]{6,}$/;
                if (pattern.test(this.value)) {
                    loginHint.textContent = 'Логин корректен';
                    loginHint.style.color = '#28A745';
                } else {
                    loginHint.textContent = 'Латиница и цифры, от 6 символов';
                    loginHint.style.color = '#dc3545';
                }
            });
        }
        
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!checkMatch()) {
                    e.preventDefault();
                    return false;
                }
                submitBtn.innerHTML = 'Регистрация...';
                submitBtn.disabled = true;
            });
        }
    </script>
</body>
</html>