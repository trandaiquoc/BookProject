<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/login.css">
    <?php if (!empty($register_success)): ?>
    <script>
        Swal.fire({
            title: 'Đăng ký thành công!',
            icon: 'success',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php endif; ?>
    <?php if (!empty($changepass_success)): ?>
    <script>
        Swal.fire({
            title: 'Đổi mật khẩu thành công hãy đăng nhập lại!',
            icon: 'success',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php endif; ?>
</head>
<body>
<div class="login-container">
      <h1><b>Trang Đăng Nhập</b></h1>
      <form class="form-group" action="index.php?action=login" method="post">
        <div id="user">
            <label>Tên tài khoản</label>
            <input type="text" name="username" id="username"
            value="<?php echo isset($prefillUsername) ? htmlspecialchars($prefillUsername) : ''; ?>" />
        </div>
        <div id="pass">
            <label>Mật Khẩu</label>
            <input type="password" name="password" id="password"
            value="<?php echo isset($prefillPassword) ? htmlspecialchars($prefillPassword) : ''; ?>" />
                <div class="password-options">
                    <div class="hideshow-container" onclick="togglePasswordVisibility()">
                        <img id="toggleIcon" src="./public/images/system/show.png" alt="Toggle Password Visibility" width="30px" height="30px"/>
                        <p id="toggleText">Hiện Mật Khẩu</p>
                    </div>

                    <div class="account-links">
                        <a href="index.php?action=register" class="register-link">Bạn chưa có tài khoản?</a><br>
                        <a href="index.php?action=forgotPassword" class="forgot-link">Quên mật khẩu?</a>
                    </div>
                </div>
        </div>
        <button class="login-button">Đăng nhập</button>
      </form>    
      <?php if (isset($message)): ?>
          <p style="color: red;"><?php echo $message; ?></p>
      <?php endif; ?>  
</div>
<script>
        function togglePasswordVisibility() {
            var imgElement = document.getElementById("toggleIcon");
            var passwordInput = document.getElementById("password");
            var txtElement = document.getElementById("toggleText");
            
            // Đổi hình ảnh giữa hide.png và show.png
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                imgElement.src = "./public/images/system/hide.png";
                txtElement.textContent = "Ẩn Mật Khẩu";
            } else {
                passwordInput.type = "password";
                imgElement.src = "./public/images/system/show.png";
                txtElement.textContent = "Hiện Mật Khẩu";
            }
        }
    </script>
</body>
</html>