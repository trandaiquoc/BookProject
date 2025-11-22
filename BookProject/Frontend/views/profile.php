<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/profile.css">
    <?php if (!empty($changepass_success)): ?>
    <script>
        Swal.fire({
            title: 'Đổi mật khẩu thành công!',
            icon: 'success',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php endif; ?>
    <?php if (!empty($changeProfile_success)): ?>
    <script>
        Swal.fire({
            title: 'Cập nhật thông tin thành công!',
            icon: 'success',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php endif; ?>
</head>
<body>
<?php
    $uploadedBooks = $uploadedBooks ?? [];
    $favoritedBooks = $favoritedBooks ?? [];
?>
<div class="main">
    <div id="left">
        <?php
        $avatar = isset($_SESSION['user']['avatar']) && file_exists($_SESSION['user']['avatar'])
            ? $_SESSION['user']['avatar']
            : "public/images/system/default-avatar.png";
        ?>
        <img src="<?php echo $avatar; ?>" alt="Avatar" id="avatar">
        <h1><?php echo $_SESSION['user']['name']; ?></h1>
    </div>
    <div id="right">
        <div class="info">
            <h2>Thông Tin Cá Nhân</h2>
            <p><strong>Tên đăng nhập:</strong> <?php echo $_SESSION['user']['username']; ?></p>
            <p><strong>Ngày sinh:</strong> <?php echo $_SESSION['user']['birthday'] ?? 'Chưa cập nhật'; ?></p>
            <p><strong>Số dư:</strong> 
                <?= isset($_SESSION['user']['balance']) ? number_format($_SESSION['user']['balance'], 0, ',', '.') : 'Chưa cập nhật'; ?> VNĐ
            </p>
        <div class="book-section">
            <h2>Sách Đã Tải Lên</h2>
            <div class="book-list">
                <?php if ($favoritedBooks): ?>
                    <?php foreach ($uploadedBooks as $book): 
                        $imgSrc = !empty($book['avatar']) ? $book['avatar'] : 'public/images/system/book.jpg';
                        $bookId = isset($book['book_id']) ? $book['book_id'] : '';
                    ?>
                    <a class="book-link" href="index.php?action=bookProfile&book_id=<?= urlencode($bookId) ?>">
                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($book['name']) ?>" title="<?= htmlspecialchars($book['name']) ?>">
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Không có sách nào!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="book-section">
            <h2>Sách yêu thích</h2>
            <div class="book-list">
                <?php if ($favoritedBooks): ?>
                    <?php foreach ($favoritedBooks as $book): 
                        $imgSrc = !empty($book['avatar']) ? $book['avatar'] : 'public/images/system/book.jpg';
                        $bookId = isset($book['book_id']) ? $book['book_id'] : '';
                    ?>
                    <a class="book-link" href="index.php?action=bookProfile&book_id=<?= urlencode($bookId) ?>">
                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($book['name']) ?>" title="<?= htmlspecialchars($book['name']) ?>">
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Không có sách nào!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="buttons">
            <button type="button"
                onclick="window.location.href='index.php?action=updateProfile';"
                class="btn update">
                Cập Nhật Hồ Sơ
            </button>
            <button type="button"
                onclick="window.location.href='index.php?action=forgotPassword&step=reset';"
                class="btn update">
                Đổi Mật Khẩu
        </button>
        </div>
    </div>
</div>
<?php if (isset($message)): ?>
    <p style="color: red;"><?php echo $message; ?></p>
<?php endif; ?>  
</body>