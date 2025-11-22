<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="public/css/bookProfile.css">
    <link rel="stylesheet" href="public/css/bookReader.css">
    <?php if (!empty($_SESSION['paymentnotif'])): ?>
    <script>
        Swal.fire({
            title: <?= json_encode($_SESSION['paymentnotif']) ?>,
            icon: 'error',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php unset($_SESSION['paymentnotif']); ?>
    <?php endif; ?>
</head>

<body>
    <div id="main">
        <div id="left">
            <?php $imgSrc = !empty($book['avatar']) ? $book['avatar'] : 'public/images/system/book.jpg'; ?>

            <div class="imgbook">
                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($book['name']) ?>" title="<?= htmlspecialchars($book['name']) ?>">
            </div>
            <div class="book-operations">
                <div class="readbook">
                    <?php if(isset($book['price']) && $book['price'] == 0 || isset($purchased) && $purchased != 0): ?>
                        <!-- javascript:void(0); click không reload lại trang -->
                        <a href="javascript:void(0);" 
                            class="btn-read"
                            id="btn-read"
                            data-url="<?= $book['url'] ?>"
                            data-book="<?= $book['book_id'] ?>"
                            data-user="<?= $_SESSION['user']['user_id'] ?>"
                            data-totalpage="<?= $book['totalpage'] ?? 1 ?>">
                            Đọc sách
                        </a>
                        <!-- JavaScript có thể truy cập bằng element.dataset.url, ... -->
                    <?php else: ?>
                        <a href="index.php?action=payment&book_id=<?= $book['book_id']?>&book_name=<?= urlencode($book['name']) ?>" class="btn-buy">
                            Mua: <?= number_format($book['price'], 0, ',', '.') ?> VNĐ
                        </a>
                    <?php endif; ?>
                </div>
                <div class="Save">
                    <?php 
                        $isSaved = $saved ?? 0; // 0 = chưa lưu, 1 = đã lưu
                        $saveIcon = $isSaved 
                            ? 'public/images/system/unSaveBook.png' 
                            : 'public/images/system/saveBook.png';

                        // Nếu đã lưu → unlink
                        $saveLink = $isSaved
                            ? "index.php?action=unSaveBook&book_id=" . $book['book_id']
                            : "index.php?action=saveBook&book_id=" . $book['book_id'];
                    ?>
                    
                    <a href="<?= $saveLink ?>" class="btn-save-icon">
                        <img src="<?= $saveIcon ?>" alt="save-icon">
                    </a>
                </div>
            </div>
        </div>

        <div id="right">
            <h1 id="title"><?= htmlspecialchars($book['name']); ?></h1>

            <?php $current_tab = $_GET['tab'] ?? 'description'; ?>

            <div class="tabs">
                <button class="tab-button <?= $current_tab=='description'?'active':'' ?>" data-tab="description">Mô tả</button>
                <button class="tab-button <?= $current_tab=='reviews'?'active':'' ?>" data-tab="reviews">Đánh giá</button>
            </div>

            <div class="tab-content">
                <!-- TAB MÔ TẢ -->
                <div id="description" class="tab-panel <?= $current_tab=='description'?'active':'' ?>">
                    <p><?= htmlspecialchars($book['describe'] ?? 'Chưa có mô tả'); ?></p>
                    <p><strong>Tác giả: </strong><?= htmlspecialchars($book['author'] ?? 'Chưa có'); ?></p>
                    <p><strong>Thể loại: </strong>

                    <?php if ($categories): ?>
                        <?php foreach ($categories as $i=>$c): ?>
                            <?= htmlspecialchars($c['name']) ?><?= $i < count($categories)-1 ? ', ' : '' ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        Chưa có mô tả
                    <?php endif; ?>
                    </p>

                    <p><strong>Ngôn ngữ: </strong><?= htmlspecialchars($book['language'] ?? 'N/A'); ?></p>
                    <p><strong>Phiên bản: </strong><?= htmlspecialchars($book['edition'] ?? 'N/A'); ?></p>
                    <p><strong>Lượt xem: </strong><?= htmlspecialchars($book['visits'] ?? '0'); ?></p>

                    <!-- Rating -->
                    <span class="book-stars">
                        <?php
                            $rating = floatval($book['rating'] ?? 0);
                            $finalRating = ($rating - floor($rating) > 0.5) ? ceil($rating) : floor($rating);
                        ?>
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <span class="sao <?= $i <= $finalRating ? 'selected':'' ?>">&#9733;</span>
                        <?php endfor; ?>
                    </span>

                    <script>
                        const BOOK_ID = <?= intval($book['book_id']) ?>;
                        const USER_ID = <?= intval($_SESSION['user']['user_id']) ?>;
                    </script>
                </div>

                <!-- TAB ĐÁNH GIÁ -->
                <div id="reviews" class="tab-panel <?= $current_tab=='reviews'?'active':'' ?>">
                    <div class="rating">
                        <span class="star" data-value="1">&#9733;</span>
                        <span class="star" data-value="2">&#9733;</span>
                        <span class="star" data-value="3">&#9733;</span>
                        <span class="star" data-value="4">&#9733;</span>
                        <span class="star" data-value="5">&#9733;</span>
                    </div>

                    <div class="comment-box">
                        <textarea id="comment" placeholder="Nhập bình luận..."></textarea>
                        <button id="submit-comment">Gửi bình luận</button>
                    </div>

                    <div class="comments-list">
                        <?php foreach($comments as $c): ?>
                        <div class="comment-item">
                            <img src="<?= $c['avatar'] ?? 'public/images/system/default-avatar.png' ?>" class="comment-avatar">

                            <div class="comment-content">
                                <div class="comment-header">
                                    <strong><?= htmlspecialchars($c['username']) ?></strong>
                                    <span class="comment-stars">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <span class="sao <?= $i <= intval($c['score']) ? 'selected':'' ?>">&#9733;</span>
                                        <?php endfor; ?>
                                    </span>
                                </div>

                                <div class="comment-text"><?= htmlspecialchars($c['content']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a class="page-btn" href="?action=bookProfile&book_id=<?= $book['book_id'] ?>&page=<?= $current_page-1 ?>&tab=reviews">«</a>
                        <?php endif; ?>

                        <?php for($p=1; $p <= $total_pages; $p++): ?>
                            <a class="page-btn <?= $p==$current_page?'active':'' ?>"
                                href="?action=bookProfile&book_id=<?= $book['book_id'] ?>&page=<?= $p ?>&tab=reviews">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a class="page-btn" href="?action=bookProfile&book_id=<?= $book['book_id'] ?>&page=<?= $current_page+1 ?>&tab=reviews">»</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF READER OVERLAY -->
    <div id="reader-overlay">
        <div id="reader-container">
            <button id="reader-close">✖</button>

            <div id="pdf-wrapper">
                <canvas id="pdf-canvas"></canvas>
            </div>

            <div id="reader-controls">
                <button id="prevPage"><strong>Trang trước</strong></button>
                <span id="pageInfo"><strong>1 / 1</strong></span>
                <button id="nextPage"><strong>Trang sau</strong></button>
                <button id="zoomOut" style="display:none;">Thu nhỏ</button>
                <button id="zoomIn">Phóng to</button>

            </div>
        </div>
    </div>
<?php if (isset($_GET['error'])): ?>
    <div class="error-message">
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>


<!-- Comment Ajax + Bootstrap -->
<script src="public/js/bookProfile_commentAjax_Pagination.js" defer></script>
<!-- PDF.js Core -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.min.js"></script>
<!-- Reader -->
<script src="public/js/bookProject_Reader.js"></script>
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
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnRead = document.getElementById('btn-read');
    if (btnRead && window.openReader) {
        btnRead.addEventListener('click', () => {
            const url = btnRead.dataset.url;
            const bookId = parseInt(btnRead.dataset.book);
            const userId = parseInt(btnRead.dataset.user);

            openReader(url, bookId, userId);
        });
    }
});
</script>
</body>
</html>
