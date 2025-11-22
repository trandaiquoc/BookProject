<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/manageBook.css">
</head>
<body>
    <?php $current_tab = $_GET['tab'] ?? 'favorite'; ?>
    <div class="tabs">
        <button class="tab-button <?= $current_tab=='favorite'?'active':'' ?>" data-tab="favorite"> Sách Đã Lưu</button>
        <button class="tab-button <?= $current_tab=='purchase'?'active':'' ?>" data-tab="purchase">Sách Đã Mua</button>
        <button class="tab-button <?= $current_tab=='reading'?'active':'' ?>" data-tab="reading">Lịch Sử Đọc Sách</button>
    </div>
    <div class="tab-content">
        <div id="favorite" class="tab-panel <?= $current_tab=='favorite'?'active':'' ?>">
            <?php if (!empty($favoritedBooks)): ?>
                <h2>Sách Đã Lưu</h2>
                <div class="book-list">
                    <?php foreach ($favoritedBooks as $b): ?>
                        <a class="book-link" href="index.php?action=bookProfile&book_id=<?= urlencode($b['book']['info']['book_id']) ?>">
                            <div class="book-item">
                                <?php $imgSrc = !empty($b['book']['info']['avatar']) ? $book['avatar'] : 'public/images/system/book.jpg'; ?>
                                <img src="<?= $imgSrc ?>" alt="book avatar" class="book-avatar">
                                <div class="book-info">
                                    <strong><?= htmlspecialchars($b['book']['info']['name']) ?></strong><br>
                                    Tác giả: <?= htmlspecialchars($b['book']['info']['author']) ?><br>
                                    Thể loại: 
                                    <span class="categories">
                                        <?= implode(', ', array_column($b['book']['categories'], 'name')) ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div id="purchase" class="tab-panel <?= $current_tab=='purchase'?'active':'' ?>">
            <?php if (!empty($purchasedBooks)): ?>
            <h2>Sách Đã Mua</h2>
            <div class="book-list">
                <?php foreach ($purchasedBooks as $b): ?>
                    <a class="book-link" href="index.php?action=bookProfile&book_id=<?= urlencode($b['book']['info']['book_id']) ?>">
                        <div class="book-item">
                            <?php $imgSrc = !empty($b['book']['info']['avatar']) ? $book['avatar'] : 'public/images/system/book.jpg'; ?>
                            <img src="<?= $imgSrc ?>" alt="book avatar" class="book-avatar">
                            <div class="book-info">
                                <strong><?= htmlspecialchars($b['book']['info']['name']) ?></strong><br>
                                Tác giả: <?= htmlspecialchars($b['book']['info']['author']) ?><br>
                                Thể loại: 
                                <span class="categories">
                                    <?= implode(', ', array_column($b['book']['categories'], 'name')) ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
        <div id="reading" class="tab-panel <?= $current_tab=='reading'?'active':'' ?>">
            <?php if (!empty($readingBooks)): ?>
            <h2>Sách Đang Đọc</h2>
            <div class="book-list">
                <?php foreach ($readingBooks as $b): ?>
                    <a class="book-link" href="index.php?action=bookProfile&book_id=<?= urlencode($b['book']['info']['book_id']) ?>">
                        <div class="book-item">
                            <?php $imgSrc = !empty($b['book']['info']['avatar']) ? $book['avatar'] : 'public/images/system/book.jpg'; ?>
                            <img src="<?= $imgSrc ?>" alt="book avatar" class="book-avatar">
                            <div class="book-info">
                                <strong><?= htmlspecialchars($b['book']['info']['name']) ?></strong><br>
                                Tác giả: <?= htmlspecialchars($b['book']['info']['author']) ?><br>
                                Thể loại: 
                                <span class="categories">
                                    <?= implode(', ', array_column($b['book']['categories'], 'name')) ?>
                                </span><br>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?= intval($b['progress']) ?>%;"></div>
                                </div>
                                <span class="progress-text"><?= intval($b['progress']) ?>%</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>

<?php if (isset($message)): ?>
    <p style="color:red;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>
<script>
// Tabs
document.querySelectorAll('.tab-button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});
</script>
</body>