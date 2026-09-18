<?php
session_start();
include 'checkAuth.php';

$conf = parse_ini_file('../config/config.ini');
$connection = mysqli_connect($conf['hostname'], $conf['username'], $conf['password']);
include 'getLogin.php';

// Получаем данные из формы
$dbName = $_POST['dbName'];
$marketName = $_POST['marketName'];
$email = $_POST['userEmail'];
mysqli_query($connection, "USE `$dbName`");


// Удаляем папку с медиафайлами
$dir = mysqli_fetch_assoc(mysqli_query($connection, "SELECT `name` FROM `market-info` WHERE id = 1"))['name'];
$dirTr = translit($dir);
$mediaDir = "../marketsMedia/$dirTr";
if (file_exists($mediaDir)) {
    array_map('unlink', glob("$mediaDir/*.*"));
    rmdir($mediaDir);
}

// Удаляем базу данных
mysqli_query($connection, "DROP DATABASE `$dbName`");

// Удаляем запись о ярмарке из основной базы
mysqli_query($connection, "USE market");
mysqli_query($connection, "DELETE FROM `all-existing-markets` WHERE `database-name` = '$dbName'");

// Удаляем из избранных
mysqli_query($connection, "DELETE FROM `user-favourites` WHERE `database-name` = '$dbName'");

// Польз не мен птмчт нет у него ярмарок 
$showPast = false; $sort = 'id';
include 'getManagedMarkets.php';
if (mysqli_num_rows($allMarketsInfo) == 0)
    mysqli_query($connection, "DELETE FROM `user-roles` WHERE `email` = '$email' AND `role` = 'manager'");


mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");
mysqli_close($connection);

// Перенаправляем обратно на страницу управления ярмарками
header("Location: ../managed-markets.php");
exit();

function translit($str) {
        $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return str_replace($russian, $translit, $str);
    }
?>
