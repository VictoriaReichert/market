<?php
    session_start();
    include 'checkAuth.php'; 
    
    $conf = parse_ini_file('../config/config.ini');
    $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);

    // Получаем данные из формы
    $productId = intval($_POST['productId']);
    $email = $_COOKIE['logIn'];
    $userLogin = lowerCase($_POST['userName']);
    $category = mysqli_real_escape_string($connection, $_POST['category']);
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $price = intval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $currentImage = $_POST['currentImage'];
    $imagePath = $currentImage;

    // Обработка загруженного изображения (если загружено новое)
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image'];
        $imageName = $image['name'];
        $newImagePath = 'marketSellersMedia/'.$userLogin."/";
        
        if (!file_exists("../".$newImagePath)) {
            mkdir("../".$newImagePath, 0777, true);
        }

        // Удаляем старое изображение, если оно существует
        if (file_exists("../".$currentImage)) {
            unlink("../".$currentImage);
        }

        $newImagePath .= $imageName;
        
        // Перемещаем новое изображение
        if (move_uploaded_file($image['tmp_name'], "../".$newImagePath)) {
            $imagePath = $newImagePath;
        }
    }

    // Обновляем данные товара
    $updateQuery = "UPDATE `market-catalogs`.`$userLogin` 
                    SET `type` = '$category', 
                        `name` = '$name', 
                        `description` = '$description', 
                        `price` = $price, 
                        `quantity` = $quantity, 
                        `pictureFileName` = '$imagePath'
                    WHERE id = $productId";
    
    mysqli_query($connection, $updateQuery);
    mysqli_close($connection);

    // Перенаправляем пользователя после успешного обновления
    header("Location: ../managed-products.php");
    exit();

    function lowerCase($str) {
        $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $translit = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        return str_replace($russian, $translit, $str);
    }
?>
