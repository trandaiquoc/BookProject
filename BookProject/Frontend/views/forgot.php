<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/login.css">
    <link rel="stylesheet" href="public/css/forgot.css">
    <title>Đổi mật khẩu</title>
</head>
<body>
    <div class="changePassContainer">
        <h1><?= isset($step) && $step === 'reset' ? 'Đổi mật khẩu' : 'Quên mật khẩu' ?></h1>

        <?php if (empty($step)): ?>
            <!-- ======= BƯỚC 1: NHẬP USERNAME ======= -->
            <form class="form-group" method="POST" action="index.php?action=forgotPassword">
                <div class="user">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <button class="button" type="submit">Xác minh</button>
            </form>

        <?php else: ?>
            <!-- ======= BƯỚC 2: ĐỔI MẬT KHẨU ======= -->
            <form class="form-group" method="POST" action="index.php?action=updatePassword">
                <!-- Thêm hidden input để xác định gọi từ đâu -->
                <input type="hidden" 
                       name="context" 
                       value="<?= (isset($_GET['step']) && $_GET['step'] === 'reset') ? 'profile' : 'forgot' ?>">

                <div class="user">
                    <label for="username2">Tên đăng nhập:</label>
                    <input id="username2" 
                           type="text" 
                           name="username" 
                           value="<?= htmlspecialchars($username ?? '') ?>" 
                           readonly>
                </div>

                <div id="pass">
                    <label for="password">Mật khẩu mới:</label>
                    <input id="password" type="password" name="password" required>

                    <label for="passwordagain">Nhập lại mật khẩu:</label>
                    <input id="password2" type="password" name="passwordagain" required>
                </div>

                <button class="button" type="submit">Đổi mật khẩu</button>
            </form>
        <?php endif; ?>

        <!-- ======= HIỂN THỊ THÔNG BÁO ======= -->
        <?php if (!empty($message)): ?>
            <p style="color:red; margin-top:10px;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
