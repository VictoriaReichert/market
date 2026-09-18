<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои товары</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/market-page.css">
    <link rel="stylesheet" href="styles/products.css">
</head>
<body>
    <?php
        session_start();
        include 'scripts/checkAuth.php';
        include 'scripts/connectToDB.php';
        include 'scripts/getLogin.php';
        include 'header.php';

        // Обработка удаления
        if (isset($_GET['table'])) {
            $table = $_GET['table'];
            $id = $_GET['id'];
            $pic = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `market-catalogs`.$table WHERE id = $id"))['pictureFileName'];
            if (file_exists($pic)) unlink($pic);

            mysqli_query($connection, "DELETE FROM `market-catalogs`.$table WHERE id = $id"); // из каталога продавца
            mysqli_query($connection, "DELETE FROM market.cart WHERE `product-id` = $id"); // из корзин
            


            // Проверяем, остались ли товары у продавца
            $checkSeller = mysqli_query($connection, "SELECT * FROM `market-catalogs`.$table");
            if (mysqli_num_rows($checkSeller) == 0) { // сё, не продавец
                mysqli_query($connection, "DELETE FROM market.`user-roles` WHERE `role` = 'seller' AND email = '$email'");
                mysqli_query($connection, "DROP TABLE `market-catalogs`.$table");
                mysqli_query($connection, "DELETE FROM `market-catalogs`.`user-catalogs` WHERE email = '$email'");

                $mediaDir = "marketSellersMedia/".lowerCase($table);
                if (file_exists($mediaDir)) {
                    array_map('unlink', glob("$mediaDir/*.*"));
                    rmdir($mediaDir);
                }
            }

            header("Location: managed-products.php");
            exit();
        }

    ?>

    <main>
        <div class="products-container">
            <div class="products-header">
                <h1>Мои товары</h1>
                <a href="add-product.php" class="add-product-btn">Добавить товар</a>
            </div>

            <div class="filters-container">
                <form method="GET" class="search-form">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Поиск по названию товара..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    
                    <div class="sort-options">
                        <label for="sort-select">Сортировка:</label>
                        <select name="sort" id="sort-select" onchange="this.form.submit()">
                            <option value="name" <?= ($_GET['sort'] ?? '') == 'name' ? 'selected' : '' ?>>По названию (А-Я)</option>
                            <option value="name DESC" <?= ($_GET['sort'] ?? '') == 'name DESC' ? 'selected' : '' ?>>По названию (Я-А)</option>
                            <option value="price" <?= ($_GET['sort'] ?? '') == 'price' ? 'selected' : '' ?>>По цене (возрастание)</option>
                            <option value="price DESC" <?= ($_GET['sort'] ?? '') == 'price DESC' ? 'selected' : '' ?>>По цене (убывание)</option>
                            <option value="created_at DESC" <?= ($_GET['sort'] ?? '') == 'date DESC' ? 'selected' : '' ?>>Сначала новые</option>
                        </select>
                    </div>
                </form>
            </div>

            <?php
                // Получаем название таблицы товаров пользователя
                $result2 = mysqli_query($connection, "SELECT * FROM `market-catalogs`.`user-catalogs` WHERE email = '$email'");
                if (mysqli_num_rows($result2) == 0) {
                    echo '<div class="no-products">У вас нет товаров.'; // <a href="add-product.php">Добавить товар</a></div>
                } else {
                    $user2 = mysqli_fetch_assoc($result2);
                    $tableName = $user2['tableName'];

                    // Получаем товары продавца
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date DESC';
                    
                    mysqli_query($connection, "USE `market-catalogs`");
                    $query = "SELECT * FROM `$tableName` WHERE 1=1";
                    if ($search) $query .= " AND name LIKE '%$search%'";
                    $query .= " ORDER BY $sort";
                    $products = mysqli_query($connection, $query);
                    
                    if (mysqli_num_rows($products) == 0) {
                        echo '<div class="no-products">У вас нет товаров. </div>'; //<a href="add-product.php">Добавить товар</a>
                    } else {
                        echo '<div class="products-grid">';
                        while ($product = mysqli_fetch_assoc($products)) {
                            echo '
                            <div class="product-card">
                                <div class="product-image-container">
                                    <img src="'.htmlspecialchars($product['pictureFileName']).'" alt="'.htmlspecialchars($product['name']).'" class="product-image">
                                    <div class="product-actions">
                                        <a href="edit-product.php?id=' . $product['id'] . '&market=' .  '" class="edit-btn"><i class="fas fa-edit"></i></a>';

                            // Проверяем что ни у кого это не забронировано-----------------------------------------------------------------
                            // id, orders-id, sellerName, type, name, description, price, quantity, pictureFileName
                            $sellerName = lowerCase($tableName);
                            $checkOrders = mysqli_query($connection, "SELECT * FROM market.`order-items` i JOIN market.orders o ON i.`orders-id` = o.id 
                                WHERE i.sellerName = '$sellerName'
                                AND i.`type` = '".$product['type']."'
                                AND i.`name` = '".$product['name']."'
                                AND i.pictureFileName = '".$product['pictureFileName']."'
                                AND o.status != 'завершён'
                                AND o.status != 'отменён продавцом'
                                AND o.status != 'отменён покупателем'
                                ");
                            if (mysqli_num_rows($checkOrders) == 0) {
                                echo '<a href="managed-products.php?table='.$tableName.'&id='.$product['id'].'" class="delete-btn" ><i class="fas fa-trash"></i></a>';
                            }
                            echo '
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3>'.htmlspecialchars($product['type']).' "'.htmlspecialchars($product['name']).'"</h3>
                                    <p class="product-description">'.htmlspecialchars($product['description']).'</p>
                                    <p class="product-price">' . $product['price'] . ' руб.</p>
                                    <p class="product-seller">Остаток: '.$product['quantity'].'</p>
                                </div>
                            </div>';
                        } // onclick="return confirm(\'Вы уверены, что хотите удалить этот товар?\')"
                        echo '</div>';
                    }
                }
                mysqli_close($connection);
            ?>
        </div>
    </main>

<?php
        function lowerCase($str) {
            $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
            $translit = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        return str_replace($russian, $translit, $str);
    }
?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        // Открытие/закрытие выпадающего меню
        document.querySelector('.account-btn').addEventListener('click', function() {
            this.parentElement.classList.toggle('active');
        });

        // Закрытие меню при клике вне его
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.account-dropdown')) {
                document.querySelector('.account-dropdown').classList.remove('active');
            }
        });
    </script>
</body>
</html>
