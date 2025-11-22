<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cập nhật hồ sơ</title>
  <link rel="stylesheet" href="public/css/updateProfile.css" />
</head>
<body>
    <div>
        <h1 id="title">Cập nhật hồ sơ cá nhân</h1>
    </div>
    <div class="container">
        <form action="index.php?action=updateProfile" method="POST" enctype="multipart/form-data" class="profile-form">
            <div id="left">
                <!-- Ảnh đại diện -->
                <div class="avatar-section">
                    <?php
                        $avatar = isset($_SESSION['user']['avatar']) && file_exists($_SESSION['user']['avatar'])
                        ? $_SESSION['user']['avatar']
                        : "public/images/system/default-avatar.png";
                    ?>
                    <img src="<?php echo $avatar; ?>" alt="Avatar" id="avatarPreview">
                </div>
                <div class="avatar-update">
                    <label for="avatar" class="custom-file-label">📷 Chọn ảnh đại diện</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(event)" hidden />
                </div>
            </div>
            <div id="right">
                <!-- Tên -->
                <div class="form-group">
                    <label for="name">Tên hiển thị:</label>
                    <input type="text" id="name" name="name" value="<?php echo $_SESSION['user']['name']; ?>" required />
                </div>
                <!-- Ngày sinh -->
                <div class="form-group">
                    <label for="birthday">Ngày sinh:</label>
                    <input type="date" id="birthday" name="birthday" value="<?php echo $_SESSION['user']['birthday']; ?>" required/>
                </div>

                <!-- Nút lưu -->
                <div class="buttons">
                    <button type="submit" class="btn save">Lưu thay đổi</button>
                    <button onclick="window.location.href='index.php?action=profile';" 
                    type="reset" class="btn cancel">Hủy</button>
                </div>
            </div>
        </form>
    </div>
    <?php if (isset($message)): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>  
    <script>
        // Hàm hiển thị ảnh vừa chọn
        function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
        }
    </script>
</body>
</html>
