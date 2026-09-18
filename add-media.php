<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить фото/видео</title>
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

        // Получаем список медиафайлов
        $mediaFiles = mysqli_query($connection, "SELECT * FROM `media`");
        $mediaList = [];
        while ($row = mysqli_fetch_assoc($mediaFiles)) {
            $mediaList[] = $row;
        }

        include 'header.php';
    ?>


    <main>
        <div class="create-market-container">
            <h1>Изменить галерею</h1>
            
            <nav>
                <table border="0" width="50%" align="center">
                    <tr> 
                        <td><a href="add-media.php" class="menuButtonCur">Добавить</a></td>
                        <td><a href="delete-media.php" class="menuButton">Удалить</a></td>
                    </tr>
                </table>
            </nav><br>

            <form id="createMarketForm" action="scripts/addMedia.php" method="POST" enctype="multipart/form-data">          
                <div class="form-group">
                    <div class="file-upload">
                        <div class="file-upload-btn" id="mediaUploadBtn">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Нажмите для загрузки медиафайлов</p>
                            <!-- <p class="small-text" style="font-size: 12px; color: #777;">Максимум 10 файлов</p> -->
                        </div>
                        <input type="file" id="marketMedia" name="marketMedia[]" class="file-upload-input" accept="image/*,video/*" multiple>
                    </div>
                    
                    <div class="preview-container" id="mediaPreview"></div>
                    <input type="hidden" id="removedMedia" name="removedMedia" value="">
                </div>
                
                <button type="submit" class="submit-btn">Добавить</button>
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

        
        // Предпросмотр медиафайлов
        document.getElementById('marketMedia').addEventListener('change', function(e) {
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
        
        
        // Удаление медиафайла
        function removeMediaItem(index) {
            const input = document.getElementById('marketMedia');
            const files = Array.from(input.files);
            files.splice(index, 1);
            
            // Создаем новый FileList (нельзя напрямую изменить существующий)
            const dataTransfer = new DataTransfer();
            files.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
            
            // Перезагружаем превью
            const event = new Event('change');
            input.dispatchEvent(event);
        }
        
        // Удаление новых медиафайлов (еще не сохраненных)
        function removeNewMediaItem(index) {
            const input = document.getElementById('marketMedia');
            const files = Array.from(input.files);
            files.splice(index, 1);
            
            const dataTransfer = new DataTransfer();
            files.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
            
            const event = new Event('change');
            input.dispatchEvent(event);
        }
        
        // Удаление существующих медиафайлов
        function removeMedia(id) {
            const item = document.querySelector(`.preview-item[data-id="${id}"]`);
            if (item) {
                item.remove();
                
                const removedMedia = document.getElementById('removedMedia');
                const currentValue = removedMedia.value ? removedMedia.value.split(',') : [];
                currentValue.push(id);
                removedMedia.value = currentValue.join(',');
            }
        }
        
    </script>
</body>
</html>
