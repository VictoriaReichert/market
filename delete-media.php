<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить фото/видео</title>
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
                        <td><a href="add-media.php" class="menuButton">Добавить</a></td>
                        <td><a href="delete-media.php" class="menuButtonCur">Удалить</a></td>
                    </tr>
                </table>
            </nav><br>

            <form id="createMarketForm" action="scripts/addMedia.php" method="POST" enctype="multipart/form-data">
                           
                <div class="form-group">
                    
                    <div class="preview-container" id="mediaPreview">
                        <?php foreach ($mediaList as $media): ?>
                            <div class="preview-item" data-id="<?= $media['id'] ?>">
                                <?php if ($media['type'] == 'image'): ?>
                                    <img src="<?= htmlspecialchars($media['fileName']) ?>" alt="Медиафайл">
                                <?php else: ?>
                                    <video controls>
                                        <source src="<?= htmlspecialchars($media['fileName']) ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                                <button type="button" class="remove-btn-delete" onclick="removeMedia(<?= $media['id'] ?>)">&times;</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="removedMedia" name="removedMedia" value="">
                </div>
                
                <button type="submit" class="submit-btn">Удалить</button>
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
