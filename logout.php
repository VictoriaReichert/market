<?php
    $conf = parse_ini_file('config/config.ini');
    setcookie("logIn", "", time() - 1);
    //include 'scripts\checkAuth.php'; 
    header("Location: ".$conf['indexPageUrl']);   
?>