<?php // Получаем список ярмарок, на которых пользватель $email является организатором
    $userLogin = $userData['login'];
    $tableNameInc = "temp.`".$userLogin."_inc_req`"; // Запросы от ПОЛЬЗОВАТЕЛЕЙ на ярмарки
    $tableNameOut = "temp.`".$userLogin."_out_req`"; // Приглашения от ЯРМАРОК до пользователей

    // 0. Получаем необходимые данные
    date_default_timezone_set('Europe/Moscow');
    $date = date('Y-m-d');
    $time = date('H:i:s');

    // Создаём временные таблицы
    mysqli_query($connection, "DROP TABLE IF EXISTS $tableNameOut");
    $table = "CREATE TABLE $tableNameOut
    (
        id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        senderDB VARCHAR(45) NOT NULL,
        marketName VARCHAR(45) NOT NULL,
        managerEmail VARCHAR(100) NOT NULL,
        receiverEmail VARCHAR(45) NOT NULL,
        `role` VARCHAR(45) NOT NULL,
        `status` VARCHAR(45) NOT NULL,
        `date` DATE NOT NULL
    )";
    mysqli_query($connection, $table);

    mysqli_query($connection, "DROP TABLE IF EXISTS $tableNameInc");
    $table = "CREATE TABLE $tableNameInc
    (
        id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        senderEmail VARCHAR(45) NOT NULL,
        receiverDB VARCHAR(45) NOT NULL,
        marketName VARCHAR(45) NOT NULL,
        managerEmail VARCHAR(100) NOT NULL,
        `role` VARCHAR(45) NOT NULL,
        `status` VARCHAR(45) NOT NULL,
        `date` DATE NOT NULL
    )";
    mysqli_query($connection, $table);


    // Приглашение отправлено - отправила ярмаркА
    // Запрос отправлен - отправил продавец/волонтёр ярмаркЕ
    // Участвует - мемберс


    // 1. Подключаемся к каждой ярмарке (бд)
    $dbNames = mysqli_query($connection, "SELECT `database-name` FROM `market`.`all-existing-markets`");
    while($marketDBName = mysqli_fetch_assoc($dbNames)):
        // Подготовка данных
        $n = "`".$marketDBName['database-name']."`"; // для запроса к бд $n.`t`
        $h = $marketDBName['database-name']; // для заполнения таблицы
        $marketName = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM $n.`market-info`"))['name'];
        $managerEmail = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM $n.`market-members` WHERE `role` = 'manager'"))['email'];

        // 2.1 В конкретной ярмарке проверяем ИСХОДЯЩИЕ приглашения на участие
        $reqInfo = mysqli_query($connection, "SELECT * FROM $n.`requests-outgoing`");
        while($req = mysqli_fetch_assoc($reqInfo)):
            $emailReq = $req['receiverEmail'];
            $roleReq = $req['role'];
            $statusReq = $req['status'];
            $dateReq = $req['date'];

            mysqli_query($connection, "INSERT INTO $tableNameOut (senderDB, marketName, managerEmail, receiverEmail, `role`, `status`, `date`)
            VALUES
            ('$h', '$marketName', '$managerEmail', '$emailReq', '$roleReq', '$statusReq', '$dateReq')
            ");
        endwhile;

        // 2.2 В конкретной ярмарке проверяем ВХОДЯЩИЕ запросы на участие
        $reqInfo = mysqli_query($connection, "SELECT * FROM $n.`requests-incoming`");
        while($req = mysqli_fetch_assoc($reqInfo)):
            $emailReq = $req['senderEmail'];
            $roleReq = $req['role'];
            $statusReq = $req['status'];
            $dateReq = $req['date'];

            mysqli_query($connection, "INSERT INTO $tableNameInc (senderEmail, receiverDB, marketName, managerEmail, `role`, `status`, `date`)
            VALUES
            ('$emailReq', '$h', '$marketName', '$managerEmail', '$roleReq', '$statusReq', '$dateReq')
            ");
        endwhile;
    endwhile;

?>