<!-- layout.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Project</title>
    <link rel="stylesheet" href="public/css/layout.css">
    <link href="https://fonts.googleapis.com/css2?family=Beiruti:wght@200..900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Kiểm tra session đã tồn tại và hiển thị thông báo chào
        <?php if (isset($_SESSION['user']) && !$_SESSION['welcome_shown']): ?>
            window.onload = function() {
                var username = '<?php echo $_SESSION['user']['name']; ?>';
                Swal.fire({
                    title: 'Book Project chào bạn, ' + username + '!',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 3000 // Tự đóng sau 3 giây
                }).then(() => {
                    <?php $_SESSION['welcome_shown'] = true; ?>
                });
            };
        <?php endif; ?>
    </script>
</head>
<body>
    <header>
        <div class="headleft">
            <a href="index.php?action=home" id="logolink">
                <img src="public/images/system/logo.png" alt="Book Project Logo"/>
            </a>
            <div class="search-container">
                <form id="searchForm" action="index.php?action=search" method="GET">
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="submit">
                            <img src="public/images/system/magnifiying-glass.png" width="20" height="20" alt="search">
                        </button>
                        <input type="text" class="form-control" name="q" id="searchInput" placeholder="Tìm sách..." autocomplete="off">
                    </div>
                    <ul class="list-group position-absolute" id="searchDropdown" style="z-index: 1000; display:none;"></ul>
                </form>
            </div>
        </div>
        <div class="headright">
            <?php if (isset($_SESSION['user'])): ?>
                <div class="option">
                    <div class="dropdown">
                        <img id="notificationBtn" src="public/images/system/bell.png" 
                            width="25" height="25" data-bs-toggle="dropdown">
                        <ul class="dropdown-menu dropdown-menu-end" id="notificationList"
                            style="width: 300px; max-height: 300px; overflow-y: auto;">
                            <li class="text-center p-2">Đang tải...</li>
                        </ul>
                    </div>
                    <?php
                    $avatar = isset($_SESSION['user']['avatar']) && file_exists($_SESSION['user']['avatar'])
                        ? $_SESSION['user']['avatar']
                        : "public/images/system/default-avatar.png";
                    ?>
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img id="avt" src="<?php echo $avatar; ?>"  alt="Avatar" class="rounded-circle" width="50" height="50">
                        <?php echo $_SESSION['user']['name']; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="index.php?action=profile">Thông tin cá nhân</a></li>
                        <li><a class="dropdown-item" href="index.php?action=bookManagement">Quản lý thư viện</a></li>
                        <li><a class="dropdown-item" href="index.php?action=invoiceManagement">Quản lý giao dịch</a></li>
                        <li><a class="dropdown-item" href="index.php?action=logout">Đăng xuất</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="option">
                    <a href="index.php?action=login">Đăng Nhập</a>
             </div>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <?php include($view); ?>
    </main>
    <footer>
        <p>&copy; 20127609 - Trần Đại Quốc - Book Project</p>
    </footer>
<script>const USER_ID = <?= $_SESSION['user']['user_id']; ?>;</script>
<script src="public/js/layout_notification.js" defer></script>
<script src="public/js/layout_search.js" defer></script>
</body>
</html>
