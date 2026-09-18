<header>
    <div class="header-container">
        <a href="market.php" class="logo-link">
            <img src="media/иконка.png" alt="Логотип Ярмарки" class="logo">
        </a>
        
        <div class="sort-options">
        <form method="post" id="role-form">
            <select name="role" id="role-select" onchange="document.getElementById('role-form').submit()">
                <option value="visitor" <?= $currentUserRole == 'visitor' ? 'selected' : '' ?>>Посетитель</option>
                <?php if ($userRoles['seller']): ?>
                    <option value="seller" <?= $currentUserRole == 'seller' ? 'selected' : '' ?>>Продавец</option>
                <?php endif; ?>

                <?php if ($userRoles['manager']): ?>
                    <option value="manager" <?= $currentUserRole == 'manager' ? 'selected' : '' ?>>Организатор</option>
                <?php endif; ?>

                <?php if ($userRoles['volunteer']): ?>
                    <option value="volunteer" <?= $currentUserRole == 'volunteer' ? 'selected' : '' ?>>Волонтёр</option>
                <?php endif; ?>
            </select>
        </form>
        </div>

        <div class="user-menu">
            <div class="account-dropdown">
                <button class="account-btn">
                    <span>≡ <?= htmlspecialchars($userData['login'] ?? 'Кабинет') ?></span>
                </button>
                <div class="dropdown-content">
                    <a href="favourites.php"> Избранное</a>
                    <a href="managed-markets.php"> Мои ярмарки</a>
                    <a href="requests.php"> Запросы на участие</a>
                    <a href="managed-products.php"> Мои товары</a>
                    <a href="cart.php"> Корзина</a>
                    <a href="orders.php"> Активные заказы</a>
                    <a href="order-history.php"> История заказов</a>
                    <a href="logout.php"> Выйти</a>
                </div>
            </div>
        </div>
    </div>
</header>