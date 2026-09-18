<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История заказов</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <?php
        session_start();
        include 'scripts/checkAuth.php';
        include 'scripts/connectToDB.php';
        include 'scripts/getLogin.php';

        $sortMarkets = isset($_GET['name']) ? " AND `name` = '".$_GET['name']."'" : '';

        // Определяем доступные ярмарки для пользователя
        $managedMarkets = [];
        $sellerMarkets = [];
        $isManager = false;

        $isSeller = (mysqli_num_rows(mysqli_query($connection, "SELECT * FROM market.`user-roles` WHERE email = '$email' AND `role` = 'seller'")) != 0);
        $isVisitor = (mysqli_num_rows(mysqli_query($connection, "SELECT * FROM market.`user-roles` WHERE email = '$email' AND `role` = 'visitor'")) != 0);

        // Для менеджеров получаем список управляемых ярмарок
        $sort = 'id'; $showPast = false;
        include 'scripts\getManagedMarkets.php';
        $managerMarkets = mysqli_query($connection, 
            "SELECT `database-name` FROM $tableName
            WHERE managerEmail='$email'".$sortMarkets);
        while ($market = mysqli_fetch_assoc($managerMarkets)) {
            $managedMarkets[] = $market['database-name'];
            $isManager = true;
        }

        // echo 'seller '.$isSeller.'<br>';
        // echo 'visitor '.$isVisitor.'<br>';
        // echo 'manager '.$isManager.'<br>';

        // Для продавцов получаем список ярмарок, где они продают/продавали
        if ($isSeller) {
            $sellerResult = mysqli_query($connection,
                "SELECT DISTINCT o.marketDBName 
                FROM `market`.`orders` o
                JOIN `market`.`order-items` i ON o.id = i.`orders-id`
                WHERE i.sellerName = '".$userData['login']."'");
            while ($market = mysqli_fetch_assoc($sellerResult)) {
                $sellerMarkets[] = $market['marketDBName'];
            }
        }

        // Получаем заказы в зависимости от роли
        $orders = [];
        $ordersManager = [];
        $ordersSeller = [];
        $ordersVisitor = [];
        if ($isManager && !empty($managedMarkets)) {
            // Менеджер видит все заказы на своих ярмарках
            $marketList = "'" . implode("','", $managedMarkets) . "'";
            //print_r($marketList);
            $ordersQueryManager = "SELECT * FROM `market`.`orders` 
                        WHERE `status` = 'завершён' AND marketDBName IN ($marketList)
                        ORDER BY date DESC, time DESC";
        } 
        if ($isSeller && !empty($sellerMarkets)) {
            // Продавец видит свои заказы на всех ярмарках
            $marketList = "'" . implode("','", $sellerMarkets) . "'";
            $ordersQuerySeller = "SELECT o.* FROM `market`.`orders` o
                        JOIN `market`.`order-items` i ON o.id = i.`orders-id`
                        WHERE `status` = 'завершён' AND o.marketDBName IN ($marketList) 
                        AND i.sellerName = '".$userData['login']."'
                        GROUP BY o.id
                        ORDER BY o.date DESC, o.time DESC";
        } 
        if ($isVisitor) {
            // Посетитель видит только свои завершенные заказы
            $ordersQueryVisitor = "SELECT * FROM `market`.`orders` 
                        WHERE buyer = '$email' AND `status` = 'завершён'
                        ORDER BY date DESC, time DESC";
        }

        include 'header.php';
    ?>
    <main class="orders-container">
        <h1>История заказов</h1>
        
        <?php 
            if (!empty($ordersQueryManager)) {
                //echo 'организатор';
                $roleStr = 'организатор';
                $ordersManager = mysqli_query($connection, $ordersQueryManager);
                if (mysqli_num_rows($ordersManager) != 0) {
                    $orders = $ordersManager;
                    include 'scripts\orderHistoryInfo.php';
                }
            }        
            if (!empty($ordersQuerySeller)) {
                //echo 'продавец';
                $roleStr = 'продавец';
                $ordersSeller = mysqli_query($connection, $ordersQuerySeller);
                if (mysqli_num_rows($ordersSeller) != 0) {
                    $orders = $ordersSeller;
                    include 'scripts\orderHistoryInfo.php';
                }
            }        
            if (!empty($ordersQueryVisitor)) {
                //echo 'посетитель';
                $roleStr = 'посетитель';
                $ordersVisitor = mysqli_query($connection, $ordersQueryVisitor);
                if (mysqli_num_rows($ordersVisitor) != 0) {
                    $orders = $ordersVisitor;
                    include 'scripts\orderHistoryInfo.php';
                }
            }
 
        ?>

    </main>
    <script src='scripts\functions.js'></script>
</body>
</html>
