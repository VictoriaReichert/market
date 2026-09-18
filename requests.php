<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запросы и приглашения</title>
    <link rel="shortcut icon" href="media/иконка.png"/>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        
    </style>
</head>
<body>
    <?php
        session_start();
        include 'scripts/checkAuth.php';
        include 'scripts/connectToDB.php';
        include 'scripts/getLogin.php';

        include 'scripts/getAllRequests.php';
        include 'scripts/handleRequests.php';

        include 'header.php';
    ?>

    <main class="requests-container">
        <h1>Мои запросы и приглашения</h1>
        
        <div class="tabs">
            <div class="tab active" onclick="showTab('incoming')">Входящие</div>
            <div class="tab" onclick="showTab('outgoing')">Исходящие</div>
            <div class="tab" onclick="showTab('new')">Новый запрос</div>
        </div>
        
    <!-- 1. ------------------------------------------------------------------------------------------------------------------------------->
        <div id="incoming-tab" class="requests-section">
            <h2>Запросы на участие в Ваших ярмарках</h2>
            <?php
                // Получаем входящие запросы
                $result1 = mysqli_query($connection, "SELECT * FROM $tableNameInc WHERE managerEmail = '$email' AND `status`= 'Запрос отправлен' ORDER BY `date` DESC"); // Запрос пришёл менеджеру ярмарки
                
                if (mysqli_num_rows($result1) > 0) {
                    echo '<table class="requests-table">';
                    echo '<thead><tr>
                            <th>Ярмарка</th>
                            <th>Отправитель</th>
                            <th>Роль</th>
                            <th>Получено</th>
                            <th>Действие</th>
                          </tr></thead>';
                    echo '<tbody>';
                    
                    while ($row = mysqli_fetch_assoc($result1)) {
                        $senEm = $row['senderEmail'];
                        $senderName = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM market.users WHERE email = '$senEm'"))['login'];
                        $statusClass = 'status-'.$row['status'];
                        echo '<tr>';
                        echo '<td>'.$row['marketName'].'</td>';
                        if ($row['role'] == 'seller')
                            echo '<td><a href="products.php?seller='.$senderName.'" target="_blank">'.$row['senderEmail'].' ('.$senderName.')</a></td>';
                        else
                            echo '<td>'.$row['senderEmail'].' ('.$senderName.')</td>';
                        echo '<td>'.role($row['role']).'</td>';
                        echo '<td>'.$row['date'].'</td>';
                        


                        if ($row['status'] == 'Приглашение отправлено' || $row['status'] == 'Запрос отправлен')
                            echo '<td>
                                    <a href="requests.php?accept=1&market='.$row['receiverDB'].'&role='.$row['role'].'&sender='.$row['senderEmail'].'&type=inc" class="action-btn">Принять</a>
                                    <a href="requests.php?accept=0&market='.$row['receiverDB'].'&role='.$row['role'].'&sender='.$row['senderEmail'].'&type=inc" class="action-btn">Отклонить</a>
                                </td>';
                        else echo '<td>-</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                } else {
                    echo '<p>Нет входящих запросов.</p>';
                }
            ?>

            <br>
            <h2>Пришлашения на другие ярмарки</h2>
            <?php
                // Получаем входящие запросы
                $result2 = mysqli_query($connection, "SELECT * FROM $tableNameOut WHERE receiverEmail = '$email' AND `status` = 'Приглашение отправлено' ORDER BY `date` DESC"); // Запрос пришёл пользователю
                
                if (mysqli_num_rows($result2) > 0) {
                    echo '<table class="requests-table">';
                    echo '<thead><tr>
                            <th>Отправитель</th>
                            <th>Роль</th>
                            <th>Получено</th>
                            <th>Действие</th>
                          </tr></thead>';
                    echo '<tbody>';
                    
                    while ($row = mysqli_fetch_assoc($result2)) {
                        $statusClass = 'status-'.$row['status'];
                        echo '<tr>';
                        //echo '<td>'.$row['marketName'].'</td>';
                        echo '<td><a href="market-page.php?name='.$row['marketName'].'">'.$row['marketName'].'</a></td>';
                        echo '<td>'.role($row['role']).'</td>';
                        echo '<td>'.$row['date'].'</td>';

                        
                        if ($row['status'] == 'Приглашение отправлено' || $row['status'] == 'Запрос отправлен')
                            echo '<td>
                                    <a href="requests.php?accept=1&market='.$row['senderDB'].'&role='.$row['role'].'&sender='.$row['receiverEmail'].'&type=out" class="action-btn">Принять</a>
                                    <a href="requests.php?accept=0&market='.$row['senderDB'].'&role='.$row['role'].'&sender='.$row['receiverEmail'].'&type=out" class="action-btn">Отклонить</a>
                                </td>';
                        else echo '<td>-</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                } else {
                    echo '<p>Нет входящих запросов.</p>';
                }
            ?>

        </div>
        
    <!-- 2. ------------------------------------------------------------------------------------------------------------------------------->
        <div id="outgoing-tab" class="requests-section" style="display: none;">
            <h2>Запросы на участие</h2>
            <?php
                // Получаем исходящие приглашения
                $result1 = mysqli_query($connection, "SELECT * FROM $tableNameInc WHERE senderEmail = '$email' ORDER BY `date` DESC"); // Пользователь отправил запрос

                if (mysqli_num_rows($result1) > 0) {
                    echo '<table class="requests-table">';
                    echo '<thead><tr>
                            <th>Получатель</th>
                            <th>Роль</th>
                            <th>Отправлено</th>
                            <th>Статус</th>
                            <th>Действие</th>
                          </tr></thead>';
                    echo '<tbody>';
                    
                    while ($row = mysqli_fetch_assoc($result1)) {
                        $statusClass = 'status-'.$row['status'];
                        echo '<tr>';
                        echo '<td>'.$row['marketName'].'</td>';
                        echo '<td>'.role($row['role']).'</td>';
                        echo '<td>'.$row['date'].'</td>';
                        echo '<td class="'.$statusClass.'">'.$row['status'].'</td>';
                        
                        if ($row['status'] == 'Приглашение отправлено' || $row['status'] == 'Запрос отправлен')
                            echo '<td><a href="requests.php?accept=2&market='.$row['receiverDB'].'&role='.$row['role'].'&sender='.$row['senderEmail'].'&type=inc" class="action-btn">Отозвать</a></td>';
                        else if ($row['status'] == 'Принято' || $row['status'] == 'Отклонено') {
                            echo '<td><a href="requests.php?accept=2&market='.$row['receiverDB'].'&role='.$row['role'].'&sender='.$row['senderEmail'].'&type=inc" class="action-btn">Ок</a></td>';
                        }
                        else
                            echo '<td>-</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                } else {
                    echo '<p>Нет исходящих приглашений.</p>';
                }
            ?>

            <br>
            <h2>Приглашения</h2>
            <?php
                // Получаем исходящие приглашения
                $result2 = mysqli_query($connection, "SELECT * FROM $tableNameOut WHERE managerEmail = '$email' ORDER BY `date` DESC"); // Ярмарка отправила запрос

                if (mysqli_num_rows($result2) > 0) {
                    echo '<table class="requests-table">';
                    echo '<thead><tr>
                            <th>Ярмарка</th>
                            <th>Получатель</th>
                            <th>Роль</th>
                            <th>Отправлено</th>
                            <th>Статус</th>
                            <th>Действие</th>
                          </tr></thead>';
                    echo '<tbody>';
                                              

                    while ($row = mysqli_fetch_assoc($result2)) {
                        $recEm = $row['receiverEmail'];
                        $recName = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM market.users WHERE email = '$recEm'"))['login'];  
                        $statusClass = 'status-'.$row['status'];
                        echo '<tr>';
                        echo '<td>'.$row['marketName'].'</td>';
                        echo '<td>'.$row['receiverEmail'].' ('.$recName.')</td>';
                        echo '<td>'.role($row['role']).'</td>';
                        echo '<td>'.$row['date'].'</td>';
                        echo '<td class="'.$statusClass.'">'.$row['status'].'</td>';
                        
                        if ($row['status'] == 'Приглашение отправлено' || $row['status'] == 'Запрос отправлен')
                            echo '<td><a href="requests.php?accept=2&market='.$row['senderDB'].'&role='.$row['role'].'&sender='.$row['receiverEmail'].'&type=out" class="action-btn">Отозвать</a></td>';
                        else if ($row['status'] == 'Принято' || $row['status'] == 'Отклонено') {
                            echo '<td><a href="requests.php?accept=2&market='.$row['senderDB'].'&role='.$row['role'].'&sender='.$row['receiverEmail'].'&type=out" class="action-btn">Ок</a></td>';
                        } else
                            echo '<td>-</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                } else {
                    echo '<p>Нет исходящих приглашений.</p>';
                }
            ?>
        </div>


    <!-- 3. ------------------------------------------------------------------------------------------------------------------------------->
        <div id="new-tab" class="requests-section" style="display: none;">
            <h2>Создать новый запрос на участие</h2>
            <div class="new-request-form">
                <form method="POST" action="requests.php">
                    <div class="form-group">
                        <label for="market">Ярмарка:</label>
                        <select id="market" name="market" required>
                            <option value="">Выберите ярмарку</option>
                            <?php
                                $dbNames = mysqli_query($connection, "SELECT `database-name` FROM `market`.`all-existing-markets`");
                                while ($market = mysqli_fetch_assoc($dbNames)) {
                                    $dbname = $market['database-name'];
                                    $marketName = mysqli_fetch_assoc(mysqli_query($connection, "SELECT `name` FROM `$dbname`.`market-info`"))['name'];
                                    echo '<option value="'.$market['database-name'].'">'.$marketName.'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Роль:</label>
                        <select id="role" name="role" required>
                            <option value="">Выберите роль</option>
                            <option value="seller">Продавец</option>
                            <option value="volunteer">Волонтер</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="create_request" class="submit-btn">Отправить запрос</button>
                </form>
            </div>
        </div>
    </main>

    <script src='scripts\functions.js'></script>
    <script>
        function showTab(tabName) {
            // Скрыть все табы
            document.getElementById('incoming-tab').style.display = 'none';
            document.getElementById('outgoing-tab').style.display = 'none';
            document.getElementById('new-tab').style.display = 'none';
            
            // Убрать активный класс у всех табов
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Показать выбранный таб и добавить активный класс
            document.getElementById(tabName + '-tab').style.display = 'block';
            event.currentTarget.classList.add('active');
        }
    </script>
    <?php
        function role($str) {
            $eng = array('seller', 'manager', 'visitor', 'volunteer');
            $ru = array('продавец', 'менеджер', 'посетитель', 'волонтёр');
            return str_replace($eng, $ru, $str);
        }

        mysqli_query($connection, "DROP TABLE IF EXISTS $tableNameInc");
        mysqli_query($connection, "DROP TABLE IF EXISTS $tableNameOut");

    ?>
</body>
</html>
