<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список посетителей</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/visitors-market.css">
</head>
<body>
    <?php
        session_start();
        include 'scripts/checkAuth.php';
        include 'scripts/connectToDB.php';
        include 'scripts/getLogin.php';
        date_default_timezone_set('Europe/Moscow');

        // Проверяем, что пользователь имеет права волонтера или организатора
        // if (!isset($_SESSION['currentRole']) || ($_SESSION['currentRole'] != 'volunteer' && $_SESSION['currentRole'] != 'manager')) {
        //     header('Location: index.php');
        //     exit();
        // }
        function lowerCase($str) {
            $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
            $translit = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
            return str_replace($russian, $translit, $str);
        }
        function translit($str) {
            $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
            $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
            return str_replace($russian, $translit, $str);
        }
        // Получаем информацию о ярмарке
        $marketName = $_GET['name'];
        $marketDb = "market-manager-db_".translit(lowerCase($marketName));
        mysqli_query($connection, "USE `$marketDb`");

        // Обработка изменения статуса посетителя
        if (isset($_POST['change_status'])) {
            $email = $_POST['email'];
            $date = $_POST['date'];
            mysqli_query($connection, 
                "UPDATE visitors 
                SET status = 'Посетил' 
                WHERE email = '$email' AND date = '$date'");
        }

        // Обработка завершения ярмарки
        if (isset($_POST['finish_market'])) {
            mysqli_query($connection, 
                "UPDATE visitors 
                SET status = 'Посещал' 
                WHERE status = 'Посетил'");

            mysqli_query($connection, 
                "UPDATE visitors 
                SET status = 'Не пришёл' 
                WHERE status = 'Планирует прийти'");


            $_SESSION['market_finished'] = true;
            header("Location: visitors-market.php");
            exit();
        }

        // Получаем параметры фильтрации
        $filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
        $filterDate = isset($_GET['date']) ? $_GET['date'] : '';

        // Формируем SQL запрос с учетом фильтров
        $whereClause = "WHERE status != 'Посещал' AND status != 'Не пришёл'";
        if ($filterStatus) {
            $whereClause .= " AND status = '$filterStatus'";
        }
        if ($filterDate) {
            $whereClause .= " AND date = '$filterDate'";
        }

        // Получаем список посетителей
        $visitorsQuery = mysqli_query($connection, 
            "SELECT * FROM visitors 
            $whereClause 
            ORDER BY date, user, email");

        // Получаем список доступных дат из market-days
        $daysQuery = mysqli_query($connection, "SELECT * FROM `market-days` ORDER BY date");
        $availableDates = [];
        while ($day = mysqli_fetch_assoc($daysQuery)) {
            $availableDates[] = $day;
        }

        // Получаем список уникальных статусов
        $statusesQuery = mysqli_query($connection, 
            "SELECT DISTINCT status FROM visitors 
            WHERE status != 'Посещал' AND status != 'Не пришёл'");
        $availableStatuses = [];
        while ($status = mysqli_fetch_assoc($statusesQuery)) {
            $availableStatuses[] = $status['status'];
        }


        // Проверяем, можно ли отображать кнопку завершения ярмарки
        $showFinishButton = false;
        $marketInfoQuery = mysqli_query($connection, "SELECT closingDate, closingTime FROM `market-info`");
        if ($marketInfo = mysqli_fetch_assoc($marketInfoQuery)) {
            $currentDateTime = new DateTime();
            $closingDateTime = new DateTime($marketInfo['closingDate'] . ' ' . $marketInfo['closingTime']);
            
            if ($currentDateTime > $closingDateTime) {
                $showFinishButton = true;
            }
        }


        include 'header.php'; 
    ?>

    <main class="visitors-container">
        <h1>Список посетителей ярмарки <?php echo $marketName;?></h1>
        
        <?php if (isset($_SESSION['market_finished'])): ?>
            <div class="success-message">Ярмарка успешно завершена! Все посетители отмечены как "Посещал".</div>
            <?php unset($_SESSION['market_finished']); ?>
        <?php endif; ?>

        <!-- Фильтры -->
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-group">
                    <label for="status">Статус:</label>
                    <select id="status" name="status">
                        <option value="">Все статусы</option>
                        <?php foreach ($availableStatuses as $status): ?>
                            <option value="<?= $status ?>" <?= $filterStatus == $status ? 'selected' : '' ?>>
                                <?= $status ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="date">Дата:</label>
                    <select id="date" name="date">
                        <option value="">Все даты</option>
                        <?php foreach ($availableDates as $day): ?>
                            <option value="<?= $day['date'] ?>" <?= $filterDate == $day['date'] ? 'selected' : '' ?>>
                                <?= date('d.m.Y', strtotime($day['date'])) ?> 
                                (Вместимость: <?= $day['capacity'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="action-btn">Применить фильтры</button>
                <a href="market-visitors.php" class="action-btn">Сбросить фильтры</a>
                <?php if ($_SESSION['currentRole'] == 'volunteer'): ?>
                    <a href="visitor-to-market.php?name=<?= $_GET['name']?>" class="action-btn">Добавить посетителя без записи</a>
                <?php endif; ?>
                <a href="market-page.php?name=<?= urlencode($marketName) ?>" class="action-btn">Назад</a>
            </form>
        </div>

        <!-- Таблица посетителей -->
        <div class="visitors-table">
            <?php if (mysqli_num_rows($visitorsQuery) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Тип</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Дата посещения</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($visitor = mysqli_fetch_assoc($visitorsQuery)): 
                            $userName = mysqli_fetch_assoc(mysqli_query($connection, "SELECT `login` FROM market.users WHERE email = '".$visitor['email']."'"));
                            ?>
                            <tr>
                                <td><?= $visitor['user'] == 'user' ? 'По записи' : 'Без записи' ?></td>
                                <td><?= isset($userName) ? $userName['login'] : '-' ?></td>
                                <td><?= htmlspecialchars($visitor['email']) ?></td>
                                <td><?= date('d.m.Y', strtotime($visitor['date'])) ?></td>
                                <td><?= htmlspecialchars($visitor['status']) ?></td>
                                <td>
                                    <?php if ($visitor['status'] != 'Посетил' && $visitor['status'] != 'Посещал'): ?>
                                        <form method="POST" action="">
                                            <input type="hidden" name="email" value="<?= $visitor['email'] ?>">
                                            <input type="hidden" name="date" value="<?= $visitor['date'] ?>">
                                            <?php if ($showFinishButton && $_SESSION['currentRole'] == 'vollunteer'): ?>
                                                <button type="submit" name="change_status" class="mark-visited-btn">
                                                    Посетил
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-visitors">Нет посетителей, соответствующих выбранным фильтрам.</p>
            <?php endif; ?>
        </div>

        <!-- Кнопка завершения ярмарки очень плохо с сессией, надо как-то с формой, но уже мало времени -->
        <?php if ($showFinishButton && $_SESSION['currentRole'] == 'manager'): ?>
            <div class="finish-market">
                <form method="POST" action="">
                    <button type="submit" name="finish_market" class="finish-btn" 
                        onclick="return confirm('Вы уверены, что хотите завершить ярмарку? Все посетители будут отмечены статусом -Посещал-.')">
                        Завершить ярмарку
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </main>
    <script src='scripts\functions.js'></script>
</body>
</html>
