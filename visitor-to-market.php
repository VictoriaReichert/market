<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление посетителя без записи</title>
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
        date_default_timezone_set('Europe/Moscow');

        // Проверяем права доступа
        if (!isset($_SESSION['currentRole']) || ($_SESSION['currentRole'] != 'volunteer' && $_SESSION['currentRole'] != 'manager')) {
            header('Location: index.php');
            exit();
        }

        // Получаем информацию о ярмарке
        $marketName = $_SESSION['currentMarket'];
        $marketDb = $_SESSION['currentMarketDB'];

        // Получаем доступные даты из таблицы market-days
        mysqli_query($connection, "USE `$marketDb`");
        $daysQuery = mysqli_query($connection, "SELECT * FROM `market-days` ORDER BY date");
        $availableDays = [];
        while ($day = mysqli_fetch_assoc($daysQuery)) {
            $availableDays[] = $day;
        }

        // Обработка формы добавления посетителя
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_visitor'])) {
            $visitDate = $_POST['visit_date'];
            
            // Добавляем посетителя без записи
            mysqli_query($connection, 
                "INSERT INTO `visitors` 
                (`user`, `email`, `date`, `status`)
                VALUES
                ('visitor', '-', '$visitDate', 'Посетил')");

            // Уменьшаем вместимость на 1
            mysqli_query($connection, 
                "UPDATE `market-days` 
                SET capacity = capacity - 1 
                WHERE date = '$visitDate'");
            
            $_SESSION['visitor_added'] = true;
            header("Location: market-visitors.php?name=".$_GET['name']);
            exit();
        }

        include 'header.php';
    ?>

    <main class="visit-market">
        <h1>Добавление посетителя без записи на <?= htmlspecialchars($marketName) ?></h1>
        
        <?php if (isset($_SESSION['visitor_added'])): ?>
            <div class="success-message">Посетитель без записи успешно добавлен!</div>
            <?php unset($_SESSION['visitor_added']); ?>
        <?php endif; ?>

        <?php if (!empty($availableDays)): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="visit_date">Выберите дату посещения:</label>
                    <select id="visit_date" name="visit_date" required>
                        <option value="">-- Выберите дату --</option>
                        <?php foreach ($availableDays as $day): ?>
                            <option value="<?= $day['date'] ?>">
                                <?= date('d.m.Y', strtotime($day['date'])) ?> 
                                (Вместимость: <?= $day['capacity'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="add_visitor" class="submit-btn">Добавить посетителя</button>
                <a href="market-visitors.php?name=<?= $_GET['name']?>" class="back-link">Назад к списку посетителей</a>
            </form>
        <?php else: ?>
            <div class="no-dates">
                <p>Нет доступных дат для посещения.</p>
                <a href="market-visitors.php?name=<?= $_GET['name']?>" class="back-link">Назад к списку посетителей</a>
            </div>
        <?php endif; ?>
    </main>
    <script src='scripts\functions.js'></script>
</body>
</html>
