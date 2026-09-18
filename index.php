<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" type="text/css" href="styles\index.css">
</head>
<body>

<?php
    session_start();
    //$_SESSION['currentRole'] = 'visitor'; 

    // Проверяем cookie (если есть, то сразу пускаем на главную)
    $conf = parse_ini_file('config/config.ini');
    if (isset($_COOKIE['logIn']))
    {
        $userEmail = $_COOKIE['logIn'];
        $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password'], $conf['database']); 
        $result = mysqli_query($connection, "SELECT * FROM `users` WHERE email like '$userEmail'");
        if (mysqli_num_rows($result) != 0) 
        {
            header("Location: ".$conf['marketPageUrl']);
            exit;
        }
        mysqli_close($connection);
    }
?>

    <div class="container">
        <div class="left-section">
            <div class="carousel">
                <img src="media/ogonek.jpg" alt="Ярмарка Огонёк" class="active">
                <img src="media/murket.jpg" alt="Ярмарка Муркет">
                <img src="media/hokku.jpg" alt="Ярмарка Хокку">
            </div>
            
            <div class="description">
                <h2>Все ярмарки - тут!</h2>
                <p>Вы можете ознакомиться с актуальной информацией о Ваших любимых ярмарках и их товарах.</p>
                <p>Создавайте страницы Ваших мероприятий, приглашайте продавцов и волонтёров
                     и сделайте Ваше мероприятие удобным для посетителей!</p>
            </div>
        </div>
        
        <div class="right-section">
            <div class="login-form">
                <h2>Вход</h2>
                
                <form action=authentication.php method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="email">Адрес электронной почты</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="show-password" onclick="togglePassword()">Показать</button>
                        </div>
                    </div>

                    <?php
                        if (isset($_SESSION['error'])) {
                            echo '<div class="error-message">'.htmlspecialchars($_SESSION['error']).'</div>';
                            unset($_SESSION['error']);
                        }
                    ?>

                    <button type="submit" class="login-btn" id="b">Войти</button>
                </form>
                
                <div class="register-section">
                    <p>Ещё нет аккаунта? <button class="register-btn" onclick="window.location.href='register.php'">Зарегистрироваться</button></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Карусель изображений
        let currentImage = 0;
        const images = document.querySelectorAll('.carousel img');
        
        function changeImage() {
            images[currentImage].classList.remove('active');
            currentImage = (currentImage + 1) % images.length;
            images[currentImage].classList.add('active');
        }
        
        setInterval(changeImage, 6000);
        
        // Показать/скрыть пароль
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const showButton = document.querySelector('.show-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showButton.textContent = 'Скрыть';
            } else {
                passwordInput.type = 'password';
                showButton.textContent = 'Показать';
            }
        }
    </script>
</body>
</html>
