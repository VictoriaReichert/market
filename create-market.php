<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать ярмарку</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/create-market.css">
</head>
<body>
    <?php
        session_start();
        include 'scripts/checkAuth.php';
        include 'scripts/connectToDB.php';
        include 'scripts/getLogin.php';
        include 'header.php';
    ?>


    <main>
        <div class="create-market-container">
            <h1>Создать новую ярмарку</h1>
            
            <form id="createMarketForm" action="scripts/createDatabase.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="marketName">Название ярмарки *</label>
                    <input type="text" id="marketName" name="marketName" class="form-control" required>
                    <div class="error-message" id="nameError"></div>
                </div>
                
                <div class="form-group">
                    <label for="marketDescription">Описание ярмарки *</label>
                    <textarea id="marketDescription" name="marketDescription" class="form-control" rows="4" required></textarea>
                    <div class="error-message" id="descriptionError"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="openingDate">Дата открытия *</label>
                        <input type="date" id="openingDate" name="openingDate" class="form-control" required>
                        <div class="error-message" id="openingDateError"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="openingTime">Время открытия *</label>
                        <input type="time" id="openingTime" name="openingTime" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="closingDate">Дата закрытия *</label>
                        <input type="date" id="closingDate" name="closingDate" class="form-control" required>
                        <div class="error-message" id="closingDateError"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="closingTime">Время закрытия *</label>
                        <input type="time" id="closingTime" name="closingTime" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="marketAddress">Адрес проведения *</label>
                    <input type="text" id="marketAddress" name="marketAddress" class="form-control" required>
                    <div class="error-message" id="addressError"></div>
                </div>
                
                <div class="form-group">
                    <label for="ticketPrice">Вместимость ярмарки (посетителей в один день) *</label>
                    <input type="number" id="ticketPrice" name="ticketPrice" class="form-control" min="1" step="1" required>
                    <div class="error-message" id="priceError"></div>
                </div>
                
                <div class="form-group">
                    <label for="ticketPrice">Количество столиков *</label>
                    <input type="number" id="tables" name="tables" class="form-control" min="0" step="1" required>
                    <div class="error-message" id="tablesError"></div>
                </div>

                <div class="form-group">
                    <label>Обложка ярмарки *</label>
                    <div class="file-upload">
                        <div class="file-upload-btn" id="coverUploadBtn">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Нажмите для загрузки обложки</p>
                            <!-- <p class="small-text" style="font-size: 12px; color: #777;">Рекомендуемый размер: 1200x600px</p> -->
                        </div>
                        <input type="file" id="marketCover" name="marketCover" class="file-upload-input" accept="image/*" required>
                    </div>
                    <div class="preview-container" id="coverPreview"></div>
                    <div class="error-message" id="coverError"></div>
                </div>
                
                <button type="submit" class="submit-btn">Создать ярмарку</button>
                <a href="managed-markets.php"><p align="center">Отмена</p></a>
            </form>
        </div>
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

        // Предпросмотр обложки
        document.getElementById('marketCover').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('coverPreview');
                    preview.innerHTML = `
                        <div class="preview-item">
                            <img src="${event.target.result}" alt="Предпросмотр обложки">
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
            document.getElementById('coverPreview').innerHTML = '';
        }
        
        // Удаление медиафайла
        // function removeMediaItem(index) {
        //     const input = document.getElementById('marketMedia');
        //     const files = Array.from(input.files);
        //     files.splice(index, 1);
            
        //     // Создаем новый FileList (нельзя напрямую изменить существующий)
        //     const dataTransfer = new DataTransfer();
        //     files.forEach(file => dataTransfer.items.add(file));
        //     input.files = dataTransfer.files;
            
        //     // Перезагружаем превью
        //     const event = new Event('change');
        //     input.dispatchEvent(event);
        // }
        
        // Добавление продавцов
        // document.getElementById('addSellerBtn').addEventListener('click', function() {
        //     const emailInput = document.getElementById('sellerEmail');
        //     const email = emailInput.value.trim();
            
        //     if (email && validateEmail(email)) {
        //         const invitedList = document.getElementById('invitedList');
                
        //         // Проверяем, не добавлен ли уже этот email
        //         const existingItems = invitedList.querySelectorAll('.invited-item');
        //         let alreadyAdded = false;
                
        //         existingItems.forEach(item => {
        //             if (item.textContent.includes(email)) {
        //                 alreadyAdded = true;
        //             }
        //         });
                
        //         if (!alreadyAdded) {
        //             const item = document.createElement('div');
        //             item.className = 'invited-item';
        //             item.innerHTML = `
        //                 <span>${email}</span>
        //                 <span class="remove-invite" data-email="${email}">&times;</span>
        //             `;
        //             invitedList.appendChild(item);
                    
        //             // Добавляем обработчик удаления
        //             item.querySelector('.remove-invite').addEventListener('click', function() {
        //                 item.remove();
        //                 updateInvitedSellersInput();
        //             });
                    
        //             updateInvitedSellersInput();
        //             emailInput.value = '';
        //         } else {
        //             alert('Этот продавец уже добавлен в список');
        //         }
        //     } else {
        //         alert('Пожалуйста, введите корректный email');
        //     }
        // });
        
        // // Обновление скрытого поля с приглашенными продавцами
        // function updateInvitedSellersInput() {
        //     const invitedItems = document.querySelectorAll('.invited-item span:first-child');
        //     const emails = Array.from(invitedItems).map(item => item.textContent.trim());
        //     document.getElementById('invitedSellers').value = emails.join(',');
        // }
        
        // // Валидация email
        // function validateEmail(email) {
        //     const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        //     return re.test(email);
        // }
        
        // Валидация формы перед отправкой
        // document.getElementById('createMarketForm').addEventListener('submit', function(e) {
        //     let isValid = true;
        //     const now = new Date();
            
        //     // Проверка названия
        //     const name = document.getElementById('marketName').value.trim();
        //     if (name.length < 3 || name.length > 100) {
        //         document.getElementById('nameError').textContent = 'Название должно быть от 3 до 100 символов';
        //         document.getElementById('nameError').style.display = 'block';
        //         isValid = false;
        //     } else {
        //         document.getElementById('nameError').style.display = 'none';
        //     }
            
        //     // Проверка описания
        //     const description = document.getElementById('marketDescription').value.trim();
        //     if (description.length < 10 || description.length > 1000) {
        //         document.getElementById('descriptionError').textContent = 'Описание должно быть от 10 до 1000 символов';
        //         document.getElementById('descriptionError').style.display = 'block';
        //         isValid = false;
        //     } else {
        //         document.getElementById('descriptionError').style.display = 'none';
        //     }
            
        //     // Проверка даты открытия
        //     const openingDate = new Date(document.getElementById('openingDate').value + 'T' + document.getElementById('openingTime').value);
        //     if (isNaN(openingDate.getTime()) || openingDate < now) {
        //         document.getElementById('openingDateError').textContent = 'Дата открытия должна быть в будущем';
        //         document.getElementById('openingDateError').style.display = 'block';
        //         isValid = false;
        //     } else {
        //         document.getElementById('openingDateError').style.display = 'none';
        //     }
            
        //     // Проверка даты закрытия
        //     const closingDate = new Date(document.getElementById('closingDate').value + 'T' + document.getElementById('closingTime').value);
        //     if (isNaN(closingDate.getTime()) || closingDate  openingDate) {
        //         document.getElementById('closingDateError').textContent = 'Дата закрытия должна быть после даты открытия';
        //         document.getElementById('closingDateError').style.display = 'block';
        //         isValid = false;
        //     } else {
        //         document.getElementById('closingDateError').style.display = 'none';
        //     }
            
        //     // Проверка адреса
        //     const address = document.getElementById('marketAddress').value.trim();
        //     if (address.length < 5) {
        //         document.getElementById('addressError').textContent = 'Введите корректный адрес';
        //         document.getElementById('addressError').style.display = 'block';
        //         isValid = false;
        //     } else {
        //         document.getElementById('addressError').style.display = 'none';
        //     }
            
        //     // Проверка цены
        //     const price = parseFloat(document.getElementById('ticketPrice').value);
        //     if (isNaN(price) || price < 0) {
        //         document.getElementById('priceError').textContent = 'Введите корректную цену';
        //         document.getElementById('priceError').style.display = 'block';
        //         isValid = false;
        //     } else {
        //         document.getElementById('priceError').style.display = 'none';
        //     }
            
        //     // Проверка обложки
        //     const cover = document.getElementById('marketCover').files[0];
        //     if (!cover) {
        //         document.getElementById('coverError').textContent = 'Необходимо загрузить обложку';
        //         document.getElementById('coverError').style.display = 'block';
        //         isValid = false;
        //     } else {
        //         document.getElementById('coverError').style.display = 'none';
        //     }
            
        //     if (!isValid) {
        //         e.preventDefault();
        //         window.scrollTo(0, 0);
        //     }
        // });
    </script>
</body>
</html>
