<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_GET['seller']; ?></title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/market-page.css">
</head>
<body>
    <?php
    session_start();
    include 'scripts/checkAuth.php'; 
    include 'scripts/connectToDB.php';
    include 'scripts/getLogin.php';

    // Получаем параметры фильтрации и сортировки
    $search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';

    include 'scripts\getSellerProducts.php';
    include 'header.php';
    ?>

    <!-- Вкладки ярмарки (Маркет Товары Карта) -->
    <main class="single-market">
        <section class="markets-section">
            <h1>Товары продавца <?php echo $_GET['seller']; ?></h1>
        </section>
                <!-- <a href="market-map.php?name=<//?= $_SESSION['currentMarket']?>" >Назад к карте</a> -->

        <section class="market-products">
            <!-- Поиск Сорт Раскуп -->
            <form method="GET" class="search-form">
                <input type="hidden" name="name" value="<?= htmlspecialchars($marketInfo['name']) ?>">

                <div class="search-box">
                    <input type="text" name="search" placeholder="Поиск..." value="<?= htmlspecialchars($search) ?>">
                </div>

            </form>
            
            <div class="products-grid">
                <?php if (empty($sellerProducts)): ?>
                    <p class="no-products">У этого продавца пока нет товаров</p>
                <?php else: ?>

                    <?php foreach ($sellerProducts as $product): ?>
                        <div class="product-card">
                            <div class="product-image-container">
                                <img src="<?= htmlspecialchars($product['pictureFileName']) ?>" 
                                        alt="<?= htmlspecialchars($product['name']) ?>" 
                                        class="product-image">
                                <!-- ??? -->

                            </div>

                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['type']) ?> "<?= htmlspecialchars($product['name']) ?>"</h3>
                                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="product-price"><?= $product['price'] ?> руб.</p>
                                <p class="product-seller">Остаток: <?= htmlspecialchars($product['quantity']) ?></p>
                                <!-- <button class="add-to-cart-btn" data-product-id="<?= $product['id'] ?>">  --------------------------------------------------- 
                                    В корзину
                                </button> -->
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        
    </main>

    <script src='scripts\functions.js'></script>
</body>
</html>
