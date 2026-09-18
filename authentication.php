<?php
    session_start();
    
    $conf = parse_ini_file('config/config.ini');

    $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password'], $conf['database']); // Подключились к базе данных маркета
    if (!$connection) {
        $_SESSION['error'] = 'Ошибка подключения к базе данных';
        header("Location: ".$conf['indexPageUrl']);
        exit;
    }

    $email = mysqli_real_escape_string($connection, $_POST["email"]); // Получили данные почты и пароля из формы
    $password = $_POST["password"];
    $result = mysqli_query($connection, "SELECT * FROM `users` WHERE email like '$email'");

    if (mysqli_num_rows($result) == 0) 
    {
        mysqli_close($connection);
        $_SESSION['error'] = 'Пользователя с такой почтой не существует';
        header("Location: ".$conf['indexPageUrl']);
        exit;
    } 
    else // Почта есть, проверяем пароль
    {
        $row = mysqli_fetch_assoc($result); 
        $dbPass = $row['password'];

        if ($password != $dbPass) // Ошибка. Неправильный пароль
        { 
            mysqli_close($connection);
            $_SESSION['error'] = 'Неправильный пароль';
            header("Location: ".$conf['indexPageUrl']);
            exit;
        } 
    }

    // Пользователь зашёл, ставим cookie и перенаправляем на нужный сайт ------------------------
    setcookie("logIn", $email, time() + $conf['cookieLifeTime']);
    mysqli_close($connection);
    header("Location: ".$conf['marketPageUrl']);
    exit;
?>
