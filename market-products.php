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
    
    // Получаем все возможные роли пользователя
    $result1 = mysqli_query($connection, "SELECT role FROM `user-roles` WHERE email = '$email'");
    $seller = mysqli_query($connection, "SELECT email FROM `$marketDb`.`market-members` WHERE email = '$email' AND `role` = 'seller' AND `status` = 'Участвует'");
    $vol = mysqli_query($connection, "SELECT email FROM `$marketDb`.`market-members` WHERE email = '$email' AND `role` = 'volunteer' AND `status` = 'Участвует'");
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
    // Задаём переменную с ролью    
    $currentUserRole = isset($_SESSION['currentRole']) ? $_SESSION['currentRole'] : 'visitor';


    // // Получаем информацию о ярмарке
    // $result2 = mysqli_query($connection, "SELECT `database-name` FROM $tableName WHERE name = '$marketName'");
    // $row2 = mysqli_fetch_assoc($result2);
    // $marketDb = $row2['database-name'];
    // $_SESSION['currentMarketDB'] = $marketDb;

    // Удаляем временную таблицу, так как все данные из неё были использованы
    mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");

    // Получаем параметры фильтрации и сортировки
    $search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date DESC';
    $showPast = isset($_GET['show_past']) ? $_GET['show_past'] : false; // Будет для закончившихся 

    include 'scripts\getAllProducts.php';
    include 'market-header.php';
    ?>

    <!-- Вкладки ярмарки (Маркет Товары Карта) -->
    <main class="single-market">
        <section class="market-header">
            <table border="0" width="50%" align="center">
                <tr> 
                    <td><h1><a href="market-page.php?name=<?= $marketInfo['name'] ?>" class="menuButton"><?= htmlspecialchars($marketInfo['name']) ?></a></h1></td>
                    <td><h1><a href="market-products.php?name=<?= $marketInfo['name'] ?>" class="menuButtonCur">Товары</a></h1></td>
                    <td><h1><a href="market-map.php?name=<?= $marketInfo['name'] ?>" class="menuButton">Карта</a></h1></td>
                </tr>
            </table>
        </section>
        
        <section class="action-buttons">
            <?php if($currentUserRole == 'seller'): ?>
                <a href="create-order.php">
                    <button class="action-btn">Составить заказ</button>
                </a>
            <?php endif; ?>
        </section>

        <section class="market-products">
            <!-- Поиск Сорт Раскуп -->
            <form method="GET" class="search-form">
                <input type="hidden" name="name" value="<?= htmlspecialchars($marketInfo['name']) ?>">

                <div class="search-box">
                    <input type="text" name="search" placeholder="Поиск..." value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="sort-options">
                    <label for="sort-select">Сортировка:</label>
                    <select name="sort" id="sort-select" onchange="this.form.submit()">
                        <option value="sellerName" <?= $sort == 'sellerName' ? 'selected' : '' ?>>Продавец</option>
                        <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Название</option>
                        <option value="price" <?= $sort == 'price' ? 'selected' : '' ?>>Дешевле</option>
                        <option value="date DESC" <?= $sort == 'date DESC' ? 'selected' : '' ?>>Новее</option>
                    </select>
                </div>

                <div class="show-past">
                    <input type="checkbox" id="show_past" name="show_past" value="1" <?= $showPast ? 'checked' : '' ?> onchange="this.form.submit()">
                    <label for="show_past">Показать не в наличии</label>
                </div>
            </form>
            
            <!-- СЕТКА КАРТОЧЕК ТОВАРОВ -->
            <div class="products-grid">
                <?php if (empty($marketProducts)): ?>
                    <p class="no-products">На этой ярмарке пока нет товаров</p>
                <?php else: ?>
                    <?php foreach ($marketProducts as $product): ?>
                        <div class="product-card">
                            <div class="product-image-container">
                                <img src="<?= htmlspecialchars($product['pictureFileName']) ?>" 
                                        alt="<?= htmlspecialchars($product['name']) ?>" 
                                        class="product-image">
                                <!-- ??? -->
                            </div>

                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['type']) ?> "<?= htmlspecialchars($product['name']) ?>"</h3>
                                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="product-price"><?= $product['price'] ?> руб.</p>
                                <p class="product-seller">Продавец: <?= htmlspecialchars($product['sellerName']) ?> (<?= $product['quantity'] ?> шт.)</p>

                                <!-- Форма добавления в корзину -->
                                <?php if ($product['quantity']>0): ?>
                                    <form class="add-to-cart-form" method="POST" action="scripts/addToCart.php">
                                        <input type="hidden" name="market_db" value="<?= htmlspecialchars($_SESSION['currentMarketDB']) ?>">
                                        <input type="hidden" name="market_name" value="<?= htmlspecialchars($_SESSION['currentMarket']) ?>">
                                        <!-- <input type="hidden" name="seller_email" value="<//?= htmlspecialchars($product['email']) ?>"> -->
                                        <input type="hidden" name="seller_name" value="<?= htmlspecialchars($product['sellerName']) ?>">
                                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['trueId']) ?>">
                                        <input type="hidden" name="user_email" value="<?= htmlspecialchars($email) ?>">
                                        <input type="hidden" name="cur_quantity" value="<?= htmlspecialchars($product['quantity']) ?>">
                                        <!-- <input type="hidden" name="product_name" value="<//?= htmlspecialchars($product['name']) ?>"> -->
                                        <!-- <input type="hidden" name="product_price" value="<//?= htmlspecialchars($product['price']) ?>"> -->
                                        
                                        <button type="submit" class="add-to-cart-btn">
                                            В корзину
                                        </button>

                                        <div class="quantity-controls">
                                            <label for="quantity_<?= $product['id'] ?>" class="product-seller">Количество: </label>
                                            <input type="number" id="quantity_<?= $product['id'] ?>" 
                                                name="quantity" value="1" min="1" max="<?= $product['quantity'] ?>" 
                                                class="quantity-input">
                                        </div>
                                <?php endif; ?>
                                    

                                </form>

                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        
    </main>

    <script src='scripts\functions.js'></script>
</body>
</html>
