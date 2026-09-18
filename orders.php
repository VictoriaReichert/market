<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заказы</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <?php
    session_start();
    include 'scripts/checkAuth.php';
    include 'scripts/connectToDB.php';
    include 'scripts/getLogin.php';
    
    // Обработка действий с заказами
    if (isset($_GET['action'])) {
        $orderId = (int)$_GET['id'];
        $action = $_GET['action'];
        date_default_timezone_set('Europe/Moscow');
        $date = date('Y-m-d');
        $time = date('H:i:s');
        
        switch ($action) {
            case 'cancel_buyer':
                mysqli_query($connection, "UPDATE `market`.`orders` SET status = 'отменён покупателем', date = '$date', time = '$time' WHERE id = $orderId");
                break;
            case 'cancel_seller':
                mysqli_query($connection, "UPDATE `market`.`orders` SET status = 'отменён продавцом', date = '$date', time = '$time' WHERE id = $orderId");
                break;
            case 'confirm_seller':
                mysqli_query($connection, "UPDATE `market`.`orders` SET status = 'забронирован', date = '$date', time = '$time' WHERE id = $orderId");
                $productQuantity = mysqli_query($connection, "SELECT * FROM `market`.`order-items` WHERE `orders-id` = $orderId");
                while ($p = mysqli_fetch_assoc($productQuantity)) {
                    $sellerTable = "`".$p['sellerName']."`";
                    $name = $p['name'];
                    $pic = $p['pictureFileName'];

                    $curQ = mysqli_fetch_assoc(mysqli_query($connection, "SELECT quantity FROM `market-catalogs`.$sellerTable WHERE `name` = '$name' AND pictureFileName = '$pic'"))['quantity'];
                    $newQ = (int)$curQ - (int)$p['quantity'];
                    mysqli_query($connection, "UPDATE `market-catalogs`.$sellerTable SET `quantity` = $newQ WHERE `name` = '$name' AND pictureFileName = '$pic'");
                }
                break;
            case 'complete_buyer':
                mysqli_query($connection, "UPDATE `market`.`orders` SET status = 'завершён', date = '$date', time = '$time' WHERE id = $orderId");
                break;
            case 'ok_buyer':
                mysqli_query($connection, "DELETE FROM `market`.`orders` WHERE id = $orderId");
                mysqli_query($connection, "DELETE FROM `market`.`order-items` WHERE `orders-id` = $orderId");
                break;
            case 'ok_seller':
                mysqli_query($connection, "DELETE FROM `market`.`orders` WHERE id = $orderId");
                mysqli_query($connection, "DELETE FROM `market`.`order-items` WHERE `orders-id` = $orderId");
                break;
        }
        
        header("Location: orders.php");
        exit();
    }
    
    include 'header.php';
    ?>

    <main class="orders-container">
        <h1>Мои заказы</h1>
        
        <div class="tabs">
            <div class="tab active" onclick="showTab('outgoing')">Исходящие</div>
            <div class="tab" onclick="showTab('incoming')">Входящие</div>
        </div>
        
        <!-- Исходящие заказы (покупатель) -->
        <div id="outgoing-tab" class="requests-section">
            <h2>Мои заказы</h2>
            <?php
            $outgoingOrders = mysqli_query($connection, "SELECT * FROM `market`.`orders` 
                                                      WHERE buyer = '$email' AND status != 'закончен' AND status != 'отменён покупателем' AND status != 'завершён' AND `type` = 'бронь'
                                                      ORDER BY date DESC, time DESC");
            
            if (mysqli_num_rows($outgoingOrders) > 0) {
                echo '<table class="requests-table">';
                echo '<thead><tr>
                        <th>№ заказа</th>
                        <th>Ярмарка</th>
                        <th>Сумма</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Действия</th>
                      </tr></thead>';
                echo '<tbody>';
                
                while ($order = mysqli_fetch_assoc($outgoingOrders)) {
                    $marketName = mysqli_fetch_assoc(mysqli_query($connection, 
                        "SELECT `name` FROM `".$order['marketDBName']."`.`market-info`"))['name'];
                    
                    echo '<tr>';
                    echo '<td>'.$order['id'].'</td>';
                    echo '<td>'.$marketName.'</td>';
                    echo '<td>'.$order['cost'].' руб.</td>';
                    echo '<td>'.$order['date'].' '.$order['time'].'</td>';
                    echo '<td class="status-'.$order['status'].'">'.$order['status'].'</td>';
                    echo '<td>';
                    
                    if ($order['status'] == 'создано') {
                        echo '<a href="orders.php?action=cancel_buyer&id='.$order['id'].'" class="action-btn">Отменить</a>';
                    } elseif ($order['status'] == 'забронирован') {
                        echo '<a href="orders.php?action=complete_buyer&id='.$order['id'].'" class="action-btn">Подтвердить получение</a>';
                    } elseif  ($order['status'] == 'отменён продавцом'){
                        echo '<a href="orders.php?action=ok_buyer&id='.$order['id'].'" class="action-btn">Ок</a>';
                    }
                    
                    echo ' <a href="order-details.php?id='.$order['id'].'" class="action-btn" target="_blank">Подробнее</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<p>У вас нет активных заказов.</p>';
            }
            ?>
        </div>
        
        <!-- Входящие заказы (продавец) -->
        <div id="incoming-tab" class="requests-section" style="display: none;">
            <h2>Заказы от покупателей</h2>
            <?php
            // Получаем заказы, где текущий пользователь является продавцом хотя бы одного товара
            $incomingOrders = mysqli_query($connection, "SELECT DISTINCT o.id, o.marketDBName, o.buyer, o.cost, o.date, o.time, o.status 
                                                       FROM `market`.`orders` o
                                                       JOIN `market`.`order-items` i ON o.id = i.`orders-id`
                                                       WHERE o.type = 'бронь' AND o.status != 'завершён' AND o.status != 'отменён продавцом' AND i.sellerName = '".$userData['login']."' 
                                                       ORDER BY o.date DESC, o.time DESC");

            if (mysqli_num_rows($incomingOrders) > 0) {
                echo '<table class="requests-table">';
                echo '<thead><tr>
                        <th>№ заказа</th>
                        <th>Покупатель</th>
                        <th>Сумма</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Действия</th>
                      </tr></thead>';
                echo '<tbody>';
                
                while ($order = mysqli_fetch_assoc($incomingOrders)) {
                    $buyerInfo = mysqli_fetch_assoc(mysqli_query($connection, 
                        "SELECT login FROM `market`.`users` WHERE email = '".$order['buyer']."'"));
                    
                    echo '<tr>';
                    echo '<td>'.$order['id'].'</td>';
                    echo '<td>'.$buyerInfo['login'].'</td>';
                    echo '<td>'.$order['cost'].' руб.</td>';
                    echo '<td>'.$order['date'].' '.$order['time'].'</td>';
                    echo '<td class="status-'.$order['status'].'">'.$order['status'].'</td>';
                    echo '<td>';
                    
                    if ($order['status'] == 'создано') {
                        echo '<a href="orders.php?action=cancel_seller&id='.$order['id'].'" class="action-btn">Отказать</a>';
                        echo ' <a href="orders.php?action=confirm_seller&id='.$order['id'].'" class="action-btn">Подтвердить</a>';
                    } elseif ($order['status'] == 'отменён покупателем') {
                        echo ' <a href="orders.php?action=ok_seller&id='.$order['id'].'" class="action-btn">Ок</a>';
                    }
                    
                    echo ' <a href="order-details.php?id='.$order['id'].'" class="action-btn" target="_blank">Подробнее</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<p>У вас нет заказов от покупателей.</p>';
            }
            ?>
        </div>
    </main>

    <script src='scripts\functions.js'></script>
    <script>
        function showTab(tabName) {
            // Скрыть все табы
            document.getElementById('incoming-tab').style.display = 'none';
            document.getElementById('outgoing-tab').style.display = 'none';
            
            // Убрать активный класс у всех табов
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Показать выбранный таб и добавить активный класс
            document.getElementById(tabName + '-tab').style.display = 'block';
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>
