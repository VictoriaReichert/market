<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить участников</title>
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

        // Получаем всех участников (продавцов и волонтеров)
        $members = mysqli_query($connection, "SELECT email, role FROM `market-members` WHERE role = 'seller' OR role = 'volunteer'");
        $membersList = [];
        while ($row = mysqli_fetch_assoc($members)) {
            $membersList[] = $row;
        }

        // Обработка удаления участника
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_member'])) {
            $emailToRemove = mysqli_real_escape_string($connection, $_POST['email']);
            $roleToRemove = mysqli_real_escape_string($connection, $_POST['role']);
            mysqli_query($connection, "DELETE FROM `market-members` WHERE email = '$emailToRemove' AND `role` = '$roleToRemove'");
            
            if ($roleToRemove == 'volunteer') {
                // Получаем ярмарки, где пользователь волонтер
                $showPast = false; $sort = 'id';
                include 'scripts/getVolunteerMarkets.php'; 
                if (mysqli_num_rows($allMarketsInfo) == 0) {
                    mysqli_query($connection, "DELETE FROM `market`.`user-roles`
                            WHERE email = '$emailToRemove' AND `role` = 'volunteer';
                            ");
                }
            }

            // Обновляем список после удаления
            header("Location: delete-members.php");
            exit();
        }

        include 'header.php';
    ?>

    <main>
        <div class="create-market-container">
            <h1>Изменить участников</h1>
            <nav>
                <table border="0" width="50%" align="center">
                    <tr> 
                        <td><a href="add-members.php" class="menuButton">Добавить</a></td>
                        <td><a href="delete-members.php" class="menuButtonCur">Удалить</a></td>
                    </tr>
                </table>
            </nav><br>
            
            <div class="members-section">
                <h3>Текущие участники</h3>
                <p>Выберите участников, которых хотите удалить с ярмарки</p>
                
                <div class="members-list">
                    <?php if (!empty($membersList)): ?>
                        <?php foreach ($membersList as $member): ?>
                            <div class="member-item">
                                <div class="member-info">
                                    <span class="member-email"><?= htmlspecialchars($member['email']) ?></span>
                                    <span class="member-role">
                                        <?= $member['role'] == 'seller' ? 'Продавец' : 'Волонтер' ?>
                                    </span>
                                </div>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="email" value="<?= htmlspecialchars($member['email']) ?>">
                                    <input type="hidden" name="role" value="<?= htmlspecialchars($member['role']) ?>">
                                    <button type="submit" name="remove_member" class="remove-btn">Удалить</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-members">Нет участников для удаления</div>
                    <?php endif; ?>
                </div>
            </div>
            <a href="scripts\returnToMarket.php" class="cancel-link"><p align="center">Отмена</p></a>
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

        // // Подтверждение удаления
        // document.querySelectorAll('.remove-btn').forEach(btn => {
        //     btn.addEventListener('click', function(e) {
        //         if (!confirm('Вы уверены, что хотите удалить этого участника?')) {
        //             e.preventDefault();
        //         }
        //     });
        // });
    </script>
</body>
</html>