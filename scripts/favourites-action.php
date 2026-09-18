<?php
    session_start();
    include 'checkAuth.php'; 

    $conf = parse_ini_file('../config/config.ini');
    $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);
    mysqli_query($connection, "USE market");

    $email = $_COOKIE['logIn'];
    $marketName = $_SESSION['currentMarket'];
    $marketDb = $_SESSION['currentMarketDB'];

    if ($_SESSION['favAction'] == 'add')
        mysqli_query($connection, "INSERT INTO `user-favourites` (`email`, `market-name`, `database-name`) VALUES ('$email', '$marketName', '$marketDb')");
    else if ($_SESSION['favAction'] == 'delete')
        mysqli_query($connection, "DELETE FROM `user-favourites` WHERE email='$email' AND `market-name` = '$marketName';");

    header("Location: ".$conf['singleMarketPageUrl']."?name=".$marketName);
    exit();
?>