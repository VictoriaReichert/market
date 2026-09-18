<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать товар</title>
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

    // Получаем ID товара для редактирования
    $productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $productData = null;

    if ($productId > 0) {
        // Получаем данные товара
        $userLogin = lowerCase($userData['login']);
        $query = "SELECT * FROM `market-catalogs`.`$userLogin` WHERE id = $productId";
        $result = mysqli_query($connection, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $productData = mysqli_fetch_assoc($result);
        } else {
            header("Location: managed-products.php");
            exit();
        }
    } else {
        header("Location: managed-products.php");
        exit();
    }

    function lowerCase($str) {
        $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $translit = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        return str_replace($russian, $translit, $str);
    }

    include 'header.php';
?>

    <main>
        <div class="create-market-container">
            <h1>Редактировать товар</h1>
            
            <form id="editProductForm" action="scripts/updateProduct.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="productId" value="<?= $productId ?>">
                <input type="hidden" name="userName" value="<?= $userData['login'] ?>">
                <input type="hidden" name="currentImage" value="<?= $productData['pictureFileName'] ?>">

                <div class="form-group">
                    <label for="productCategory">Категория *</label>
                    <input type="text" id="productCategory" name="category" class="form-control" 
                           value="<?= htmlspecialchars($productData['type']) ?>" required>
                    <div class="error-message" id="categoryError"></div>
                </div>
            
                <div class="form-group">
                    <label for="productName">Название товара *</label>
                    <input type="text" id="productName" name="name" class="form-control" 
                           value="<?= htmlspecialchars($productData['name']) ?>" required>
                    <div class="error-message" id="nameError"></div>
                </div>
                
                <div class="form-group">
                    <label for="productDescription">Описание товара *</label>
                    <textarea id="productDescription" name="description" class="form-control" rows="4" required><?= 
                        htmlspecialchars($productData['description']) ?></textarea>
                    <div class="error-message" id="descriptionError"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="productPrice">Цена (руб.) *</label>
                        <input type="number" id="productPrice" name="price" class="form-control" 
                               value="<?= $productData['price'] ?>" min="0" step="1" required>
                        <div class="error-message" id="priceError"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="productQuantity">Количество *</label>
                        <input type="number" id="productQuantity" name="quantity" class="form-control" 
                               value="<?= $productData['quantity'] ?>" min="1" required>
                        <div class="error-message" id="quantityError"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Изображение товара</label>
                    <div class="current-image-preview">
                        <img src="<?= $productData['pictureFileName'] ?>" alt="Текущее изображение" style="max-width: 100%; height: auto;">
                        <p>Текущее изображение</p>
                    </div>
                    <div class="file-upload">
                        <div class="file-upload-btn" id="imageUploadBtn">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Нажмите для изменения изображения</p>
                        </div>
                        <input type="file" id="productImage" name="image" class="file-upload-input" accept="image/*">
                    </div>
                    <div class="preview-container" id="imagePreview"></div>
                    <div class="error-message" id="imageError"></div>
                </div>
                
                <button type="submit" class="submit-btn">Сохранить изменения</button>
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
                            <img src="${event.target.result}" alt="Новое изображение">
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
