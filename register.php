<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" type="text/css" href="styles/index.css">
</head>
<body>
<?php
    session_start(); 
    // Проверяем cookie (если есть, то сразу пускаем на главную)
    $conf = parse_ini_file('config/config.ini');
    if (isset($_COOKIE['logIn'])) {
        $userEmail = $_COOKIE['logIn'];
        $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password'], $conf['database']); 
        $result = mysqli_query($connection, "SELECT * FROM `users` WHERE email like '$userEmail'");
        if (mysqli_num_rows($result) != 0) {
            header("Location: ".$conf['marketPageUrl']);
        }
        mysqli_close($connection);
    }
        
    // print_r($_COOKIE);
    // print_r($_SESSION);
     
?>

    <div class="container-reg">
    <div class="left-section">
        <div class="carousel">
            <img src="media/hokku.jpg" alt="Ярмарка Хокку" class="active">
            <img src="media/murket.jpg" alt="Ярмарка Муркет">
            <img src="media/ogonek.jpg" alt="Ярмарка Огонёк">
        </div>
        
    
        <div class="login-form">
            <h2>Регистрация</h2>
            
            <form id="registerForm" action="registerHandler.php" method="POST">
                <div class="form-group">
                    <label for="login">Логин*</label>
                    <input type="text" id="login" name="login" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Адрес электронной почты*</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль*</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required minlength="4">
                        <button type="button" class="show-password" onclick="togglePassword('password')">Показать</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Подтверждение пароля*</label>
                    <div class="password-container">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="show-password" onclick="togglePassword('confirm_password')">Показать</button>
                    </div>
                </div>
                
                
                <?php
                    if (isset($_SESSION['errorReg'])) {
                        echo '<div class="error-message" id="errorMessage">'.htmlspecialchars($_SESSION['errorReg']).'</div>';
                        //unset($_SESSION['errorReg']);
                    }  
                ?>
        
                
                <button type="submit" class="login-btn">Зарегистрироваться</button>
            </form>
            
            <div class="register-section">
                <p>Уже есть аккаунт? <button class="register-btn" onclick="window.location.href='index.php'">Войти</button></p>
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
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const showButton = passwordInput.nextElementSibling;
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            showButton.textContent = 'Скрыть';
        } else {
            passwordInput.type = 'password';
            showButton.textContent = 'Показать';
        }
    }



    // Валидация формы
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const errorElement = document.getElementById('errorMessage');
        
        // Сброс ошибок
        errorElement.textContent = '';
        errorElement.style.display = 'none';
        
        // Проверка паролей
        if (password !== confirmPassword) {
            e.preventDefault();
            errorElement.textContent = 'Пароли не совпадают';
            errorElement.style.display = 'block';
            document.getElementById('password').style.borderColor = 'red';
            document.getElementById('confirm_password').style.borderColor = 'red';
            return;
        }
        
        if (isset($_SESSION['errorReg'])) {
            e.preventDefault();
            errorElement.textContent = $_SESSION['errorReg'];
            errorElement.style.display = 'block';
            document.getElementById('password').style.borderColor = 'red';
            document.getElementById('confirm_password').style.borderColor = 'red';
            return;
        }
    });
    </script>
</body>
</html>
