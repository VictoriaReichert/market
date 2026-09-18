<?php // Получаем список ярмарок, на которых пользватель $email является волонтёром
    $userLogin = $userData['login'];
    $tableName = "temp.`".$userLogin."_vol`";

    mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");
    $table = "CREATE TABLE $tableName
    (
        id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` varchar(45) NOT NULL,
        managerEmail varchar(100),
        `description` varchar(600) NOT NULL,
        openingDate datetime NOT NULL,
        closingDate datetime NOT NULL,
        `address` varchar(300) NOT NULL,
        ticketPrice varchar(45) NOT NULL,
        pictureFileName varchar(300) NOT NULL,
        mapFileName varchar(300),
        `database-name` varchar(45)
    )";
    $query1 = "SELECT `database-name` FROM market.`all-existing-markets`";

    mysqli_query($connection, $table);
    $dbNames = mysqli_query($connection, $query1);

    while($marketDBName = mysqli_fetch_assoc($dbNames)):
        $n = "`".$marketDBName['database-name']."`";
        $h = $marketDBName['database-name'];

        $v = mysqli_query($connection, "SELECT email FROM $n.`market-members` WHERE `role` = 'volunteer' AND email = '$email'");
        if (mysqli_num_rows($v) > 0) {
            $vol = mysqli_fetch_assoc($v);
            if ($email == $vol['email']) {
                mysqli_query($connection, "INSERT INTO $tableName (`name`, managerEmail, `description`, openingDate, closingDate, `address`, ticketPrice, pictureFileName, mapFileName, `database-name`)
                SELECT `name`, email, `description`, openingDate, closingDate, `address`, ticketPrice, pictureFileName, mapFileName, '$h'
                FROM $n.`market-info` JOIN $n.`market-members` AS m ON  m.`role` = 'manager';");
            }
        }
    endwhile;

    $query = "SELECT * FROM $tableName WHERE 1=1";
    if (!$showPast) 
        $query .= " AND closingDate >= NOW()";
    $query .= " ORDER BY $sort";

    $allMarketsInfo = mysqli_query($connection, $query);
?>