<?php
    session_start();
    include 'checkAuth.php'; 

    $conf = parse_ini_file('../config/config.ini');
    $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);

    // 0. Получаем необходимые данные
    $email = $_COOKIE['logIn'];
    date_default_timezone_set('Europe/Moscow');
    $date = date('Y-m-d');
    $time = date('H:i:s');
    // Из формы
    $marketName_tr = translit($_POST['marketName']);
    $dbName = "market-manager-db_".$marketName_tr;
    $marketName = mysqli_real_escape_string($connection, $_POST['marketName']);
    $description = mysqli_real_escape_string($connection, $_POST['marketDescription']);
    $openingDate = $_POST['openingDate'];
    $closingDate = $_POST['closingDate'];
    $openingTime = $_POST['openingTime'].':00';
    $closingTime = $_POST['closingTime'].':00';
    $address = mysqli_real_escape_string($connection, $_POST['marketAddress']);
    $ticketPrice = $_POST['ticketPrice'];
    $tables = $_POST['tables'];
    $invitedSellers = isset($_POST['invitedSellers']) ? explode(',', $_POST['invitedSellers']) : [];

    $_SESSION['currentMarket'] = $_POST['marketName'];

    // 1. Создание базы данных
    $sql = "CREATE SCHEMA `".$dbName."`";
    if ($connection->query($sql) === TRUE) {
        $_SESSION['dbStatus'] = 'Создана база данных '.$dbName;
        // 1.5. Добавим запись о новом маркете
        mysqli_query($connection, "USE market"); 
        mysqli_query($connection, 
            "INSERT INTO `all-existing-markets` 
            (`database-name`, creationDate) 
            VALUES 
            ('$dbName', '$date')");
    } else {
        $_SESSION['dbStatus'] = 'Ошибка при создании базы данных';
    }
    

    // 2. Создание таблиц
    mysqli_query($connection, "USE `".$dbName."`"); 

    $table1 = "CREATE TABLE `market-info` (
        id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` varchar(45) NOT NULL,
        `description` varchar(600) NOT NULL,
        `openingDate` date NOT NULL,
        `closingDate` date NOT NULL,
        `openingTime` time NOT NULL,
        `closingTime` time NOT NULL,
        `address` varchar(300) NOT NULL,
        ticketPrice varchar(45) NOT NULL,
        pictureFileName varchar(300) NOT NULL,
        `mapFileName` varchar(300),
        `tables` int NOT NULL
        )";

    $table2 = "CREATE TABLE `market-members` (
        `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `email` varchar(100) NOT NULL,
        `role` varchar(45) NOT NULL,
        `status` varchar(45) NOT NULL,
        `date` date NOT NULL,
        `tableNumber` int NOT NULL DEFAULT '0'
        )";

    $table3 = "CREATE TABLE `media` (
        `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `fileName` varchar(100) NOT NULL,
        `type` varchar(10) NOT NULL,
        `uploadDate` date NOT NULL
        )";

    $table4 = "CREATE TABLE `visitors` (
        `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user` varchar(45) NOT NULL,
        `email` varchar(100) NOT NULL,
        `date` date NOT NULL,
        `status` VARCHAR(45) NOT NULL
        )";

    $table5 = "CREATE TABLE `reviews` (
        `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `senderLogin` varchar(45) NOT NULL,
        `text` varchar(600) NOT NULL,
        `score` int NOT NULL,
        `date` date NOT NULL,
        `time` time NOT NULL
    )";

    $table6 = "CREATE TABLE `requests-incoming` (
        `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `senderEmail` varchar(100) NOT NULL,
        `role` varchar(45) NOT NULL,
        `status` varchar(45) NOT NULL,
        `date` date NOT NULL
    )";

    $table7 = "CREATE TABLE `requests-outgoing` (
        `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `receiverEmail` varchar(100) NOT NULL,
        `role` varchar(45) NOT NULL,
        `status` varchar(45) NOT NULL,
        `date` date NOT NULL
    )";

    $table8 = "CREATE TABLE `market-days` (
        `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `date` date NOT NULL,
        `capacity` int NOT NULL
    )";

    if ($connection->query($table1) === TRUE && $connection->query($table2) === TRUE && $connection->query($table3) === TRUE 
    && $connection->query($table4) === TRUE && $connection->query($table5) === TRUE && $connection->query($table6) === TRUE 
    && $connection->query($table7) === TRUE && $connection->query($table8) === TRUE) {
        $_SESSION['dbTableStatus'] = 'Созданы все таблицы';
    } else {
        $_SESSION['dbTableStatus'] = 'Ошибка при создании таблиц';
    }


    // 3. Добавление фото
    //$marketName = $_POST['marketName'];
    // Обрабатываем загрузку обложки
    $coverFileName = '';
    if (isset($_FILES['marketCover'])) {
        $cover = $_FILES['marketCover'];
        $coverFileName = $cover['name'];
        $coverPath = 'marketsMedia/'.$marketName_tr.'/';
        
        if (!file_exists("../".$coverPath)) mkdir("../".$coverPath, 0777, true);

        $fullCoverPath = $coverPath.$coverFileName;
        if (!move_uploaded_file($cover['tmp_name'], "../".$fullCoverPath))
            die('Ошибка при загрузке обложки');
    }

    // Обрабатываем загрузку медиафайлов
    // $mediaFiles = [];
    // if (!empty($_FILES['marketMedia']['name'][0])) {
    //     foreach ($_FILES['marketMedia']['name'] as $key => $name) {
    //         $tmpName = $_FILES['marketMedia']['tmp_name'][$key];
    //         $error = $_FILES['marketMedia']['error'][$key];
            
    //         if ($error === UPLOAD_ERR_OK) {
    //             $fileType = strpos($_FILES['marketMedia']['type'][$key], 'mp4') !== false ? 'video' : 'image';
    //             $fileName = $name;
    //             $filePath = 'marketsMedia/'.$marketName_tr.'/';

    //             if (!file_exists("../".$filePath)) mkdir("../".$filePath, 0777, true);
                
    //             $fullPath = $filePath.$fileName;
    //             if (move_uploaded_file($tmpName, "../".$fullPath)) {
    //                 $mediaFiles[] = [
    //                     'fileName' => $fullPath,
    //                     'fileType' => $fileType
    //                 ];
    //             }
    //         }
    //     }
    // }
    //$mapFileName = 'boop';


    // 4. Заполняем таблицы данными
    mysqli_query($connection, 
        "INSERT INTO `market-info` 
        (`name`,`description`,`openingDate`,`closingDate`,`openingTime`,`closingTime`,`address`,`ticketPrice`,`pictureFileName`,`tables`)
        VALUES
        ('$marketName', '$description', '$openingDate', '$closingDate', '$openingTime', '$closingTime', '$address', '$ticketPrice', '$fullCoverPath', '$tables');" );

    mysqli_query($connection, 
        "INSERT INTO `market-members` 
        (`email`,`role`,`status`,`date`)
        VALUES
        ('$email', 'manager', 'Участвует', '$date');" 
    );

    // 5. Указывает менеджером
    if (mysqli_num_rows(mysqli_query($connection, "SELECT * FROM market.`user-roles` WHERE email = '$email' AND `role` = 'manager'")) == 0) {
        mysqli_query($connection, "INSERT INTO `market`.`user-roles` (`email`,`role`,`date`)
        VALUES ('$email', 'manager', '$date')
        ");
    }


    // 4. Заполнение таблицы market-days
    $startDate = new DateTime($openingDate);
    $endDate = new DateTime($closingDate);
    $capacity = $ticketPrice; // Вместимость берем из поля ticketPrice

    // Создаем период между датами
    $interval = new DateInterval('P1D'); // 1 день интервал
    $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day')); // +1 день чтобы включить конечную дату

    // Добавляем каждый день в таблицу
    foreach ($period as $date) {
        $dateStr = $date->format('Y-m-d');
        mysqli_query($connection, 
            "INSERT INTO `market-days` 
            (`date`, `capacity`)
            VALUES
            ('$dateStr', '$capacity')");
    }




    // for ($i = 0; $i < count($invitedSellers); $i++) {
    //     mysqli_query($connection, 
    //     "INSERT INTO `market-members` 
    //     (`email`,`role`,`status`,`date`)
    //     VALUES
    //     ('$invitedSellers[$i]', 'seller', 'Приглашение отправлено', '$date');" ); 
    // }

    // Array ( 
    //     [0] => Array ( [fileName] => girl.jpg [fileType] => image ) 
    //     [1] => Array ( [fileName] => 02.23.jpg [fileType] => image ) 
    // )
    // for ($i = 0; $i < count($mediaFiles); $i++) {
    //     $n = $mediaFiles[$i]['fileName'];
    //     $t = $mediaFiles[$i]['fileType'];
    //     mysqli_query($connection, 
    //         "INSERT INTO `media` 
    //         (`fileName`,`type`,`uploadDate`)
    //         VALUES
    //         ('$n', '$t', '$date');" );
    // }


    mysqli_close($connection);

    include 'returnToMarket.php';

    function translit($str) {
        $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return str_replace($russian, $translit, $str);
    }
?>