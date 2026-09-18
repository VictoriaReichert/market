<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать заказ</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            padding: 10px;
            width: 200px;
            cursor: pointer;
        }
        .product-card.selected {
            background-color: #e0f7fa;
            border-color: #4dd0e1;
        }
        .order-items {
            margin: 20px 0;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        .order-item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-input {
            width: 50px;
            text-align: center;
        }
        .product-thumbnail {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .product-card {
            width: 180px; /* Уменьшил ширину для компактности */
            text-align: center;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    include 'scripts/checkAuth.php';
    include 'scripts/connectToDB.php';
    include 'scripts/getLogin.php';
    
    // Проверяем, что пользователь - продавец и выбрана ярмарка
    if (!isset($_SESSION['currentMarketDB'])) {
        header("Location: market.php");
        exit();
    }
    
    $marketDB = $_SESSION['currentMarketDB'];
    $sellerName = $userData['login'];
    $marketName = mysqli_fetch_assoc(mysqli_query($connection, 
        "SELECT `name` FROM `$marketDB`.`market-info`"))['name'];
    
// Обработка создания заказа -------------------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_order'])) {
        $items = json_decode($_POST['items'], true);
        
        // Создаем запись в таблице orders
        date_default_timezone_set('Europe/Moscow');
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $buyer = 'посетитель'; // Покупатель - посетитель
        $type = 'составлен продавцом';
        $status = 'завершён';
        $cost = 0;

        $sellerEmail = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM market.users WHERE `login` = '$sellerName'"))['email'];
        $tableNumber = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `$marketDB`.`market-members` WHERE email = '$sellerEmail' AND `role` = 'seller'"))['tableNumber'];
        
        
        // Сначала вычисляем общую стоимость
        foreach ($items as $item) {
            $product = mysqli_fetch_assoc(mysqli_query($connection, 
                "SELECT * FROM `market-catalogs`.`$sellerName` WHERE id = ".(int)$item['id']));
            $cost += $product['price'] * (int)$item['quantity'];
        }
        
        // Создаем заказ
        mysqli_query($connection, 
            "INSERT INTO `market`.`orders` (`type`, `marketDBName`, tableNumber, `buyer`, `cost`, `date`, `time`, `status`)
             VALUES ('$type', '$marketDB', $tableNumber, '$buyer', $cost, '$date', '$time', '$status')");
        
        $orderId = mysqli_insert_id($connection);
        
        // Добавляем товары в order-items
        foreach ($items as $item) {
            $product = mysqli_fetch_assoc(mysqli_query($connection, 
                "SELECT * FROM `market-catalogs`.`$sellerName` WHERE id = ".(int)$item['id']));
            
            mysqli_query($connection, 
                "INSERT INTO `market`.`order-items` 
                (`orders-id`, `sellerName`, `type`, `name`, `description`, `price`, `quantity`, `pictureFileName`)
                VALUES (
                    $orderId, 
                    '$sellerName', 
                    '".mysqli_real_escape_string($connection, $product['type'])."', 
                    '".mysqli_real_escape_string($connection, $product['name'])."', 
                    '".mysqli_real_escape_string($connection, $product['description'])."', 
                    ".$product['price'].", 
                    ".(int)$item['quantity'].", 
                    '".mysqli_real_escape_string($connection, $product['pictureFileName'])."'
                )");

            // Уменьшаем количество товара
            mysqli_query($connection, 
                "UPDATE `market-catalogs`.`$sellerName` 
                 SET quantity = quantity - ".(int)$item['quantity']." 
                 WHERE id = ".$item['id']);
        }
        
        // header("Location: orders.php");
        // exit();
    }
// Обработка создания заказа -------------------------------------------------------------------------
    
    // Получаем товары продавца
    $products = mysqli_query($connection, "SELECT * FROM `market-catalogs`.`$sellerName`");
    
    include 'header.php';
    ?>

    <main class="create-order-container">
        <h1>Создать заказ (<?= htmlspecialchars($marketName) ?>)</h1>
        
        <form id="order-form" method="POST" action="create-order.php">
            <h2>Выберите товары</h2>
            <div class="product-list">
                <?php while ($product = mysqli_fetch_assoc($products)): ?>
                    <div class="product-card" 
                        data-id="<?= $product['id'] ?>"
                        data-name="<?= htmlspecialchars($product['name']) ?>"
                        data-price="<?= $product['price'] ?>">
                        <img src="<?= htmlspecialchars($product['pictureFileName']) ?>" 
                            alt="<?= htmlspecialchars($product['name']) ?>" 
                            class="product-thumbnail">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p>Цена: <?= $product['price'] ?> руб.</p>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <h2>Состав заказа</h2>
            <div class="order-items" id="order-items">
                <!-- Товары будут добавляться сюда -->
                <p id="no-items">Нет выбранных товаров</p>
            </div>
            
            <div class="order-total">
                <strong>Итого: </strong><span id="total-price">0</span> руб.
            </div>
            
            <input type="hidden" name="create_order" value="1">
            <input type="hidden" name="items" id="order-items-data" value="">
            
            <button type="submit" class="submit-btn" >Оформить заказ</button>
            <a href="market-products.php?name=<?php echo $marketName ?>"><p>Отмена</p></a>
        </form>
    </main>

    <script src='scripts\functions.js'></script>
    <script>
        // Хранилище выбранных товаров
        let selectedItems = [];
        let totalPrice = 0;
        
        // Обработка выбора товара
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                const productId = this.dataset.id;
                const productName = this.dataset.name;
                const productPrice = parseInt(this.dataset.price);
                 const productImage = this.querySelector('img').src.split('/').pop(); // Получаем имя файла
                
                // Проверяем, есть ли уже такой товар в заказе
                const existingItem = selectedItems.find(item => item.id == productId);
                
                if (existingItem) {
                    // Увеличиваем количество
                    existingItem.quantity += 1;
                    updateItemInList(existingItem);
                } else {
                    // Добавляем новый товар
                    const newItem = {
                        id: productId,
                        name: productName,
                        price: productPrice,
                        quantity: 1,
                        pictureFileName: productImage // Добавляем имя файла изображения
                    };
                    selectedItems.push(newItem);
                    addItemToList(newItem);
                }
                
                updateTotalPrice();
                updateHiddenInput();
                toggleNoItemsMessage();
            });
        });
        
        // Добавление товара в список
        function addItemToList(item) {
            const itemsContainer = document.getElementById('order-items');
            const noItemsMsg = document.getElementById('no-items');
            
            if (noItemsMsg) noItemsMsg.remove();
            
            const itemElement = document.createElement('div');
            itemElement.className = 'order-item';
            itemElement.id = `item-${item.id}`;
            itemElement.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div>
                        <strong>${item.name}</strong><br>
                        ${item.price} руб. × 
                        <input type="number" class="quantity-input" value="${item.quantity}" min="1" 
                            onchange="updateQuantity(${item.id}, this.value)">
                    </div>
                </div>
                <div class="order-item-controls">
                    <span class="item-total">${item.price * item.quantity} руб.</span>
                    <button type="button" onclick="removeItem(${item.id})">×</button>
                </div>
            `;
            
            itemsContainer.appendChild(itemElement);
        }
        
        // Обновление товара в списке
        function updateItemInList(item) {
            const itemElement = document.getElementById(`item-${item.id}`);
            if (itemElement) {
                itemElement.querySelector('.quantity-input').value = item.quantity;
                itemElement.querySelector('.item-total').textContent = `${item.price * item.quantity} руб.`;
            }
        }
        
        // Удаление товара из списка
        function removeItem(productId) {
            selectedItems = selectedItems.filter(item => item.id != productId);
            const itemElement = document.getElementById(`item-${productId}`);
            if (itemElement) itemElement.remove();
            
            updateTotalPrice();
            updateHiddenInput();
            toggleNoItemsMessage();
        }
        
        // Обновление количества товара
        function updateQuantity(productId, newQuantity) {
            newQuantity = parseInt(newQuantity) || 1;
            
            const item = selectedItems.find(item => item.id == productId);
            if (item) {
                item.quantity = newQuantity;
                updateItemInList(item);
                updateTotalPrice();
                updateHiddenInput();
            }
        }
        
        // Обновление общей стоимости
        function updateTotalPrice() {
            totalPrice = selectedItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            document.getElementById('total-price').textContent = totalPrice;
        }
        
        // Обновление скрытого поля формы
        function updateHiddenInput() {
            document.getElementById('order-items-data').value = JSON.stringify(selectedItems);
        }
        
        // Показать/скрыть сообщение "Нет товаров"
        function toggleNoItemsMessage() {
            const itemsContainer = document.getElementById('order-items');
            const noItemsMsg = document.getElementById('no-items');
            
            if (selectedItems.length === 0) {
                if (!noItemsMsg) {
                    const msg = document.createElement('p');
                    msg.id = 'no-items';
                    msg.textContent = 'Нет выбранных товаров';
                    itemsContainer.appendChild(msg);
                }
            } else if (noItemsMsg) {
                noItemsMsg.remove();
            }
        }
        
        // Обработка отправки формы
        document.getElementById('order-form').addEventListener('submit', function(e) {
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Добавьте хотя бы один товар в заказ');
            }
        });
    </script>
</body>
</html>
