<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <?php
    session_start();
    include 'scripts/checkAuth.php';
    include 'scripts/connectToDB.php';
    include 'scripts/getLogin.php';
    
    // Обработка удаления товара из корзины
    if (isset($_GET['remove'])) {
        $itemId = (int)$_GET['id'];
        mysqli_query($connection, "DELETE FROM `market`.`cart` WHERE id = $itemId AND userEmail = '$email'");
        header("Location: cart.php");
        exit();
    }
    
// Обработка оформления заказа -------------------------------------------------------------------------
    if (isset($_POST['checkout'])) {
        $marketDB = mysqli_real_escape_string($connection, $_POST['market_db']);
        $sellerName = mysqli_real_escape_string($connection, $_POST['sellerName']);
        $buyer = mysqli_real_escape_string($connection, $_POST['buyer']);
        $cost = mysqli_real_escape_string($connection, $_POST['cost']);
        
        date_default_timezone_set('Europe/Moscow');
        $date = date('Y-m-d');
        $time = date('H:i:s');

        $type = 'бронь';
        $status = 'создано';

        $sellerEmail = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM market.users WHERE `login` = '$sellerName'"))['email'];
        $tableNumber = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `$marketDB`.`market-members` WHERE email = '$sellerEmail' AND `role` = 'seller'"))['tableNumber'];

        // Переносим товары из корзины в заказы
        mysqli_query($connection, "INSERT INTO `market`.`orders` (`type`, marketDBName, tableNumber, buyer, cost, `date`, `time`, `status`)
                                    VALUES
                                    ('$type', '$marketDB', $tableNumber, '$buyer', $cost, '$date', '$time', '$status')
                                    ");
        $ordersId = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `market`.`orders`
                                        WHERE `type`= '$type' AND marketDBName = '$marketDB' AND  buyer = '$buyer' AND 
                                        cost = $cost AND `date` = '$date' AND `time` = '$time' AND `status` = '$status'"))['id'];
                         
        $cartItems = mysqli_query($connection, "SELECT * FROM `market`.`cart` WHERE userEmail = '$email' AND marketDBName = '$marketDB' AND sellerName = '$sellerName'");
        while ($item = mysqli_fetch_assoc($cartItems)) {
            $sellerTable = "`".$item['sellerName']."`";
            $productId = $item['product-id'];
            $prodInfo = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `market-catalogs`.$sellerTable WHERE id = $productId"));

            // Из таблицы товаров
            $type = $prodInfo['type'];
            $name = $prodInfo['name'];
            $description = $prodInfo['description'];
            $price = $prodInfo['price'];
            $pictureFileName = $prodInfo['pictureFileName'];

            // Из формы
            $sellerName = $item['sellerName'];
            $quantity = $item['quantity'];

            mysqli_query($connection, 
            "INSERT INTO `market`.`order-items` (`orders-id`, sellerName, `type`, `name`, `description`, price, quantity, pictureFileName)
            VALUES
            ($ordersId, '$sellerName', '$type', '$name', '$description', $price, $quantity, '$pictureFileName')
            ");
        }

        // Удаляем товары из корзины
        mysqli_query($connection, "DELETE FROM `market`.`cart` WHERE userEmail = '$email' AND marketDBName = '$marketDB' AND sellerName = '$sellerName'");
        
        header("Location: cart.php");
        exit();
    }
// Обработка оформления заказа -------------------------------------------------------------------------
    
// Получаем содержимое корзины и помещаем его во временную таблицу ---------------------------------------------------------------------
    $userLogin = $userData['login'];
    $tableName = "temp.`".$userLogin."_cart`";

    mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");
    $table = "CREATE TABLE $tableName
    (
        id int NOT NULL PRIMARY KEY,
        `marketDBName` varchar(45) NOT NULL,
        `marketName` varchar(45) NOT NULL,
        `sellerName` varchar(45) NOT NULL,
        `product-id` varchar(45) NOT NULL,
        `userEmail` varchar(100) NOT NULL,
        `quantity` int NOT NULL,
        `date` varchar(45) NOT NULL,

        `type` varchar(45) NOT NULL,
        `name` varchar(100) NOT NULL,
        `description` varchar(600) NOT NULL,
        `price` int unsigned NOT NULL,
        `pictureFileName` varchar(300) NOT NULL,
        `total` int unsigned NOT NULL
    )";
    mysqli_query($connection, $table);

    $cartInfo = mysqli_query($connection, "SELECT * FROM `market`.`cart` WHERE userEmail = '$email' ORDER BY marketDBName, sellerName, `date`");
    if (mysqli_num_rows($cartInfo) > 0) {
        while ($row = mysqli_fetch_assoc($cartInfo)) {
            $sellerTable = '`'.$row['sellerName'].'`';
            $prodId = $row['product-id'];
            $cartId = $row['id'];

            mysqli_query($connection, "INSERT INTO $tableName (id,`marketDBName`,`marketName`,`sellerName`,`product-id`,`userEmail`,`quantity`,`date`,`type`,`name`,`description`,`price`,`pictureFileName`, `total`)
            SELECT c.id, `marketDBName`,`marketName`,`sellerName`,`product-id`,`userEmail`, c.`quantity`, c.`date`, `type`,`name`,`description`,`price`,`pictureFileName`,c.`quantity`*s.`price` AS total
            FROM `market`.`cart` AS c JOIN `market-catalogs`.$sellerTable AS s ON c.id = $cartId AND s.id = $prodId");
        }
    }

    $cartItems = mysqli_query($connection, "SELECT * FROM $tableName ORDER BY marketName, sellerName");
    mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");
// Получаем содержимое корзины и помещаем его во временную таблицу ---------------------------------------------------------------------

    include 'header.php';
    ?>

    <main class="cart-container">
        <h1>Ваша корзина</h1>
        
        <?php if (mysqli_num_rows($cartItems) == 0): ?>
            <div class="empty-cart">
                <p>Ваша корзина пуста</p>
                <a href="market.php" class="action-btn">Перейти к ярмаркам</a>
            </div>
        <?php else: ?>
            <?php
            $currentMarket = '';
            $groupItems = [];
            
            // Группируем товары по ярмаркам
            while ($item = mysqli_fetch_assoc($cartItems)) {
                if ($item['marketDBName'] != $currentMarket) {
                    if (!empty($groupItems)) {
                        renderMarketGroup($currentMarket, $groupItems);
                    }
                    $currentMarket = $item['marketDBName'];
                    $groupItems = [];
                }
                $groupItems[] = $item;
            }
            
            // Отображаем последнюю группу
            if (!empty($groupItems)) {
                renderMarketGroup($currentMarket, $groupItems);
            }
            ?>
        <?php endif; ?>
        
        <?php
        // Функция для отображения группы товаров по ярмарке
        function renderMarketGroup($marketDB, $items) {
            $marketName = $items[0]['marketName'];
            echo '<div class="market-group">';
            echo '<div class="market-header-cart">'.htmlspecialchars($marketName).'</div>';
            
            // Группируем товары по продавцам
            $currentSeller = '';
            $sellerItems = [];
            
            foreach ($items as $item) {
                if ($item['sellerName'] != $currentSeller) {
                    if (!empty($sellerItems)) {
                        renderSellerGroup($marketDB, $currentSeller, $sellerItems);
                    }
                    $currentSeller = $item['sellerName'];
                    $sellerItems = [];
                }
                $sellerItems[] = $item;
            }
            
            // Отображаем последнего продавца
            if (!empty($sellerItems)) {
                renderSellerGroup($marketDB, $currentSeller, $sellerItems);
            }
            
            echo '</div>';
        }
        
        // Функция для отображения группы товаров по продавцу
        function renderSellerGroup($marketDB, $sellerName, $items) {
            $sellerTotal = array_sum(array_column($items, 'total'));
            
            echo '<div class="seller-group">';
            echo '<div class="seller-header-cart">Продавец: '.htmlspecialchars($sellerName).'</div>';
            echo '<div class="cart-items">';
            
            foreach ($items as $item) {
                echo '<div class="cart-item">';
                echo '<div class="item-info">';
                echo '<div class="item-name">'.htmlspecialchars($item['name']).'</div>';
                echo '<div class="item-description">'.htmlspecialchars($item['description']).'</div>';
                echo '</div>';
                echo '<div class="item-price">'.$item['total'].' рублей ('.$item['quantity'].' шт.)</div>';
                echo '<div class="item-controls">';
                echo '<a href="cart.php?remove=1&id='.$item['id'].'" class="remove-btn">Удалить</a>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
            echo '<div class="seller-total">';
            echo '<div class="total-price">Итого: '.$sellerTotal.' рублей</div>';
            
            echo '<form method="POST">';
            echo '<input type="hidden" name="market_db" value="'.htmlspecialchars($marketDB).'">';
            echo '<input type="hidden" name="sellerName" value="'.htmlspecialchars($sellerName).'">';
            echo '<input type="hidden" name="buyer" value="'.htmlspecialchars($items[0]['userEmail']).'">';
            echo '<input type="hidden" name="cost" value="'.htmlspecialchars($sellerTotal).'">';
            echo '<button type="submit" name="checkout" class="checkout-btn">Забронировать</button>';
            echo '</form>';
            
            echo '</div>';
            echo '</div>';
        }
        ?>
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

        // Подтверждение удаления
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
                    e.preventDefault();
                }
            });
        });

        // Подтверждение оформления заказа
        document.querySelectorAll('.checkout-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Вы уверены, что хотите оформить заказ у этого продавца?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
