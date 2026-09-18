<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить товар</title>
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

    // // Получаем список ярмарок, где пользователь является продавцом
    // $sellerMarkets = [];
    // $marketsQuery = mysqli_query($connection, "SELECT `database-name`, name FROM `all-existing-markets`");

    // while ($market = mysqli_fetch_assoc($marketsQuery)) {
    //     $dbName = $market['database-name'];
    //     mysqli_query($connection, "USE `$dbName`");
    //     $isSeller = mysqli_query($connection, "SELECT 1 FROM `market-members` WHERE email = '$email' AND role = 'seller'");
        
    //     if (mysqli_num_rows($isSeller) > 0) {
    //         $sellerMarkets[] = $market;
    //     }
    // }

    include 'header.php';
?>

    <main>
        <div class="create-market-container">
            <h1>Добавить новый товар</h1>
            
            <form id="addProductForm" action="scripts/addProduct.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="userName" value="<?= $userData['login'] ?>">

                <div class="form-group">
                    <label for="productCategory">Категория *</label>
                    <input type="text" id="productCategory" name="category" class="form-control" required>
                    <div class="error-message" id="categoryError"></div>
                </div>
            
                <div class="form-group">
                    <label for="productName">Название товара *</label>
                    <input type="text" id="productName" name="name" class="form-control" required>
                    <div class="error-message" id="nameError"></div>
                </div>
                
                <div class="form-group">
                    <label for="productDescription">Описание товара *</label>
                    <textarea id="productDescription" name="description" class="form-control" rows="4" required></textarea>
                    <div class="error-message" id="descriptionError"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="productPrice">Цена (руб.) *</label>
                        <input type="number" id="productPrice" name="price" class="form-control" min="0" step="1" required>
                        <div class="error-message" id="priceError"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="productQuantity">Количество *</label>
                        <input type="number" id="productQuantity" name="quantity" class="form-control" min="1" required>
                        <div class="error-message" id="quantityError"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Изображение товара *</label>
                    <div class="file-upload">
                        <div class="file-upload-btn" id="imageUploadBtn">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Нажмите для загрузки изображения</p>
                        </div>
                        <input type="file" id="productImage" name="image" class="file-upload-input" accept="image/*" required>
                    </div>
                    <div class="preview-container" id="imagePreview"></div>
                    <div class="error-message" id="imageError"></div>
                </div>
                
                <button type="submit" class="submit-btn">Добавить товар</button>
            </form>
            <a href="managed-products.php"><p align="center">Отмена</p></a>
        </div>
    </main>

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

        // Предпросмотр изображения
        document.getElementById('productImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = `
                        <div class="preview-item">
                            <img src="${event.target.result}" alt="Предпросмотр изображения">
                            <button type="button" class="remove-btn" onclick="removeImage()">&times;</button>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Удаление изображения
        function removeImage() {
            document.getElementById('productImage').value = '';
            document.getElementById('imagePreview').innerHTML = '';
        }
        
    </script>
</body>
</html>
