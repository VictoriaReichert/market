<?php   
    // Получаем название таблицы товаров пользователя
    $sellerName = $_GET['seller'];
    $sellerEmail = mysqli_fetch_assoc(mysqli_query($connection, "SELECT email FROM market.users WHERE `login` = '$sellerName'"))['email'];

    $result2 = mysqli_query($connection, "SELECT * FROM `market-catalogs`.`user-catalogs` WHERE email = '$sellerEmail'");
    if (mysqli_num_rows($result2) > 0) {
        $tableName = mysqli_fetch_assoc($result2)['tableName'];

        // Получаем товары продавца
        $search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date DESC';
        
        $query = "SELECT * FROM `market-catalogs`.`$tableName` WHERE 1=1";
        if ($search) $query .= " AND name LIKE '%$search%'";
        $query .= " ORDER BY $sort";

        $sellerProducts = mysqli_query($connection, $query);
    }
?>