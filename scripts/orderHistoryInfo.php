<?php if (mysqli_num_rows($orders) > 0): ?>
    <?php
    // Группируем заказы по ярмаркам
    $ordersByMarket = [];
    while ($order = mysqli_fetch_assoc($orders)) {
        $ordersByMarket[$order['marketDBName']][] = $order;
    }
    
    foreach ($ordersByMarket as $marketDB => $marketOrders): 
        $marketName = mysqli_fetch_assoc(mysqli_query($connection, 
            "SELECT `name` FROM `$marketDB`.`market-info`"))['name'];
        // Вычисляем общую сумму для текущей ярмарки
        $totalMarketSum = 0;
        foreach ($marketOrders as $order) {
            $totalMarketSum += $order['cost'];
        }
    ?>
        <div class="market-group">
            <div class="market-header-history"><?= htmlspecialchars($marketName) ?> (<?= htmlspecialchars($roleStr) ?>)</div>
            
            <?php foreach ($marketOrders as $order): ?>
                <div class="order">
                    <div class="order-header">
                        <div>
                            <strong>Заказ #<?= $order['id'] ?> (<?= $order['type'] ?>)</strong>
                            <span>от <?= $order['date'] ?> <?= $order['time'] ?></span>
                        </div>
                        <span class="order-status status-<?= $order['status'] ?>">
                            <?= $order['status'] ?>
                        </span>
                    </div>
                    
                    <div class="order-details">
                        <?php
                        $items = mysqli_query($connection, 
                            "SELECT * FROM `market`.`order-items` 
                                WHERE `orders-id` = ".$order['id']);
                        
                        while ($item = mysqli_fetch_assoc($items)): 
                            if ($isSeller && $item['sellerName'] != $userData['login']) continue;
                        ?>
                            <div class="order-item">
                                <div>
                                    <?= htmlspecialchars($item['name']) ?>
                                    <?= $isManager ? ' (Продавец: '.htmlspecialchars($item['sellerName']).')' : '' ?>
                                </div>
                                <div>
                                    <?= $item['quantity'] ?> × <?= $item['price'] ?> руб.
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <div class="order-total">
                            Итого: <?= $order['cost'] ?> руб.
                        </div>
                        <div class="order-actions">
                            <a href="order-details.php?id=<?= $order['id'] ?>" class="action-btn" target="_blank">Детали заказа</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Добавляем общую сумму для ярмарки -->
            <div class="market-total">
                Общая сумма по <?= htmlspecialchars($roleStr == 'организатор' ? 'ярмарке' : ($roleStr == 'продавец' ? 'вашим товарам' : 'вашим заказам')) ?>: 
                <strong><?= $totalMarketSum ?> руб.</strong>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Нет заказов для отображения.</p>
<?php endif; ?>