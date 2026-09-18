<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_GET['name']; ?></title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/market-page.css">
</head>
<body>
    <?php
    session_start();
    include 'scripts/checkAuth.php'; 
    include 'scripts/connectToDB.php';
    include 'scripts/getLogin.php';
    
    // Получаем список ярмарок, на которых текущий пользватель является организатором
    $sort = 'id'; $showPast = false;
    include 'scripts\getManagedMarkets.php';


    // Почта организатора этой ярмарки
    $marketName = $_GET['name'];
    $_SESSION['currentMarket'] = $marketName;
    $query = mysqli_fetch_assoc(mysqli_query($connection, "SELECT managerEmail FROM $tableName WHERE `name` = '$marketName'"));
    $managerEmail = $query['managerEmail']; 

    // Получаем информацию о ярмарке
    $result2 = mysqli_query($connection, "SELECT `database-name` FROM $tableName WHERE name = '$marketName'");
    $row2 = mysqli_fetch_assoc($result2);
    $marketDb = $row2['database-name'];
    $_SESSION['currentMarketDB'] = $marketDb;

    include 'scripts/handleReviews.php';

    // Обработка успешной записи--------------------------------------------------------------------------------------------------
    if (isset($_SESSION['visit_success'])) {
        echo '<div class="success-message">Вы успешно записаны на ярмарку!</div>';
        unset($_SESSION['visit_success']);
    }

    // Получаем все возможные роли пользователя
    $result1 = mysqli_query($connection, "SELECT role FROM `user-roles` WHERE email = '$email'");
    mysqli_query($connection, "USE `".$marketDb."`");
    $seller = mysqli_query($connection, "SELECT email FROM `market-members` WHERE email = '$email' AND `role` = 'seller' AND `status` = 'Участвует'");
    $vol = mysqli_query($connection, "SELECT email FROM `market-members` WHERE email = '$email' AND `role` = 'volunteer' AND `status` = 'Участвует'");
    mysqli_query($connection, "USE `market`");


    while ($row = mysqli_fetch_assoc($result1)) {
        $role = $row['role'];
        if ($role == 'visitor') $userRoles['visitor'] = 1;
        if ($role == 'seller' && mysqli_num_rows($seller) > 0) $userRoles['seller'] = 1;
        if ($role == 'manager' && $managerEmail == $email) $userRoles['manager'] = 1;
        if ($role == 'volunteer' && mysqli_num_rows($vol) > 0) $userRoles['volunteer'] = 1;            
    }
    // При изменении роли перезагружаем страницу с новыми правами
    if (isset($_POST['role'])) {
        $_SESSION['currentRole'] = $_POST['role'];
        header("Location: ".$_SERVER['PHP_SELF']."?name=".$_GET['name']);
        exit();
    }
    // Если перешли с ролью выше, которой у пользователя нет на данной ярмарке, то понижаем до "посетителя"
    if (!isset($_SESSION['currentRole'])) $_SESSION['currentRole'] = 'visitor';
    if ($_SESSION['currentRole'] == 'seller' && !isset($userRoles['seller'])) $_SESSION['currentRole'] = 'visitor';
    if ($_SESSION['currentRole'] == 'manager' && !isset($userRoles['manager'])) $_SESSION['currentRole'] = 'visitor';
    if ($_SESSION['currentRole'] == 'volunteer' && !isset($userRoles['volunteer'])) $_SESSION['currentRole'] = 'visitor';
    
    $currentUserRole = isset($_SESSION['currentRole']) ? $_SESSION['currentRole'] : 'visitor';


    $query2 = mysqli_query($connection, "SELECT a.`database-name` FROM $tableName AS a INNER JOIN `user-favourites` AS f ON a.`database-name`= f.`database-name`WHERE f.email='$email' AND f.`database-name`='$marketDb'");
    if (mysqli_num_rows($query2) == 0)  {
        $userFavourite = '&#9825';
        $_SESSION['favAction'] = 'add';
    }
    else {
        $userFavourite = '&#10084';
        $_SESSION['favAction'] = 'delete';
    }

    mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");

    mysqli_query($connection, "USE `".$marketDb."`");
    $marketInfo = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `market-info`"));

    // Получаем медиа (фото и видео)
    $media = mysqli_query($connection, "SELECT * FROM media ORDER BY uploadDate DESC");

    // Получаем отзывы
    $reviews = mysqli_query($connection, "SELECT * FROM reviews ORDER BY date DESC, time DESC");
    $reviewsCount = mysqli_num_rows($reviews);
    // Вычисляем средний рейтинг
    $avgRating = 0;
    if ($reviewsCount > 0) {
        $sumRating = 0;
        mysqli_data_seek($reviews, 0); // Сбрасываем указатель результата
        while ($review = mysqli_fetch_assoc($reviews)) {
            $sumRating += $review['score'];
        }
        $avgRating = round($sumRating / $reviewsCount, 1);
    }

    // Получаем схему ярмарки
    //$layout = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `market-layout` LIMIT 1"));      
    
    include 'market-header.php';
    ?>



    <main class="single-market">
        <section class="market-header">
            <table border="0" width="50%" align="center">
                <tr> 
                    <td><h1><a href="market-page.php?name=<?= $marketInfo['name'] ?>" class="menuButtonCur"><?= htmlspecialchars($marketInfo['name']) ?></a></h1></td>
                    <td><h1><a href="market-products.php?name=<?= $marketInfo['name'] ?>" class="menuButton">Товары</a></h1></td>
                    <td><h1><a href="market-map.php?name=<?= $marketInfo['name'] ?>" class="menuButton">Карта</a></h1></td>
                </tr>
            </table>
        </section>

    <!-- Галерея -->
            <!-- <h2>Галерея ярмарки</h2> -->
        <section class="media-gallery">
            <div class="gallery-container">
            <button class="gallery-nav-btn prev-btn" onclick="scrollGallery(-420)">‹</button>
            <div class="gallery-scroll-wrapper" id="galleryScroll">
            <div class="gallery-grid">
            
                <?php while($item = mysqli_fetch_assoc($media)): ?>
                    <?php if($item['type'] == 'image'): ?>
                        <div class="gallery-item">
                            <img src="<?= htmlspecialchars($item['fileName']) ?>" alt="Фото ярмарки">
                        </div>
                    <?php else: ?>
                        <div class="gallery-item video">
                            <video controls>
                                <source src="<?= htmlspecialchars($item['fileName']) ?>" type="video/mp4">
                                Ваш браузер не поддерживает видео.
                            </video>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>
            </div>
            <button class="gallery-nav-btn next-btn" onclick="scrollGallery(420)">›</button>
            </div>
        </section>

    <!-- Описание ярмарки -->
        <section class="market-description">
            <!-- <h2>Описание</h2> -->
            <div class="layout-placeholder">
            <p><?= nl2br(htmlspecialchars($marketInfo['description'])) ?></p>
            </div>
            <div class="market-meta">
                <span class="dates">
                    <?= date('d.m.Y', strtotime($marketInfo['openingDate'])) ?> - 
                    <?= date('d.m.Y', strtotime($marketInfo['closingDate'])) ?>
                </span>
                <span class="dates">
                    <?= date('H:i', strtotime($marketInfo['openingTime'])) ?> - 
                    <?= date('H:i', strtotime($marketInfo['closingTime'])) ?>
                </span>
                <!-- <span class="price">Вход: <?= $marketInfo['ticketPrice'] ?> руб.</span> -->
                <span class="address"><?= htmlspecialchars($marketInfo['address']) ?></span>
            </div>
        </section>

    <!-- Блок действий (зависит от роли) -->
        <section class="action-buttons">
            <!-- Кнопка добавления медиа (видна только организатору) -->
            <?php if($currentUserRole == 'manager'): ?>
                <a href="add-media.php">
                    <button class="action-btn">Изменить галерею</button>
                </a>
                <a href="add-members.php">
                    <button class="action-btn">Изменить участников</button>
                </a>
                <a href="edit-market.php?name=<?= $marketInfo['name'] ?>">
                    <button class="action-btn" >Изменить ярмарку</button>
                </a>
            <?php endif; ?>
             <?php if($currentUserRole == 'visitor'): ?>  <!-- посетитель -->
                <a href="visit-market.php?name=<?= urlencode($marketInfo['name']) ?>">
                    <button class="action-btn">Я хочу прийти</button>
                </a>
            <?php endif; ?>

            <?php if($currentUserRole == 'volunteer' || $currentUserRole == 'manager'): ?> <!-- волонтёр или орг -->
                <a href="market-visitors.php?name=<?= urlencode($marketInfo['name']) ?>">
                    <button class="action-btn">Список посетителей</button>
                </a>
                <!-- <form action=market-visitors.php method="POST" id="visitorsLlist"> -->
                    <!-- передать имя бд ярмарки из переменной $_SESSION['currentMarketDB'] -->
                    <!-- <button class="action-btn">Список посетителей</button> -->
                <!-- </form> -->
            <?php endif; ?>    

            <form action=scripts\favourites-action.php method="POST" id="loginForm">
                <button class="action-btn"><?php echo $userFavourite ?></button>
            </form>
        </section>
        <!---------------------------------------------------------------->
    <!-- Секция отзывов -->
        <section class="reviews-section">
            <div class="reviews-header">
                <h2>Отзывы</h2>
                <div class="average-rating">
                    <?php if ($reviewsCount > 0): ?>
                        <span><?= $avgRating ?></span>
                        <span class="rating-stars">★</span>
                        <span>(<?= $reviewsCount ?>)</span>
                    <?php else: ?>
                        <span>Нет отзывов</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Форма добавления отзыва -->
            <div class="review-form">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="review_text">Ваш отзыв</label>
                        <textarea id="review_text" name="review_text" required></textarea>
                    </div>
                    <div class="form-group rating-select">
                        <label for="rating">Оценка:</label>
                        <select id="rating" name="rating" required>
                            <option value="">Выберите оценку</option>
                            <option value="5">★ ★ ★ ★ ★</option>
                            <option value="4">★ ★ ★ ★ ☆</option>
                            <option value="3">★ ★ ★ ☆ ☆</option>
                            <option value="2">★ ★ ☆ ☆ ☆</option>
                            <option value="1">★ ☆ ☆ ☆ ☆</option>
                        </select>
                    </div>
                    <button type="submit" name="submit_review" class="submit-review">Отправить отзыв</button>
                </form>
            </div>
            
            <!-- Список отзывов -->
            <div class="reviews-list">
                <?php if ($reviewsCount > 0): ?>
                    <?php mysqli_data_seek($reviews, 0); // Сбрасываем указатель результата ?>
                    <?php while ($review = mysqli_fetch_assoc($reviews)): 
                        $sn = $review['senderLogin'];
                        $senderEmail = mysqli_fetch_assoc(mysqli_query($connection, "SELECT email FROM market.users WHERE `login` = '$sn'"))['email'];
                        ?>
                        <div class="review-item">
                            <?php 
                            if ($senderEmail == $email)
                            echo '<a href="market-page.php?name='.$_GET['name'].'&delete=1&id='.$review['id'].'" >Удалить</a>';
                            ?>

                            <div class="review-header">
                                <span class="review-author"><?= htmlspecialchars($review['senderLogin']) ?></span>
                                <span class="review-date"><?= $review['date'] ?> в <?= $review['time'] ?></span>
                            </div>
                            <div class="review-rating">
                                <?= str_repeat('★', $review['score']) ?><?= str_repeat('☆', 5 - $review['score']) ?>
                            </div>
                            <div class="review-text">
                                <?= nl2br(htmlspecialchars($review['text'])) ?>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-reviews">Пока нет отзывов. Будьте первым!</div>
                <?php endif; ?>
            </div>
        </section>
    </main>


    <script>
    // Открытие/закрытие выпадающего меню
    document.querySelector('.account-btn').addEventListener('click', function() {
        this.parentElement.classList.toggle('active');
    });

    // Закрытие меню при клике вне его
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.account-dropdown')) {
            document.querySelector('.account-dropdown').classList.remove('active');
        }
    });

    function scrollGallery(offset) {
        const gallery = document.getElementById('galleryScroll');
        gallery.scrollBy({
            left: offset,
            behavior: 'smooth'
        });
    }
    
</script>
</body>
</html>
