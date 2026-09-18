<?php
    // Проверяем cookie (если её нет, то выгоняем авторизироваться)
    $conf = parse_ini_file('D:\Programming\xampp\htdocs\market\config\config.ini');
    if (!isset($_COOKIE['logIn'])) 
        header("Location: ".$conf['indexPageUrl']);     
?>
