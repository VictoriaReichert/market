<?php
    mysqli_query($connection, "USE market");
    $email = $_COOKIE['logIn'];
    $result = mysqli_query($connection, "SELECT * FROM `users` WHERE email = '$email'");
    $user = mysqli_fetch_assoc($result);
    $userData['login'] = $user['login'];
?>