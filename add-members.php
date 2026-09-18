<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить участников</title>
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

        $db = $_SESSION['currentMarketDB'];
        mysqli_query($connection, "USE `$db`");

        $invitedSellers = mysqli_query($connection, "SELECT email FROM `market-members` WHERE role = 'seller' OR role = 'volunteer'");
        $sellersList = [];
        while ($row = mysqli_fetch_assoc($invitedSellers)) {
            $sellersList[] = $row['email'];
        }

        include 'header.php';
    ?>


    <main>
        <div class="create-market-container">
            <h1>Изменить участников</h1>
            <nav>
                <table border="0" width="50%" align="center">
                    <tr> 
                        <td><a href="add-members.php" class="menuButtonCur">Добавить</a></td>
                        <td><a href="delete-members.php" class="menuButton">Удалить</a></td>
                    </tr>
                </table>
            </nav><br>
            
            <form id="createMarketForm" action="scripts/addMembers.php" method="POST" enctype="multipart/form-data">
                
                <div class="invite-sellers">
                    <h3>Пригласить продавцов</h3>
                    <p>Введите электронную почту продавцов, которых вы хотите пригласить на ярмарку</p>
                    
                    <div class="invite-input-container">
                        <input type="email" id="sellerEmail" placeholder="Электронная почта продавца">
                        <button type="button" class="invite-btn" id="addSellerBtn">Добавить</button>
                    </div>
                    
                    <div class="invited-list" id="invitedList"></div>
                    <input type="hidden" id="invitedSellers" name="invitedSellers">
                </div>


                <div class="invite-sellers">
                    <h3>Пригласить волонтёров</h3>
                    <p>Введите электронную почту волонтёров, которых вы хотите пригласить на ярмарку</p>
                    
                    <div class="invite-input-container">
                        <input type="email" id="volunteerEmail" placeholder="Электронная почта волонтёра">
                        <button type="button" class="invite-btn" id="addVolunteerBtn">Добавить</button>
                    </div>
                    
                    <div class="invited-list-v" id="invitedListV"></div>
                    <input type="hidden" id="invitedVolunteers" name="invitedVolunteers">
                </div>

                
                
                <button type="submit" class="submit-btn">Пригласить</button>
                <a href="scripts\returnToMarket.php"><p align="center">Отмена</p></a>
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
                        item.remove();
                        updateInvitedSellersInput();
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
        
        // Обновление скрытого поля с приглашенными продавцами
        function updateInvitedSellersInput() {
            const invitedItems = document.querySelectorAll('.invited-item span:first-child');
            const emails = Array.from(invitedItems).map(item => item.textContent.trim());
            document.getElementById('invitedSellers').value = emails.join(',');
        }


        // Добавление волонтёров
        document.getElementById('addVolunteerBtn').addEventListener('click', function() {
            const emailInput = document.getElementById('volunteerEmail');
            const email = emailInput.value.trim();
            
            if (email && validateEmail(email)) {
                const invitedListV = document.getElementById('invitedListV');
                
                // Проверяем, не добавлен ли уже этот email
                const existingItems = invitedListV.querySelectorAll('.invited-item-v');
                let alreadyAdded = false;
                
                existingItems.forEach(item => {
                    if (item.textContent.includes(email)) {
                        alreadyAdded = true;
                    }
                });
                
                if (!alreadyAdded) {
                    const item = document.createElement('div');
                    item.className = 'invited-item-v';
                    item.innerHTML = `
                        <span>${email}</span>
                        <span class="remove-invite" data-email="${email}">&times;</span>
                    `;
                    invitedListV.appendChild(item);
                    
                    // Добавляем обработчик удаления
                    item.querySelector('.remove-invite').addEventListener('click', function() {
                        item.remove();
                        updateInvitedVolunteersInput();
                    });
                    
                    updateInvitedVolunteersInput();
                    emailInput.value = '';
                } else {
                    alert('Этот продавец уже добавлен в список');
                }
            } else {
                alert('Пожалуйста, введите корректный email');
            }
        });
        
        // Обновление скрытого поля с приглашенными волонтёрами
        function updateInvitedVolunteersInput() {
            const invitedItemsV = document.querySelectorAll('.invited-item-v span:first-child');
            const emails = Array.from(invitedItemsV).map(item => item.textContent.trim());
            document.getElementById('invitedVolunteers').value = emails.join(',');
        }

        // Валидация email
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }


    </script>
</body>
</html>
