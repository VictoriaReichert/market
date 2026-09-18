<?php
    session_start();
    include 'checkAuth.php'; 

    $conf = parse_ini_file('../config/config.ini');
    $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);
    $db = "`".$_SESSION['currentMarketDB']."`";
    mysqli_query($connection, "USE $db");

    $marketDB = $_SESSION['currentMarketDB'];
    $marketName = $_SESSION['currentMarket'];

    // Получаем информацию о ярмарке
    $marketInfo = mysqli_fetch_assoc(mysqli_query($connection, 
        "SELECT * FROM `$marketDB`.`market-info`"));

    // Обработка загрузки файла
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['marketMap'])) {
        // Проверяем тип файла
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['marketMap']['type'], $allowedTypes)) {
            die("Ошибка: допустимы только файлы изображений (JPEG, PNG, GIF)");
        }

        // Проверяем размер файла (макс. 5MB)
        if ($_FILES['marketMap']['size'] > 5 * 1024 * 1024) {
            die("Ошибка: размер файла не должен превышать 5MB");
        }

        // Создаем директорию для хранения карт, если ее нет
        $uploadDir = 'marketsMedia/'.translit($marketName).'/';
        if (!file_exists('../'.$uploadDir)) {
            mkdir('../'.$uploadDir, 0777, true);
        }

        // Генерируем имя файла
        $fileName = $_FILES['marketMap']['name'];
        $filePath = $uploadDir.$fileName;

        // Если файл уже существует, удаляем его
        if (!empty($marketInfo['mapFileName']) && file_exists('../'.$marketInfo['mapFileName'])) {
            unlink('../'.$marketInfo['mapFileName']);
        }

        // Загружаем новый файл
        if (move_uploaded_file($_FILES['marketMap']['tmp_name'], '../'.$filePath)) {
            // Обновляем запись в базе данных
            mysqli_query($connection, 
                "UPDATE `$marketDB`.`market-info` 
                SET mapFileName = '$filePath' 
                WHERE id = " . $marketInfo['id']);

            header("Location: ../market-map.php?name=" . urlencode($marketName));
            exit();
        } else {
            die("Ошибка при загрузке файла");
        }
    }

    // Функция для транслитерации названия ярмарки
    function translit($str) {
        $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return str_replace($russian, $translit, $str);
    }
?>