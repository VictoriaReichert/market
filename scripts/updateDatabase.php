<?php
session_start();
include 'checkAuth.php';

$conf = parse_ini_file('../config/config.ini');
$connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);

// Получаем данные из формы
$dbName = $_POST['dbName'];
$marketName = $_POST['marketName'];
$marketName_tr = translit($marketName);
$description = mysqli_real_escape_string($connection, $_POST['marketDescription']);
$openingDate = $_POST['openingDate'];
$closingDate = $_POST['closingDate'];
$openingTime = $_POST['openingTime'].':00';
$closingTime = $_POST['closingTime'].':00';
$address = mysqli_real_escape_string($connection, $_POST['marketAddress']);
$ticketPrice = $_POST['ticketPrice'];
$tables = $_POST['tables'];
date_default_timezone_set('Europe/Moscow');
$date = date('Y-m-d');

// Подключаемся к базе данных ярмарки
mysqli_query($connection, "USE `$dbName`");


//---------------------------------------------------------------------------
// Получаем текущие данные о ярмарке для сравнения
$currentInfo = mysqli_query($connection, "SELECT * FROM `market-info` WHERE name = '$marketName'");
$currentInfo = mysqli_fetch_assoc($currentInfo);

// Проверяем, изменились ли даты или цена билета
$datesChanged = ($currentInfo['openingDate'] != $openingDate || $currentInfo['closingDate'] != $closingDate);
$priceChanged = ($currentInfo['ticketPrice'] != $ticketPrice);

// Если изменились даты или цена, обновляем таблицу market-days
if ($datesChanged || $priceChanged) {
    // Удаляем старые записи
    mysqli_query($connection, "DELETE FROM `market-days`");
    
    // Создаем новые записи
    $startDate = new DateTime($openingDate);
    $endDate = new DateTime($closingDate);
    $capacity = $ticketPrice;
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
    
    foreach ($period as $dateObj) {
        $dateStr = $dateObj->format('Y-m-d');
        mysqli_query($connection, 
            "INSERT INTO `market-days` 
            (`date`, `capacity`)
            VALUES
            ('$dateStr', '$capacity')");
    }
}
//---------------------------------------------------------------------------

// Обновляем основную информацию о ярмарке
$updateQuery = "UPDATE `market-info` SET 
    `description` = '$description',
    `openingDate` = '$openingDate',
    `closingDate` = '$closingDate',
    `openingTime` = '$openingTime',
    `closingTime` = '$closingTime',
    `address` = '$address',
    `ticketPrice` = '$ticketPrice',
    `tables` = '$tables'
    WHERE name = '$marketName'";
mysqli_query($connection, $updateQuery);

// Обрабатываем загрузку новой обложки
if (isset($_FILES['marketCover']) && $_FILES['marketCover']['error'] == UPLOAD_ERR_OK) {
    $cover = $_FILES['marketCover'];
    $coverFileName = $cover['name'];
    $coverPath = 'marketsMedia/'.$marketName_tr.'/';
    
    if (!file_exists("../".$coverPath)) mkdir("../".$coverPath, 0777, true);

    $fullCoverPath = $coverPath.$coverFileName;
    if (move_uploaded_file($cover['tmp_name'], "../".$fullCoverPath)) {
        // Удаляем старую обложку, если она существует
        $oldCover = mysqli_query($connection, "SELECT pictureFileName FROM `market-info` WHERE `name` = '$marketName'");
        $oldCoverPath = mysqli_fetch_assoc($oldCover)['pictureFileName'];
        if (file_exists("../".$oldCoverPath)) {
            unlink("../".$oldCoverPath);
        }
        
        // Обновляем путь к новой обложке
        mysqli_query($connection, "UPDATE `market-info` SET pictureFileName = '$fullCoverPath' WHERE `name` = '$marketName'");
    }
}

function translit($str) {
    $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
    return str_replace($russian, $translit, $str);
}

mysqli_close($connection);

// Перенаправляем обратно на страницу управления ярмарками
include 'returnToMarket.php';
?>
