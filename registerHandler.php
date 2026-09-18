<?php
    session_start();

    $conf = parse_ini_file('config/config.ini');

    // Подключение к базе данных
    $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password'], $conf['database']);
    if (!$connection) {
        $_SESSION['errorReg'] = 'Ошибка подключения к базе данных';
        header("Location: register.php");
        exit;
    }

    // Получение данных
    $login = mysqli_real_escape_string($connection, $_POST['login']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = $_POST['password'];
    date_default_timezone_set('Europe/Moscow');
    $date = date('Y-m-d');

    // Проверка email
    $result = mysqli_query($connection, "SELECT * FROM `users` WHERE email = '$email'");
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['errorReg'] = 'Пользователь с такой почтой уже существует';
        header("Location: register.php");
        exit;
    }

    // Проверка логина
    $result = mysqli_query($connection, "SELECT * FROM `users` WHERE `login` = '$login'");
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['errorReg'] = 'Пользователь с таким логином уже существует';
        header("Location: register.php");
        exit;
    }

    // Задание роли посетителя
    mysqli_query($connection, "INSERT INTO market.`user-roles`
            (email, `role`, `date`)
            VALUES
            ('$email', 'visitor', '$date')");
    // Создание пользователя
    $query = "INSERT INTO `users` 
            (`email`, `password`, `login`, registrationDate) 
            VALUES 
            ('$email', '$password', '$login', '$date')";

    if (mysqli_query($connection, $query)) 
    {
        // Автоматический вход после регистрации
        setcookie("logIn", $email, time() + $conf['cookieLifeTime']);
        header("Location: ".$conf['marketPageUrl']);
        
    } 
    else 
    {
        $_SESSION['errorReg'] = 'Ошибка при регистрации: ' . mysqli_error($connection);
        header("Location: register.php");
    }

    mysqli_close($connection);
?>
