<?php
    session_start();
    include 'checkAuth.php'; 

    $conf = parse_ini_file('../config/config.ini');
    $connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);
    $db = "`".$_SESSION['currentMarketDB']."`";
    mysqli_query($connection, "USE $db");

    // 0. Получаем необходимые данные
    $email = $_COOKIE['logIn'];
    date_default_timezone_set('Europe/Moscow');
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $marketName_tr = translit($_SESSION['currentMarket']);
    $removedMedia = isset($_POST['removedMedia']) ? explode(',', $_POST['removedMedia']) : [];


    // 3. Добавление фото
    // Обрабатываем загрузку медиафайлов
    $mediaFiles = [];
    if (!empty($_FILES['marketMedia']['name'][0])) {
        foreach ($_FILES['marketMedia']['name'] as $key => $name) {
            $tmpName = $_FILES['marketMedia']['tmp_name'][$key];
            $error = $_FILES['marketMedia']['error'][$key];
            
            if ($error === UPLOAD_ERR_OK) {
                $fileType = strpos($_FILES['marketMedia']['type'][$key], 'mp4') !== false ? 'video' : 'image';
                $fileName = $name;
                $filePath = 'marketsMedia/'.$marketName_tr.'/';

                if (!file_exists("../".$filePath)) mkdir("../".$filePath, 0777, true);
                
                $fullPath = $filePath.$fileName;
                if (move_uploaded_file($tmpName, "../".$fullPath)) {
                    $mediaFiles[] = [
                        'fileName' => $fullPath,
                        'fileType' => $fileType
                    ];
                }
            }
        }
    }

    // 4. Заполняем таблицы данными
    // Array ( 
    //     [0] => Array ( [fileName] => girl.jpg [fileType] => image ) 
    //     [1] => Array ( [fileName] => 02.23.jpg [fileType] => image ) 
    // )
    for ($i = 0; $i < count($mediaFiles); $i++) {
        $n = $mediaFiles[$i]['fileName'];
        $t = $mediaFiles[$i]['fileType'];
        mysqli_query($connection, 
            "INSERT INTO `media` 
            (`fileName`,`type`,`uploadDate`)
            VALUES
            ('$n', '$t', '$date');" );
    }

    // Удаляем отмеченные медиафайлы
    foreach ($removedMedia as $mediaId) {
        if (!empty($mediaId)) {
            $mediaInfo = mysqli_query($connection, "SELECT `fileName` FROM `media` WHERE id = $mediaId");
            if ($mediaInfo && mysqli_num_rows($mediaInfo) > 0) {
                $fileName = mysqli_fetch_assoc($mediaInfo)['fileName'];
                if (file_exists("../".$fileName)) {
                    unlink("../".$fileName);
                }
                mysqli_query($connection, "DELETE FROM `media` WHERE id = $mediaId");
            }
        }
    }

    mysqli_close($connection);

    include 'returnToMarket.php';

    function translit($str) {
        $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return str_replace($russian, $translit, $str);
    }
?>