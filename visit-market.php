<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись на <?= htmlspecialchars($_GET['name']) ?></title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/visit-market.css">
</head>
<body>
    <?php
        session_start();
        include 'scripts/checkAuth.php';
        include 'scripts/connectToDB.php';
        include 'scripts/getLogin.php';

        // Получаем информацию о ярмарке
        $marketName = $_GET['name'];
        $marketDb = $_SESSION['currentMarketDB'];
        $email = $_COOKIE['logIn'];

        // Получаем доступные даты из таблицы market-days
        mysqli_query($connection, "USE `$marketDb`");
        $daysQuery = mysqli_query($connection, "SELECT * FROM `market-days` WHERE capacity > 0 ORDER BY date");
        $availableDays = [];
        while ($day = mysqli_fetch_assoc($daysQuery)) {
            $availableDays[] = $day;
        }

        // Обработка формы выбора даты----------------------------------
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['visit_date'])) {
            $visitDate = $_POST['visit_date'];
            
            // Проверяем, что дата доступна
            $checkDay = mysqli_query($connection, "SELECT * FROM `market-days` WHERE date = '$visitDate' AND capacity > 0");
            if (mysqli_num_rows($checkDay) > 0) {
                // Добавляем посетителя
                mysqli_query($connection, 
                    "INSERT INTO `visitors` 
                    (`user`, `email`, `date`, `status`)
                    VALUES
                    ('user', '$email', '$visitDate', 'Планирует прийти')");
                
                // Уменьшаем вместимость на 1
                mysqli_query($connection, 
                    "UPDATE `market-days` 
                    SET capacity = capacity - 1 
                    WHERE date = '$visitDate'");
                
                $_SESSION['visit_success'] = true;
                header("Location: market-page.php?name=$marketName");
                exit();
            }
        }
        include 'header.php';
    ?>


    <main class="visit-market">
        <h1>Запись на "<?= htmlspecialchars($marketName) ?>"</h1>
        
        <?php if (!empty($availableDays)): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="visit_date">Выберите дату посещения:</label>
                    <select id="visit_date" name="visit_date" required>
                        <option value="">-- Выберите дату --</option>
                        <?php foreach ($availableDays as $day): ?>
                            <option value="<?= $day['date'] ?>">
                                <?= date('d.m.Y', strtotime($day['date'])) ?> 
                                (Осталось мест: <?= $day['capacity'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <p>Ваши данные для записи:</p>
                    <ul>
                        <li>Имя: <?= htmlspecialchars($userData['login']) ?></li>
                        <li>Email: <?= htmlspecialchars($email) ?></li>
                    </ul>
                </div>
                
                <button type="submit" class="submit-btn">Подтвердить запись</button>
                <a href="market-page.php?name=<?= urlencode($marketName) ?>" class="back-link">Вернуться на страницу ярмарки</a>
            </form>
        <?php else: ?>
            <div class="no-dates">
                <p>К сожалению, на данный момент нет доступных дат для посещения.</p>
                <a href="market-page.php?name=<?= urlencode($marketName) ?>" class="back-link">Вернуться на страницу ярмарки</a>
            </div>
        <?php endif; ?>
    </main>
    <script src='scripts\functions.js'></script>
</body>
</html>