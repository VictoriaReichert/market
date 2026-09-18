<?php
session_start();
include 'checkAuth.php';
$conf = parse_ini_file('../config/config.ini');
$connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $market_db = mysqli_real_escape_string($connection, $_POST['market_db']);
    $marketName = mysqli_real_escape_string($connection, $_POST['market_name']);
    //$seller_email = mysqli_real_escape_string($connection, $_POST['seller_email']);
    $seller_name = mysqli_real_escape_string($connection, $_POST['seller_name']);
    $product_id = (int)$_POST['product_id'];
    //$product_name = mysqli_real_escape_string($connection, $_POST['product_name']);
    //$product_price = (float)$_POST['product_price'];
    $user_email = mysqli_real_escape_string($connection, $_POST['user_email']);
    $quantity = (int)$_POST['quantity']; // сколько хотят добавить
    date_default_timezone_set('Europe/Moscow');
    $date = date('Y-m-d');
    $prodQuantity = (int)$_POST['cur_quantity']; // сколько ост у продавца


    // Проверяем, есть ли уже такой товар в корзине
    $check_query = "SELECT * FROM market.`cart` 
                    WHERE userEmail = '$user_email' 
                    AND marketDBName = '$market_db' 
                    AND `product-id` = $product_id
                    AND sellerName = '$seller_name'";
    $result = mysqli_query($connection, $check_query);

    
    if (mysqli_num_rows($result) > 0) { // Если товар уже в корзине
        $cartQuantity = mysqli_fetch_assoc($result)['quantity']; // сколько уже в корзине
        echo $prodQuantity.'>='. $quantity.'+'.$cartQuantity.'<br>';
        if ($prodQuantity >= $quantity + $cartQuantity) { // Если не превысит количество
            echo 'да';
            // Обновляем количество, если товар уже в корзине
            $quantity = $quantity + $cartQuantity;
            $update_query = "UPDATE `market`.`cart`
                            SET quantity = $quantity 
                            WHERE userEmail = '$user_email' 
                            AND marketDBName = '$market_db' 
                            AND `product-id` = $product_id";
            mysqli_query($connection, $update_query);
        } else {
            echo 'нет';
            echo '<script>alert("Количество превысило допустимое")</script>';
            header("Location: ../market-products.php?name=" . urlencode($_SESSION['currentMarket']));
            exit();
        }
    } else {
        // Добавляем новый товар в корзину
        $insert_query = "INSERT INTO `market`.`cart`
                        (marketDBName, marketName, sellerName, `product-id`, userEmail, quantity, `date`) 
                         VALUES 
                        ('$market_db', '$marketName', '$seller_name', $product_id, '$user_email', $quantity, '$date')";
        mysqli_query($connection, $insert_query);
    }
    
    //Перенаправляем обратно на страницу товаров
    header("Location: ../market-products.php?name=" . urlencode($_SESSION['currentMarket']));
    exit();
}
?>