<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Изменение ярмарки</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/create-market.css">
</head>
<body>
    <?php
        session_start();
        include 'scripts/checkAuth.php';

        // Проверяем, что передан параметр с именем ярмарки
        if (!isset($_GET['name'])) {
            header('Location: managed-markets.php');
            exit();
        }

        $marketName = $_GET['name'];

        $conf = parse_ini_file('config/config.ini');
        $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);
        mysqli_query($connection, "USE market");

        // Получаем данные пользователя для шапки
        $email = $_COOKIE['logIn'];
        $userResult = mysqli_query($connection, "SELECT * FROM `users` WHERE email = '$email'");
        $user = mysqli_fetch_assoc($userResult);
        $userData['login'] = $user['login'];

        // Подключаемся к базе данных ярмарки
        $db = $_SESSION['currentMarketDB'];
        mysqli_query($connection, "USE `$db`");

        // Получаем детальную информацию о ярмарке
        $marketDetails = mysqli_query($connection, "SELECT * FROM `market-info`");
        $marketDetails = mysqli_fetch_assoc($marketDetails);

        // Получаем список медиафайлов
        // $mediaFiles = mysqli_query($connection, "SELECT * FROM `media`");
        // $mediaList = [];
        // while ($row = mysqli_fetch_assoc($mediaFiles)) {
        //     $mediaList[] = $row;
        // }

        // Получаем список приглашенных продавцов
        $invitedSellers = mysqli_query($connection, "SELECT email FROM `market-members` WHERE role = 'seller'");
        $sellersList = [];
        while ($row = mysqli_fetch_assoc($invitedSellers)) {
            $sellersList[] = $row['email'];
        }
        
        include 'header.php';
    ?>


    <main>
        <div class="create-market-container">
            <h1>Изменить ярмарку: <?= htmlspecialchars($marketName) ?></h1>
            
            <form id="editMarketForm" action="scripts/updateDatabase.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="dbName" value="<?= $db ?>">
                <input type="hidden" name="marketName" value="<?= $marketName ?>">
                
                <div class="form-group">
                    <label for="marketName">Название ярмарки </label>
                    <input type="text" id="marketName" name="marketName" class="form-control-name" value="<?= htmlspecialchars($marketDetails['name']) ?>" readonly>
                    <!-- <div class="error-message" id="nameError"></div> -->
                </div>
                
                <div class="form-group">
                    <label for="marketDescription">Описание ярмарки *</label>
                    <textarea id="marketDescription" name="marketDescription" class="form-control" rows="4" required><?= htmlspecialchars($marketDetails['description']) ?></textarea>
                    <div class="error-message" id="descriptionError"></div>
                </div>
                
                <?php 
                    $openingDate = date('Y-m-d', strtotime($marketDetails['openingDate']));
                    $openingTime = date('H:i', strtotime($marketDetails['openingTime']));
                    $closingDate = date('Y-m-d', strtotime($marketDetails['closingDate']));
                    $closingTime = date('H:i', strtotime($marketDetails['closingTime']));
                ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="openingDate">Дата открытия *</label>
                        <input type="date" id="openingDate" name="openingDate" class="form-control" value="<?= $openingDate ?>" required>
                        <div class="error-message" id="openingDateError"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="openingTime">Время открытия *</label>
                        <input type="time" id="openingTime" name="openingTime" class="form-control" value="<?php echo $openingTime ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="closingDate">Дата закрытия *</label>
                        <input type="date" id="closingDate" name="closingDate" class="form-control" value="<?= $closingDate ?>" required>
                        <div class="error-message" id="closingDateError"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="closingTime">Время закрытия *</label>
                        <input type="time" id="closingTime" name="closingTime" class="form-control" value="<?= $closingTime ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="marketAddress">Адрес проведения *</label>
                    <input type="text" id="marketAddress" name="marketAddress" class="form-control" value="<?= htmlspecialchars($marketDetails['address']) ?>" required>
                    <div class="error-message" id="addressError"></div>
                </div>
                
                <div class="form-group">
                    <label for="ticketPrice">Вместимость ярмарки (посетителей в один день) *</label>
                    <input type="number" id="ticketPrice" name="ticketPrice" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($marketDetails['ticketPrice']) ?>" required>
                    <div class="error-message" id="priceError"></div>
                </div>

                <div class="form-group">
                    <label for="ticketPrice">Количество столиков *</label>
                    <input type="number" id="tables" name="tables" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($marketDetails['tables']) ?>" required>
                    <div class="error-message" id="tablesError"></div>
                </div>
                
                <div class="form-group">
                    <label>Обложка ярмарки *</label>
                    <div class="file-upload">
                        <div class="file-upload-btn" id="coverUploadBtn">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Нажмите для изменения обложки</p>
                            <p class="small-text" style="font-size: 12px; color: #777;">Текущая обложка будет заменена</p>
                        </div>
                        <input type="file" id="marketCover" name="marketCover" class="file-upload-input" accept="image/*">
                    </div>
                    <div class="preview-container" id="coverPreview">
                        <div class="preview-item">
                            <img src="<?= htmlspecialchars($marketDetails['pictureFileName']) ?>" alt="Текущая обложка">
                        </div>
                    </div>
                    <div class="error-message" id="coverError"></div>
                </div>
                
                <div class="form-row" style="margin-top: 30px;">
                    <button type="submit" class="submit-btn" style="flex: 2;">Сохранить изменения</button>
                </div>
            </form>
            <form id="deleteMarketForm" action="scripts\deleteDatabase.php" method="POST">
                <input type="hidden" name="dbName" value="<?= $db ?>">
                <input type="hidden" name="marketName" value="<?= $marketName ?>">
                <input type="hidden" name="userEmail" value="<?= $email ?>">
                <button type="submit" class="submit-btn" style="flex: 2;">Удалить ярмарку</button>
                <a href="scripts\returnToMarket.php"><p align="center">Отмена</p></a>
            </form>
        </div>
    </main>

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script> -->
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

        // Предпросмотр обложки
        document.getElementById('marketCover').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('coverPreview');
                    preview.innerHTML = `
                        <div class="preview-item">
                            <img src="${event.target.result}" alt="Новая обложка">
                            <button type="button" class="remove-btn" onclick="removeCover()">&times;</button>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
        
        
        
        // Удаление обложки
        function removeCover() {
            document.getElementById('marketCover').value = '';
            const preview = document.getElementById('coverPreview');
            preview.innerHTML = `
                <div class="preview-item">
                    <img src="<?= htmlspecialchars($marketDetails['pictureFileName']) ?>" alt="Текущая обложка">
                </div>
            `;
        }
        
       
        // Добавление продавцов
        document.getElementById('addSellerBtn').addEventListener('click', function() {
            const emailInput = document.getElementById('sellerEmail');
            const email = emailInput.value.trim();
            
            if (email && validateEmail(email)) {
                const invitedList = document.getElementById('invitedList');
                
                // Проверяем, не добавлен ли уже этот email
                const existingItems = invitedList.querySelectorAll('.invited-item');
                let alreadyAdded = false;
                
                existingItems.forEach(item => {
                    if (item.textContent.includes(email)) {
                        alreadyAdded = true;
                    }
                });
                
                if (!alreadyAdded) {
                    const item = document.createElement('div');
                    item.className = 'invited-item';
                    item.innerHTML = `
                        <span>${email}</span>
                        <span class="remove-invite" data-email="${email}">&times;</span>
                    `;
                    invitedList.appendChild(item);
                    
                    // Добавляем обработчик удаления
                    item.querySelector('.remove-invite').addEventListener('click', function() {
                        removeSeller(email);
                    });
                    
                    updateInvitedSellersInput();
                    emailInput.value = '';
                } else {
                    alert('Этот продавец уже добавлен в список');
                }
            } else {
                alert('Пожалуйста, введите корректный email');
            }
        });
        
        // Удаление продавца из списка
        function removeSeller(email) {
            const items = document.querySelectorAll('.invited-item');
            items.forEach(item => {
                if (item.textContent.includes(email)) {
                    item.remove();
                    
                    // Добавляем в список удаленных
                    const removedSellers = document.getElementById('removedSellers');
                    const currentValue = removedSellers.value ? removedSellers.value.split(',') : [];
                    currentValue.push(email);
                    removedSellers.value = currentValue.join(',');
                }
            });
            
            updateInvitedSellersInput();
        }
        
        // Обновление скрытого поля с приглашенными продавцами
        function updateInvitedSellersInput() {
            const invitedItems = document.querySelectorAll('.invited-item span:first-child');
            const emails = Array.from(invitedItems).map(item => item.textContent.trim());
            document.getElementById('invitedSellers').value = emails.join(',');
        }
        
        // Валидация email
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Валидация формы перед отправкой
        document.getElementById('editMarketForm').addEventListener('submit', function(e) {
            let isValid = true;
            const now = new Date();
            
            // Проверка названия
            const name = document.getElementById('marketName').value.trim();
            if (name.length < 3 || name.length > 100) {
                document.getElementById('nameError').textContent = 'Название должно быть от 3 до 100 символов';
                document.getElementById('nameError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('nameError').style.display = 'none';
            }
            
            // Проверка описания
            const description = document.getElementById('marketDescription').value.trim();
            if (description.length < 10 || description.length > 1000) {
                document.getElementById('descriptionError').textContent = 'Описание должно быть от 10 до 1000 символов';
                document.getElementById('descriptionError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('descriptionError').style.display = 'none';
            }
            
            // Проверка даты открытия
            const openingDate = new Date(document.getElementById('openingDate').value + 'T' + document.getElementById('openingTime').value);
            if (isNaN(openingDate.getTime())) {
                document.getElementById('openingDateError').textContent = 'Введите корректную дату открытия';
                document.getElementById('openingDateError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('openingDateError').style.display = 'none';
            }
            
            // Проверка даты закрытия
            const closingDate = new Date(document.getElementById('closingDate').value + 'T' + document.getElementById('closingTime').value);
            if (isNaN(closingDate.getTime()) || closingDate <= openingDate) {
                document.getElementById('closingDateError').textContent = 'Дата закрытия должна быть после даты открытия';
                document.getElementById('closingDateError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('closingDateError').style.display = 'none';
            }
            
            // Проверка адреса
            const address = document.getElementById('marketAddress').value.trim();
            if (address.length < 5) {
                document.getElementById('addressError').textContent = 'Введите корректный адрес';
                document.getElementById('addressError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('addressError').style.display = 'none';
            }
            
            // Проверка цены
            const price = parseFloat(document.getElementById('ticketPrice').value);
            if (isNaN(price) || price < 0) {
                document.getElementById('priceError').textContent = 'Введите корректную цену';
                document.getElementById('priceError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('priceError').style.display = 'none';
            }
            
            if (!isValid) {
                e.preventDefault();
                window.scrollTo(0, 0);
            }
        });
        
        // Удаление ярмарки
        document.getElementById('deleteMarketBtn').addEventListener('click', function() {
            if (confirm('Вы уверены, что хотите удалить эту ярмарку? Это действие нельзя отменить.')) {
                const form = document.getElementById('editMarketForm');
                const deleteForm = document.createElement('form');
                deleteForm.method = 'POST';
                deleteForm.action = 'scripts/delete_market.php';
                
                const dbNameInput = document.createElement('input');
                dbNameInput.type = 'hidden';
                dbNameInput.name = 'dbName';
                dbNameInput.value = '<?= $dbName ?>';
                deleteForm.appendChild(dbNameInput);
                
                const marketNameInput = document.createElement('input');
                marketNameInput.type = 'hidden';
                marketNameInput.name = 'marketName';
                marketNameInput.value = '<?= $marketName ?>';
                deleteForm.appendChild(marketNameInput);
                
                document.body.appendChild(deleteForm);
                deleteForm.submit();
            }
        });
    </script>
</body>
</html>
