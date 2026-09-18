<?php while($marketInfo = mysqli_fetch_assoc($allMarketsInfo)): ?>
    <div class="market-card">
    <?php 
        $pic = $marketInfo['pictureFileName'];
    ?>

    <img src="<?php echo $pic?>" class="market-image">
    <div class="market-info">
        <h3><?= htmlspecialchars($marketInfo['name']) ?></h3>
        <p class="market-dates">
            <?= date('d.m.Y', strtotime($marketInfo['openingDate'])) ?> - 
            <?= date('d.m.Y', strtotime($marketInfo['closingDate'])) ?>
        </p>
        <p class="market-description">
            <?= htmlspecialchars(mb_substr($marketInfo['description'], 0, 100)) ?>...
        </p>
        <a href="market-page.php?name=<?= $marketInfo['name'] ?>" class="learn-more-btn">Подробнее</a>
    </div>
</div>
<?php endwhile; 
    mysqli_query($connection, "DROP TABLE IF EXISTS $tableName");
?>