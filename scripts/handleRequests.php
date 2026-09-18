<?php
    // Обработка принятия запроса
    if (isset($_GET['accept'])) {
        $accept = $_GET['accept'];
        date_default_timezone_set('Europe/Moscow');
        $date = date('Y-m-d');
        
        $marketDB = $_GET['market'];
        $senderEmail = $_GET['sender'];//-------------
        $role = $_GET['role'];
        $type = $_GET['type']; // 'inc' или 'out'


        if ($accept == 1) { // Принятие
            mysqli_query($connection, "INSERT INTO `$marketDB`.`market-members`
            (`email`,`role`,`status`,`date`)
            VALUES
            ('$senderEmail', '$role', 'Участвует', '$date')
            ");

            if ($type == 'inc') {
                mysqli_query($connection, "UPDATE `$marketDB`.`requests-incoming` 
                SET `status` = 'Принято', `date` = '$date'
                WHERE senderEmail = '$senderEmail' AND `role`='$role'
                ");
                // для волонтёра
                if ($role == 'volunteer') {
                    if (mysqli_num_rows(mysqli_query($connection, "SELECT * FROM market.`user-roles` WHERE email = '$senderEmail' AND `role` = 'volunteer'")) == 0) {
                        mysqli_query($connection, "INSERT INTO market.`user-roles` 
                                                (`email`,`role`,`date`)
                                                VALUES
                                                ('$senderEmail', '$role', '$date')");
                    }
                }
            } else if ($type == 'out') {
                mysqli_query($connection, "UPDATE `$marketDB`.`requests-outgoing` 
                SET `status` = 'Принято', `date` = '$date'
                WHERE receiverEmail = '$senderEmail' AND `role`='$role'
                ");
                // для волонтёра
                if ($role == 'volunteer') {
                    if (mysqli_num_rows(mysqli_query($connection, "SELECT * FROM market.`user-roles` WHERE email = '$senderEmail' AND `role` = 'volunteer'")) == 0) {
                        mysqli_query($connection, "INSERT INTO market.`user-roles` 
                                                (`email`,`role`,`date`)
                                                VALUES
                                                ('$senderEmail', '$role', '$date')");
                    }
                }
            }
        }
        else if ($accept == 0) { // Отклонение
            if ($type == 'inc') {
                mysqli_query($connection, "UPDATE `$marketDB`.`requests-incoming` 
                SET `status` = 'Отклонено', `date` = '$date'
                WHERE senderEmail = '$senderEmail' AND `role`='$role'
                ");
            } else if ($type == 'out') {
                mysqli_query($connection, "UPDATE `$marketDB`.`requests-outgoing` 
                SET `status` = 'Отклонено', `date` = '$date'
                WHERE receiverEmail = '$senderEmail' AND `role`='$role'
                ");
            }
        }
        else if ($accept == 2) { // Отозвано / Ок
            if ($type == 'inc') {
                mysqli_query($connection, "DELETE FROM `$marketDB`.`requests-incoming` 
                WHERE senderEmail = '$senderEmail' AND `role`='$role'
                ");
            } else if ($type == 'out') {
                mysqli_query($connection, "DELETE FROM `$marketDB`.`requests-outgoing` 
                WHERE receiverEmail = '$senderEmail' AND `role`='$role'
                ");
            }
        }

        
        header("Location: requests.php");
        exit();
    }

    // Обработка создания нового запроса
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_request'])) {
        $marketDB = mysqli_real_escape_string($connection, $_POST['market']);
        $role = mysqli_real_escape_string($connection, $_POST['role']);
        
        // Добавляем запрос в таблицу входящих запросов ярмарки
        date_default_timezone_set('Europe/Moscow');
        $date = date('Y-m-d');
        mysqli_query($connection, "INSERT INTO `$marketDB`.`requests-incoming` (senderEmail, `role`, `status`, `date`) 
                                    VALUES ('$email', '$role', 'Запрос отправлен', '$date')");
        
        header("Location: requests.php");
        exit();
    }
?>