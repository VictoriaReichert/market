<?php
    session_start();
    //print_r($_SESSION);
    header("Location: /market/market-page.php?name=".$_SESSION['currentMarket']);
    exit();
?>