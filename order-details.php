<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали заказа</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <?php
    session_start();
    include 'scripts/checkAuth.php';
    include 'scripts/connectToDB.php';
    include 'scripts/getLogin.php';
    
    if (!isset($_GET['id'])) {
        //header("Location: orders.php");
        //exit();
    }
    
    $orderId = (int)$_GET['id'];
    $order = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `market`.`orders` WHERE id = $orderId"));
    
    // Проверка прав доступа
    if ($order['buyer'] != $email && !mysqli_num_rows(mysqli_query($connection, 
        "SELECT 1 FROM `market`.`order-items` WHERE `orders-id` = $orderId AND sellerName = '".$userData['login']."'"))) {
        //header("Location: orders.php");
        //exit();
    }
    
    $marketName = mysqli_fetch_assoc(mysqli_query($connection, 
        "SELECT `name` FROM `".$order['marketDBName']."`.`market-info`"))['name'];
    $buyerInfos = mysqli_query($connection, 
        "SELECT login FROM `market`.`users` WHERE email = '".$order['buyer']."'");
    if (mysqli_num_rows($buyerInfos) != 0) {
        $buyerInfo = mysqli_fetch_assoc($buyerInfos);
        $bil = $buyerInfo['login'];
    }
    else {
        $bil = 'посетитель';
    }
    include 'header.php';

    $sellerName = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `market`.`order-items` WHERE `orders-id` = $orderId"))['sellerName'];

    ?>

    <main class="order-details-container">
        <h1>Детали заказа #<?= $orderId ?> (<?= $order['type'] ?>)</h1>
        
        <div class="order-info">
            <p><strong>Ярмарка:</strong> <?= htmlspecialchars($marketName) ?></p>
            <p><strong>Продавец:</strong> <?= htmlspecialchars($sellerName) ?> (столик <?= htmlspecialchars($order['tableNumber']) ?>)</p>
            <p><strong>Покупатель:</strong> <?= htmlspecialchars($bil) ?></p>
            <p><strong>Дата:</strong> <?= $order['date'].' '.$order['time'] ?></p>
            <p><strong>Статус:</strong> <span class="status-<?= $order['status'] ?>"><?= $order['status'] ?></span></p>
            <p><strong>Сумма:</strong> <?= $order['cost'] ?> руб.</p>
        </div>
        
        <h2>Состав заказа</h2>
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Фото</th>
                    <th>Товар</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $items = mysqli_query($connection, "SELECT * FROM `market`.`order-items` WHERE `orders-id` = $orderId");
                while ($item = mysqli_fetch_assoc($items)) {
                    echo '<tr>';
                    echo '<td><img src="'.htmlspecialchars($item['pictureFileName']).'" alt="'.htmlspecialchars($item['name']).'" style="width: 50px; height: 50px; object-fit: cover;"></td>';
                    echo '<td>
                            <div class="item-name">'.htmlspecialchars($item['name']).'</div>
                            <div class="item-desc">'.htmlspecialchars($item['description']).'</div>
                          </td>';
                    echo '<td>'.$item['price'].' руб.</td>';
                    echo '<td>'.$item['quantity'].'</td>';
                    echo '<td>'.($item['price'] * $item['quantity']).' руб.</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        
        <div class="order-actions">
            <!-- <a href="orders.php" class="action-btn">Вернуться к списку заказов</a> -->
        </div>
    </main>
    <script src='scripts\functions.js'></script>
</body>
</html>
