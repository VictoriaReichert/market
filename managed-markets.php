<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои ярмарки</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <?php
        session_start();
        include 'scripts\checkAuth.php'; 
        //$_SESSION['pageUrl'] = $_SERVER['PHP_SELF'];
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
            <h1>Мои ярмарки</h1>
        </section>

        <div class="filters-container">
            <?php include 'filters.html'; ?>
        </div>


        <!-- Секция для ярмарок, где пользователь посетитель --------------------------------------------------------------------------------->
        <?php // Получаем ярмарки, где пользователь посетитель
            include 'scripts/getVisitorMarkets.php'; 
            if (mysqli_num_rows($allMarketsInfo) > 0) {
                echo '
                    <section class="role-section">
                        <h2>Посетитель</h2>
                        <div class="markets-grid">
                ';
                include 'scripts/showSelected.php';
                echo '
                        </div>
                    </section>';
            } else {
                mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");
                //echo '<p class="no-markets-message">Вы не идёте ни на одну ярмарку</p>';
            }
        ?>

        <!-- Секция для ярмарок, где пользователь организатор -->
        <section class="role-section">
            <h2>Организатор</h2>
            <div class="markets-grid">
                <?php
                // Получаем ярмарки, где пользователь организатор
                include 'scripts/getManagedMarkets.php';
                
                if (mysqli_num_rows($allMarketsInfo) > 0) {
                    include 'scripts/showSelected.php';
                } else {
                    mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");
                    //echo '<p class="no-markets-message">Вы не являетесь организатором ни одной ярмарки</p>';
                }
                ?>
                <!-- Кнопка создания новой ярмарки -->
                <div class="market-card">
                    <img src="media/add-market.png" class="market-image">
                    <div class="market-info">
                        <h3></h3>
                        <a href="create-market.php" class="learn-more-btn">Создать ярмарку</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Секция для ярмарок, где пользователь продавец -->
        <?php // Получаем ярмарки, где пользователь продавец
            include 'scripts/getSellerMarkets.php'; 
            if (mysqli_num_rows($allMarketsInfo) > 0) {
                echo '
                    <section class="role-section">
                        <h2>Продавец</h2>
                        <div class="markets-grid">
                ';
                include 'scripts/showSelected.php';
                echo '
                        </div>
                    </section>';
            } else {
                mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");
                //echo '<p class="no-markets-message">Вы не участвуете как продавец ни в одной ярмарке</p>';
            }
        ?>

        <!-- Секция для ярмарок, где пользователь волонтер -->
        <?php // Получаем ярмарки, где пользователь волонтер
            include 'scripts/getVolunteerMarkets.php'; 
            if (mysqli_num_rows($allMarketsInfo) > 0) {
                echo '
                    <section class="role-section">
                        <h2>Волонтёр</h2>
                        <div class="markets-grid">
                ';
                include 'scripts/showSelected.php';
                echo '
                        </div>
                    </section>';
            } else {
                mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");
                //echo '<p class="no-markets-message">Вы не участвуете как волонтёр ни в одной ярмарке</p>';
            }
        ?>

    </main>

    <script src='scripts\functions.js'></script>
    </body>
</html>
