<?php
    // Обработка отправки отзыва
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
        $text = mysqli_real_escape_string($connection, $_POST['review_text']);
        $score = (int)$_POST['rating'];
        $marketName = $_GET['name'];
        $senderLogin = $userData['login'];
        date_default_timezone_set('Europe/Moscow');
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $marketDb = $_SESSION['currentMarketDB'];
        
        // Добавляем отзыв в таблицу reviews
        mysqli_query($connection, "INSERT INTO `$marketDb`.`reviews` 
                                 (senderLogin, text, score, date, time) 
                                 VALUES ('$senderLogin', '$text', $score, '$date', '$time')");
        
        header("Location: market-page.php?name=" . urlencode($marketName));
        exit();
    }

    if (isset($_GET['delete'])) {
        $delete = $_GET['delete'];
        $id = $_GET['id'];
        $marketDb = $_SESSION['currentMarketDB'];

        if ($delete == 1) {
            mysqli_query($connection, "DELETE FROM `$marketDb`.reviews WHERE id = '$id'");
        }

        header("Location: market-page.php?name=" . urlencode($marketName));
        exit();
    }

    
?>