<?php    
    //print_r($_SESSION);
    
    // Информация о ярмарке
    mysqli_query($connection, "USE `".$marketDb."`");
    $marketInfo = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `market-info`"));

    // Информация о всех продавцах ярмарки
    $table1NameAllP = "`".$_SESSION['currentMarketDB']."`.temp1";
    $table2NameAllP = "`".$_SESSION['currentMarketDB']."`.temp2";
    mysqli_query($connection, "DROP TABLE IF EXISTS $table1NameAllP");
    mysqli_query($connection, "DROP TABLE IF EXISTS $table2NameAllP");

    mysqli_query($connection, "CREATE TABLE $table1NameAllP
    (
        id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email varchar(100) NOT NULL,
        tableName varchar(45) NOT NULL,
        sellerName varchar(45) NOT NULL,
        tableNumber int 
    )");
    mysqli_query($connection, "CREATE TABLE $table2NameAllP
    (
        id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email varchar(100) NOT NULL,
        tableName varchar(45) NOT NULL,
        sellerName varchar(45) NOT NULL,
        `type` varchar(45) NOT NULL,
        `name` varchar(100) NOT NULL,
        `description` varchar(600) NOT NULL,
        `price` int unsigned NOT NULL,
        `quantity` int unsigned NOT NULL,
        `pictureFileName` varchar(300) NOT NULL,
        `date` date NOT NULL,
        trueId int
    )");


    $marketSellers = mysqli_query($connection, "SELECT email FROM `market-members` WHERE role = 'seller' AND `status` = 'Участвует'");

    while(mysqli_num_rows($marketSellers) > 0 && $marketSeller = mysqli_fetch_assoc($marketSellers)):
        // Подготовка данных для товара
        $sellerEmailAllP = $marketSeller['email'];
        $sellerTableNameAllP = mysqli_fetch_assoc(mysqli_query($connection, "SELECT tableName FROM `market-catalogs`.`user-catalogs` WHERE email = '$sellerEmailAllP'"))['tableName'];
        $sellerNameAllP = mysqli_fetch_assoc(mysqli_query($connection, "SELECT `login` FROM market.users WHERE email = '$sellerEmailAllP'"))['login'];
        $tableNum = mysqli_fetch_assoc(mysqli_query($connection, "SELECT tableNumber FROM `market-members` WHERE email = '$sellerEmailAllP' AND `role` = 'seller'"))['tableNumber'];


        // Подготовка 1 таблицы
        mysqli_query($connection, 
        "INSERT INTO $table1NameAllP (email, tableName, sellerName, tableNumber)
        VALUES ('$sellerEmailAllP', '$sellerTableNameAllP', '$sellerNameAllP', '$tableNum')
        ");
        
        // Подготовка 2 таблицы
        mysqli_query($connection, 
        "INSERT INTO $table2NameAllP (email, tableName, sellerName, `type`, `name`, `description`, price, quantity, pictureFileName, `date`, trueId)
        SELECT email, tableName, sellerName, `type`, `name`, `description`, price, quantity, pictureFileName, `date`, t2.id
        FROM $table1NameAllP AS t1 JOIN `market-catalogs`.`$sellerTableNameAllP` AS t2 ON t1.email = '$sellerEmailAllP';
        ");

    endwhile;

    //print_r(mysqli_fetch_assoc(mysqli_query($connection, "SELECT  * FROM $table2NameAllP")));

    // ПОИСК по имени продавца, названию товара
    // СОРТИРОВКА по цене, названию, новизне

    $queryAllP = "SELECT * FROM $table2NameAllP WHERE 1=1";
    if (!$showPast) 
        $queryAllP .= " AND quantity > 0";
    $queryAllP .= " ORDER BY $sort";

    $allProductsInfo = mysqli_query($connection, "SELECT * FROM $table1NameAllP");

    $marketProducts = mysqli_query($connection, $queryAllP);
    mysqli_query($connection, "DROP TABLE IF EXISTS $table1NameAllP");
    mysqli_query($connection, "DROP TABLE IF EXISTS $table2NameAllP");
?>