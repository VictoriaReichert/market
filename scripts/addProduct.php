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
    // Получаем данные из формы
    $userLogin = lowerCase($_POST['userName']);
    $category = mysqli_real_escape_string($connection, $_POST['category']);
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $price = intval($_POST['price']);
    $quantity = intval($_POST['quantity']);

    // Обработка загруженного изображения
    $image = $_FILES['image'];
    $imageName = ($image['name']);
    $imagePath = 'marketSellersMedia/'.$userLogin."/";
    
    if (!file_exists("../".$imagePath)) mkdir("../".$imagePath, 0777, true);

    $imagePath .= $imageName;
    // Перемещаем загруженный файл
    if (!move_uploaded_file($image['tmp_name'], "../".$imagePath)) {
        die("Ошибка при загрузке изображения");
    }

    // Создаем уникальное имя для каталога товаров
    //$catalogName = 'catalog_' . uniqid();

    // Создаем таблицу в базе market-catalogs
    $createTableQuery = "CREATE TABLE IF NOT EXISTS `market-catalogs`.`$userLogin` (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `type` varchar(45) NOT NULL,
            `name` varchar(100) NOT NULL,
            `description` varchar(600) NOT NULL,
            `price` int unsigned NOT NULL,
            `quantity` int unsigned NOT NULL,
            `pictureFileName` varchar(300) NOT NULL,
            `date` date NOT NULL
        )";
    mysqli_query($connection, $createTableQuery);

    // Добавляем товар в созданную таблицу
    $insertProductQuery = "INSERT INTO `market-catalogs`.`$userLogin` 
        (`type`, `name`, `description`, price, quantity, pictureFileName, `date`)
        VALUES ('$category', '$name', '$description', $price, $quantity, '$imagePath', '$date')
    ";
    mysqli_query($connection, $insertProductQuery);

    // Задаём имя таблицы в списке таблиц всех продавцов
    $sellerTable = mysqli_query($connection, "SELECT * FROM `market-catalogs`.`user-catalogs` WHERE email = '$email'");
    if (mysqli_num_rows($sellerTable) == 0) {
        $insertCatalogQuery = "INSERT INTO `market-catalogs`.`user-catalogs` 
            (email, tableName, `date`)
            VALUES ('$email', '$userLogin', '$date')
        ";
        mysqli_query($connection, $insertCatalogQuery);
    }

    // Задаём пользователю роль продавца
    $userRole = mysqli_query($connection, "SELECT * FROM market.`user-roles` WHERE email = '$email' AND `role` = 'seller'");
    if (mysqli_num_rows($userRole) == 0) {
        mysqli_query($connection, "INSERT INTO market.`user-roles` 
            (email, `role`, `date`)
            VALUES ('$email', 'seller', '$date')
        ");
    }

    // Перенаправляем пользователя после успешного добавления
    header("Location: ../managed-products.php");
    exit();

    mysqli_close($connection);


    function translit($str) {
        $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return str_replace($russian, $translit, $str);
    }

    function lowerCase($str) {
        $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $translit = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        return str_replace($russian, $translit, $str);
    }
?>