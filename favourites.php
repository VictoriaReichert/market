<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранное</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <?php
        session_start();
        include 'scripts/checkAuth.php'; 
        include 'scripts/connectToDB.php';
        include 'scripts/getLogin.php';

        // Получаем параметры фильтрации и сортировки
        $search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'openingDate';
        $showPast = isset($_GET['show_past']) ? $_GET['show_past'] : false;

        include 'header.php';
    ?>

    <main>
        <section class="markets-section">
            <h1>Избранное</h1>
        </section>

        <div class="filters-container">
            <?php
                include 'filters.html';
            ?>
        </div>
        
        <div class="markets-grid">
            <?php
                include 'scripts\getFavourites.php';
                if (mysqli_num_rows($allMarketsInfo) > 0) {
                    include 'scripts\showSelected.php';
                }
                else {
                    echo '
                    <div class="empty-cart">
                        <p>Ваша корзина пуста</p>
                        <a href="market.php" class="action-btn">Перейти к ярмаркам</a>
                    </div>
                    ';
                }
            ?>
        </div>
    </main>

    <script src='scripts\functions.js'></script>

</body>
</html>
