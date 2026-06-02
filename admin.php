<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$valid_statuses = ['Новая', 'Мероприятие назначено', 'Мероприятие завершено'];
$status_updated = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';

    if (in_array($status, $valid_statuses, true)) {
        $stmt = $con->prepare("UPDATE request SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $request_id);
        if ($stmt->execute()) {
            $status_updated = true;
        }
        $stmt->close();
    }
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'Все';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'DESC';

$where_clause = "";
if (in_array($filter, $valid_statuses, true)) {
    $where_clause = " WHERE request.status = '" . $con->real_escape_string($filter) . "' ";
}

$order_dir = ($sort === 'ASC') ? 'ASC' : 'DESC';

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$count_query = $con->query("SELECT COUNT(*) as total FROM request" . ($where_clause ? " INNER JOIN users ON request.user_id = users.id " . $where_clause : ""));
$count_res = $count_query->fetch_assoc();
$total_records = $count_res['total'];
$total_pages = ceil($total_records / $limit);

$query = $con->query("
    SELECT request.*, users.login, users.fullname
    FROM request
    INNER JOIN users ON request.user_id = users.id
    $where_clause
    ORDER BY request.date $order_dir
    LIMIT $limit OFFSET $offset
");

$stats_query = $con->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new_requests,
        SUM(CASE WHEN status = 'Мероприятие назначено' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status = 'Мероприятие завершено' THEN 1 ELSE 0 END) as completed
    FROM request
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора — Конференции.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="header">
      <div class="nav">
        <a href="index.php" class="logo">Панель <span>Администратора</span></a>
        <div class="nav-buttons">
          <a href="index.php" class="btn btn-outline">На сайт</a>
          <a href="?logout=1" class="btn btn-exit" onclick="return confirm('Выйти?')">Выход</a>
        </div>
      </div>
    </header>

    <div class="admin-workspace-split">
      <!-- Left Side: Stats & Filter Sidebar Drawer -->
      <aside class="admin-sidebar">
        <div class="sidebar-header">
          <div class="admin-badge">
            <div class="avatar-ring">A</div>
            <div>
              <h4>Администратор</h4>
              <p>Система управления</p>
            </div>
          </div>
        </div>

        <!-- Stats deck -->
        <div class="sidebar-stats-deck">
          <div class="sidebar-stat-item">
            <span class="s-val"><?= (int)$stats['total'] ?></span>
            <span class="s-lbl">Всего заявок</span>
          </div>
          <div class="sidebar-stat-item status-new">
            <span class="s-val" style="color: var(--primary);"><?= (int)$stats['new_requests'] ?></span>
            <span class="s-lbl">Новые</span>
          </div>
          <div class="sidebar-stat-item status-assigned">
            <span class="s-val" style="color: #55c970;"><?= (int)$stats['assigned'] ?></span>
            <span class="s-lbl">Назначенные</span>
          </div>
          <div class="sidebar-stat-item status-completed">
            <span class="s-val" style="color: #ced4da;"><?= (int)$stats['completed'] ?></span>
            <span class="s-lbl">Завершенные</span>
          </div>
        </div>

        <!-- Interactive sidebar filter form -->
        <div class="sidebar-filter-section">
          <h5>Фильтрация и поиск</h5>
          <form method="GET" action="" class="sidebar-filter-form">
            <div class="filter-group">
              <label>Статус заявки</label>
              <select name="filter" onchange="this.form.submit()">
                <option value="Все" <?= $filter === 'Все' ? 'selected' : '' ?>>Все статусы</option>
                <option value="Новая" <?= $filter === 'Новая' ? 'selected' : '' ?>>Новые</option>
                <option value="Мероприятие назначено" <?= $filter === 'Мероприятие назначено' ? 'selected' : '' ?>>Назначенные</option>
                <option value="Мероприятие завершено" <?= $filter === 'Мероприятие завершено' ? 'selected' : '' ?>>Завершенные</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Сортировка по дате</label>
              <select name="sort" onchange="this.form.submit()">
                <option value="DESC" <?= $sort === 'DESC' ? 'selected' : '' ?>>Сначала новые</option>
                <option value="ASC" <?= $sort === 'ASC' ? 'selected' : '' ?>>Сначала старые</option>
              </select>
            </div>
          </form>
        </div>
      </aside>

      <!-- Right Side: Stream of Request Cards -->
      <main class="admin-stream-workspace">
        <div class="workspace-header">
          <div>
            <h2>Журнал заявок на бронирование</h2>
            <p>Оперативная диспетчеризация залов и согласование проведения конференций</p>
          </div>
          <div class="stream-meta">Найдено записей: <b><?= $total_records ?></b></div>
        </div>

        <?php if ($status_updated): ?>
            <div class="success-message" id="notif" style="text-align: center; margin-bottom: 24px;">
                Статус заявки успешно изменен и сохранен!
            </div>
        <?php endif; ?>

        <div class="admin-list">
            <?php
            if ($query->num_rows === 0) {
                echo '<div class="empty-stream-card">Заявки с указанными критериями отсутствуют</div>';
            } else {
                while ($request = $query->fetch_assoc()) {
                    $status = htmlspecialchars($request['status']);
                    $tag_class = 'new';
                    if ($status === 'Мероприятие назначено') $tag_class = 'assigned';
                    elseif ($status === 'Мероприятие завершено') $tag_class = 'completed';
                    
                    $venue = htmlspecialchars($request['curses']);
            ?>
                <div class="admin-request-card">
                    <div class="admin-req-header">
                        <div>
                            <b>Пользователь:</b> <?= htmlspecialchars($request['login']) ?> 
                            <span style="color: var(--gray-mid); font-size: 13px;">(<?= htmlspecialchars($request['fullname']) ?>)</span>
                        </div>
                        <div>
                            <span style="font-size: 13px; color: var(--gray-mid); margin-right: 10px;">Заявка #<?= $request['id'] ?></span>
                            <span class="status-tag <?= $tag_class ?>"><?= $status ?></span>
                        </div>
                    </div>

                    <div class="admin-req-grid">
                        <div class="admin-req-detail">
                            <small>Дата и время</small>
                            <div><?= htmlspecialchars($request['date']) ?></div>
                        </div>
                        <div class="admin-req-detail">
                            <small>Помещение</small>
                            <div><?= $venue ?></div>
                        </div>
                        <div class="admin-req-detail">
                            <small>Способ оплаты</small>
                            <div><?= htmlspecialchars($request['payment'] ?? '—') ?></div>
                        </div>
                        <div class="admin-req-detail" style="grid-column: span 2;">
                            <small>Комментарий / Пожелания / Отзыв</small>
                            <div><?= htmlspecialchars($request['review'] ? $request['review'] : '—') ?></div>
                        </div>
                    </div>

                    <form method="POST" action="" class="admin-status-control-deck">
                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                        <label>Сменить статус:</label>
                        <div class="admin-action-btn-group">
                            <button type="submit" name="status" value="Новая" class="status-btn btn-new <?= $status === 'Новая' ? 'active' : '' ?>">Новая</button>
                            <button type="submit" name="status" value="Мероприятие назначено" class="status-btn btn-assigned <?= $status === 'Мероприятие назначено' ? 'active' : '' ?>">Назначить</button>
                            <button type="submit" name="status" value="Мероприятие завершено" class="status-btn btn-completed <?= $status === 'Мероприятие завершено' ? 'active' : '' ?>">Завершить</button>
                        </div>
                    </form>
                </div>
            <?php
                }
            }
            ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&filter=<?= urlencode($filter) ?>&sort=<?= urlencode($sort) ?>"
                       class="page-item <?= $page === $i ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
      </main>
    </div>

    <footer class="footer">
      © 2026 Конференции.РФ — панель администратора портала.
    </footer>

    <script>
        const notif = document.getElementById('notif');
        if (notif) {
            setTimeout(() => {
                notif.style.transition = 'opacity 0.5s ease';
                notif.style.opacity = '0';
                setTimeout(() => notif.remove(), 500);
            }, 3000);
        }
    </script>
</body>
</html>