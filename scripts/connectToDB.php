<?php
    $conf = parse_ini_file('config/config.ini');
    $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);
?>