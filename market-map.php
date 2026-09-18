<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_GET['name']; ?></title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/create-market.css">
    <link rel="stylesheet" href="styles/market-page.css">
</head>
<body>
    <?php
    session_start();
    include 'scripts/checkAuth.php'; 
    include 'scripts/connectToDB.php';
    include 'scripts/getLogin.php';
    
    $sort = 'id';
    $showPast = false;
    include 'scripts\getManagedMarkets.php';

    $marketName = $_GET['name'];
    $_SESSION['currentMarket'] = $marketName;
    $query = mysqli_fetch_assoc(mysqli_query($connection, "SELECT managerEmail FROM $tableName WHERE `name` = '$marketName'"));
    $managerEmail = $query['managerEmail'];

    // Получаем информацию о ярмарке
    $result2 = mysqli_query($connection, "SELECT `database-name` FROM $tableName WHERE name = '$marketName'");
    $row2 = mysqli_fetch_assoc($result2);
    $marketDb = $row2['database-name'];
    $_SESSION['currentMarketDB'] = $marketDb;

    // Получаем все возможные роли пользователя
    $result1 = mysqli_query($connection, "SELECT role FROM `user-roles` WHERE email = '$email'");
    $seller = mysqli_query($connection, "SELECT email FROM `$marketDb`.`market-members` WHERE email = '$email' AND `role` = 'seller' AND `status` = 'Участвует'");
    $vol = mysqli_query($connection, "SELECT email FROM `$marketDb`.`market-members` WHERE email = '$email' AND `role` = 'volunteer' AND `status` = 'Участвует'");
    while ($row = mysqli_fetch_assoc($result1)) {
        $role = $row['role'];
        if ($role == 'visitor') $userRoles['visitor'] = 1;
        if ($role == 'seller' && mysqli_num_rows($seller) > 0) $userRoles['seller'] = 1;
        if ($role == 'manager' && $managerEmail == $email) $userRoles['manager'] = 1;
        if ($role == 'volunteer' && mysqli_num_rows($vol) > 0) $userRoles['volunteer'] = 1;            
    }

    if (isset($_POST['role'])) {
        $_SESSION['currentRole'] = $_POST['role'];
        header("Location: ".$_SERVER['PHP_SELF']."?name=".$_GET['name']);
        exit();
    }

    if (!isset($_SESSION['currentRole'])) $_SESSION['currentRole'] = 'visitor';
    if ($_SESSION['currentRole'] == 'seller' && !isset($userRoles['seller'])) $_SESSION['currentRole'] = 'visitor';
    if ($_SESSION['currentRole'] == 'manager' && !isset($userRoles['manager'])) $_SESSION['currentRole'] = 'visitor';
    if ($_SESSION['currentRole'] == 'volunteer' && !isset($userRoles['volunteer'])) $_SESSION['currentRole'] = 'visitor';
    
    $currentUserRole = isset($_SESSION['currentRole']) ? $_SESSION['currentRole'] : 'visitor';


    // Обработка назначения столика
    if ($currentUserRole == 'manager' && isset($_POST['assign_table'])) {
        $sellerEmail = mysqli_real_escape_string($connection, $_POST['seller_email']);
        $tableNumber = (int)$_POST['table_number'];
        
        mysqli_query($connection, "USE `".$marketDb."`");
        mysqli_query($connection, "UPDATE `market-members` SET tableNumber = $tableNumber WHERE email = '$sellerEmail' AND `role` = 'seller'");
        
        header("Location: market-map.php?name=".urlencode($marketName));
        exit();
    }

    mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");

    mysqli_query($connection, "USE `".$marketDb."`");
    $marketInfo = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `market-info`"));
      
    include 'market-header.php';
    ?>

    <main class="single-market">  
        <section class="market-header">
            <table border="0" width="50%" align="center">
                <tr> 
                    <td><h1><a href="market-page.php?name=<?= $marketInfo['name'] ?>" class="menuButton"><?= htmlspecialchars($marketInfo['name']) ?></a></h1></td>
                    <td><h1><a href="market-products.php?name=<?= $marketInfo['name'] ?>" class="menuButton">Товары</a></h1></td>
                    <td><h1><a href="market-map.php?name=<?= $marketInfo['name'] ?>" class="menuButtonCur">Карта</a></h1></td>
                </tr>
            </table>
        </section>

    <!-- Список продавцов со ссылками на их страницы товаров -->
        <?php
        include 'scripts\getAllProducts.php'; // Получили список продавцов этой ярмарки
        // Получим кол-во столиков и зададим их продавцам (от лица орга)
        $tablesNum = mysqli_fetch_assoc(mysqli_query($connection, "SELECT tables FROM `market-info`"))['tables']; // кол-во столиков
        $tablesQuery = mysqli_query($connection, "SELECT tableNumber FROM `market-members`"); // номера занятых столиков
        while ($row = mysqli_fetch_assoc($tablesQuery)) {
            $occupiedTables[] = $row['tableNumber'];
        }
        

        if (mysqli_num_rows($allProductsInfo) > 0) {
            echo '<table class="requests-table">';
            echo '<thead><tr>
                    <th>Продавец</th>
                    <th>Столик</th>';
            if ($currentUserRole == 'manager')
                echo '<th>Действие</th>';
            echo '</tr></thead>';
            echo '<tbody>';

            mysqli_data_seek($allProductsInfo, 0); // Сбрасываем указатель
            while ($productInfo = mysqli_fetch_assoc($allProductsInfo)) {
                echo '<tr>';
                    echo '<td><a href="products.php?seller='.$productInfo['sellerName'].'" target="_blank">'.$productInfo['sellerName'].'</a></td>';
                    echo '<td>'.($productInfo['tableNumber'] > 0 ? $productInfo['tableNumber'] : 'Не назначен').'</td>';

                    //echo '<td><a href="market-map.php?name='.$_GET['name'].'" class="action-btn">Указать столик</a></td>';

                    if ($currentUserRole == 'manager') {
                        echo '<td>';
                        echo '<form method="POST" action="" style="display: flex;">';
                        echo '<input type="hidden" name="seller_email" value="'.htmlspecialchars($productInfo['email']).'">';
                        echo '<select name="table_number" class="table-select" required>';
                        echo '<option value="">Выберите столик</option>';
                        
                        // Генерируем список доступных столиков
                        for ($i = 1; $i <= $tablesNum; $i++) {
                            if (!in_array($i, $occupiedTables)) {
                                $selected = ($i == $productInfo['tableNumber']) ? 'selected' : '';
                                echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                            }
                        }
                        
                        echo '</select> ';
                        echo '<button type="submit" name="assign_table" class="action-btn">Назначить</button>';
                        echo '</form>';
                        echo '</td>';
                    }  
                echo '</tr>';
            }
                
            echo '</tbody></table>';
        } else {
            echo '<p>На этой ярмарке пока нет продавцов</p>';
        }
        ?>
        <!-- Карта ярмарки 
        <p>Карта будет доступна ближе к началу мероприятия</p>
        -->
        <section class="market-layout">
            <div class="layout-placeholder">
                <img class="zoom-img" src="<?php echo $marketInfo['mapFileName'] ?>" alt="Карта будет доступна ближе к началу ярмарки" id="market-map">
            </div>
        </section>

        <br>
        <!-- Кнопка добавления карты для орга-->
        <?php if($currentUserRole == 'manager'): ?>
            <form id="createMarketForm" action="scripts/addMap.php" method="POST" enctype="multipart/form-data" >          
                
                <div class="form-group">
                    <div class="file-upload">
                        <div class="file-upload-btn" id="mediaUploadBtn">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Нажмите для загрузки/замены схемы</p>
                        </div>
                        <input type="file" id="marketMap" name="marketMap" class="file-upload-input" accept="image/*" required>
                    </div>
                    
                    <div class="preview-container" id="mediaPreview"></div>
                    <input type="hidden" id="removedMedia" name="removedMedia" value="">
                </div>

                <button type="submit" class="submit-btn">Добавить</button>
                <a href="market-map.php?name=<?php echo $_GET['name'] ?>"><p align="center">Отмена</p></a>
            </form>
        <?php endif; ?>
        

    </main>

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

    function ZoomPan(e) {
        const container = this.getBoundingClientRect();

        const mouseX = e.clientX - container.left;
        const mouseY = e.clientY - container.top;
        
        const originX = (mouseX / this.offsetWidth) * 100;
        const originY = (mouseY / this.offsetHeight) * 100;
        
        this.getElementsByClassName("zoom-img")[0].style.transformOrigin = `${originX}% ${originY}%`;
        }

        var allContainers = document.getElementsByClassName("layout-placeholder");

        Array.from(allContainers).forEach(function(c) {
        c.addEventListener("mousemove", ZoomPan);
    });

    // Предпросмотр медиафайлов
    document.getElementById('marketMap').addEventListener('change', function(e) {
        const files = e.target.files;
        const preview = document.getElementById('mediaPreview');
        preview.innerHTML = '';
        
        for (let i = 0; i < Math.min(files.length, 10); i++) {
            const file = files[i];
            const reader = new FileReader();
            
            reader.onload = function(event) {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                
                if (file.type.startsWith('image/')) {
                    previewItem.innerHTML = `
                        <img src="${event.target.result}" alt="Предпросмотр медиа">
                        <button type="button" class="remove-btn" data-index="${i}">&times;</button>
                    `;
                } else if (file.type.startsWith('video/')) {
                    previewItem.innerHTML = `
                        <video controls>
                            <source src="${event.target.result}" type="${file.type}">
                        </video>
                        <button type="button" class="remove-btn" data-index="${i}">&times;</button>
                    `;
                }
                
                preview.appendChild(previewItem);
                
                // Добавляем обработчик удаления
                previewItem.querySelector('.remove-btn').addEventListener('click', function() {
                    removeMediaItem(this.getAttribute('data-index'));
                });
            };
            
            reader.readAsDataURL(file);
        }
    });

</script>
</body>
</html>
