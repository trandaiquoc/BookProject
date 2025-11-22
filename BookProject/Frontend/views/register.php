<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/register.css">
</head>
<body>
<div class="register-container">
      <h1><b>Đăng Ký Tài Khoản</b></h1>
      <form class="form-group" action="index.php?action=register" method="post">
        <div id="user">
            <label>Tên Tài Khoản</label>
            <input type="text" name="username" id="username"/>
        </div>
        <div id="displayName">
            <label>Tên Hiển Thị</label>
            <input type="text" name="name" id="name"/>
        </div>
        <div id="pass">
            <label>Mật Khẩu</label>
            <input type="password" name="password" id="password"/>
            <label>Nhập Lại Mật Khẩu</label>
            <input type="password" name="passwordagain" id="passwordagain"/>
                <div class="password-options">
                    <div class="hideshow-container" onclick="togglePasswordVisibility()">
                        <img id="toggleIcon" src="./public/images/show.png" alt="Toggle Password Visibility" width="30px" height="30px"/>
                        <p id="toggleText">Hiện Mật Khẩu</p>
                    </div>
                </div>
        </div>
        <button class="register-button">Đăng Ký</button>
      </form>    
      <?php if (isset($message)): ?>
          <p style="color: red;"><?php echo $message ?></p>
      <?php endif; ?>  
</div>
<script>
        function togglePasswordVisibility() {
            var imgElement = document.getElementById("toggleIcon");
            var passwordInput = document.getElementById("password");
            var txtElement = document.getElementById("toggleText");
            var passwordInput2 = document.getElementById("passwordagain");
            // Đổi hình ảnh giữa hide.png và show.png
            if (passwordInput.type === "password" && passwordInput2.type === "password") {
                passwordInput.type = "text";
                passwordInput2.type = "text";
                imgElement.src = "./public/images/hide.png";
                txtElement.textContent = "Ẩn Mật Khẩu";
            } else {
                passwordInput.type = "password";
                passwordInput2.type = "password";
                imgElement.src = "./public/images/show.png";
                txtElement.textContent = "Hiện Mật Khẩu";
            }
        }
    </script>
</body>
</html>