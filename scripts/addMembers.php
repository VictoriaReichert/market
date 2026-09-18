<?php
    session_start();
    include 'checkAuth.php'; 

    $conf = parse_ini_file('../config/config.ini');
    $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);
    $db = "`".$_SESSION['currentMarketDB']."`";
    mysqli_query($connection, "USE $db");

    // 0. Получаем необходимые данные
    $email = $_COOKIE['logIn'];
    date_default_timezone_set('Europe/Moscow');
    $date = date('Y-m-d');
    $time = date('H:i:s');
    // Из формы
    // $marketName_tr = translit($_POST['marketName']);
    // $dbName = "market-manager-db_".$marketName_tr;
    // $marketName = mysqli_real_escape_string($connection, $_POST['marketName']);
    $invitedSellers = isset($_POST['invitedSellers']) ? explode(',', $_POST['invitedSellers']) : [];
    $invitedVolunteers = isset($_POST['invitedVolunteers']) ? explode(',', $_POST['invitedVolunteers']) : [];


    // Фильтруем пустые email
    $invitedSellers = array_filter($invitedSellers, function($email) {
        return !empty(trim($email));
    });

    $invitedVolunteers = array_filter($invitedVolunteers, function($email) {
        return !empty(trim($email));
    });

    // 4. Заполняем таблицы данными
    for ($i = 0; $i < count($invitedSellers); $i++) {
        mysqli_query($connection, 
        "INSERT INTO `requests-outgoing` 
        (`receiverEmail`,`role`,`status`,`date`)
        VALUES
        ('$invitedSellers[$i]', 'seller', 'Приглашение отправлено', '$date');" ); 
    }
    for ($i = 0; $i < count($invitedVolunteers); $i++) {
        mysqli_query($connection, 
        "INSERT INTO `requests-outgoing` 
        (`receiverEmail`,`role`,`status`,`date`)
        VALUES
        ('$invitedVolunteers[$i]', 'volunteer', 'Приглашение отправлено', '$date');" ); 
    }


    mysqli_close($connection);

    include 'returnToMarket.php';

    function translit($str) {
        $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return str_replace($russian, $translit, $str);
    }
?>