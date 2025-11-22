<?php

class UserController extends Controller{
    private $apiUrlLogin;
    private $apiUrlRegister;
    private $apiUrlCheckUser;
    private $apiUrlResetPassword;
    private $apiUrlUpdateProfile;
    private $apiUrlUploadedBooks;
    private $apiUrlFavoritedBooks;

    public function __construct() {
        // Đường dẫn đến API Backend
        $this->apiUrlLogin = "http://localhost/BookProject/BookProject/Backend/index.php?action=login";
        $this->apiUrlRegister = "http://localhost/BookProject/BookProject/Backend/index.php?action=register";
        $this->apiUrlCheckUser = "http://localhost/BookProject/BookProject/Backend/index.php?action=checkUser";
        $this->apiUrlResetPassword = "http://localhost/BookProject/BookProject/Backend/index.php?action=resetPassword";
        $this->apiUrlUpdateProfile = "http://localhost/BookProject/BookProject/Backend/index.php?action=updateProfile";
        $this->apiUrlUploadedBooks = "http://localhost/BookProject/BookProject/Backend/index.php?action=getUploadedBooks";
        $this->apiUrlFavoritedBooks = "http://localhost/BookProject/BookProject/Backend/index.php?action=getFavoritedBooks";
    }
    public function showLogin()
    {
        $this->loadView('login.php');
    }
    public function showRegister()
    {
        $this->loadView('register.php');
    }
    public function showForgot() {

        if (isset($_GET['step']) && $_GET['step'] === 'reset') {
            // Người dùng bấm "Đổi mật khẩu" từ profile (đã login)
            if (isset($_SESSION['user']['username'])) {
                $this->loadView('forgot.php', [
                    'step' => 'reset',
                    'username' => $_SESSION['user']['username']
                ]);
            }
        }
        else{
            $this->loadView('forgot.php');
        }
    }
    public function showProfile($data)
    {
        $data = [];
        $userid = $_SESSION['user']['user_id'];
        $response1 = $this->callAPIGetUploadedBooks($userid);
        $response2 = $this->callAPIGetFavoritedBooks($userid);
        $result1 = json_decode($response1, true);
        $result2 = json_decode($response2, true);
        if ($response1 === false || $result1 === null && $response2 === false || $result2 === null) {
            $this->loadView('profile.php', [
            'message' => 'Không nhận được phản hồi từ server hoặc dữ liệu không hợp lệ.',
            ]);
            return;
        }
        if (isset($result1['status']) && $result1['status'] === 'success' 
        && isset($result2['status']) && $result2['status'] === 'success') {         
           $this->loadView('profile.php', 
            ['uploadedBooks' => $result1['uploadedBooks'],
           'favoritedBooks' => $result2['favoritedBooks']]);
        } else {
            $this->loadView('profile.php',$data);
        }
    }
    private function callAPIGetUploadedBooks($user_id) {
        $data = json_encode(["user_id" => $user_id]);

        $ch = curl_init($this->apiUrlUploadedBooks);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    private function callAPIGetFavoritedBooks($user_id) {
        $data = json_encode(["user_id" => $user_id]);

        $ch = curl_init($this->apiUrlFavoritedBooks);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    public function showUpdateProfile()
    {
        $this->loadView('updateProfile.php');
    }
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $response = $this->callAPILogin($username, $password);
            $result = json_decode($response, true);
            if ($response === false || $result === null) {
            $this->loadView('login.php', [
                'message' => 'Không nhận được phản hồi từ server hoặc dữ liệu không hợp lệ.',
                'debug' => $response // tùy chọn, để xem nội dung backend trả
            ]);
            return;
        }
            if (isset($result['status']) && $result['status'] === 'success') {
                $_SESSION['user'] = $result['data'];
                $_SESSION['welcome_shown'] = false;
                    
                // Redirect to another page or perform other actions
                header('Location: index.php');
            } else {
                $errorMessage = $result['message'];
                $this->loadView('login.php', ['message' => $errorMessage]);
            }
        } else {
            $this->loadView('login.php');
        }
    }
    private function callAPILogin($username, $password) {
        $data = json_encode(["username" => $username, "password" => $password]);

        $ch = curl_init($this->apiUrlLogin);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php?action=home");
    }

    public function register() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $name = $_POST['name'] ?? '';
            $passwordagain = $_POST['passwordagain'] ?? '';

            $response = $this->callAPIRegister($username, $password, $name, $passwordagain);
            $result = json_decode($response, true);
            if ($response === false || !is_array($result)) {
                $this->loadView('register.php', [
                    'message' => 'Không nhận được phản hồi hợp lệ từ server.',
                    'debug' => $response
            ]);
            return;
        }
        if (isset($result['status']) && $result['status'] === 'success') {                
            // Gửi username và pass qua URL
            $UN = urlencode($username);
            $PW = urlencode($password);
            $data = [
                'prefillUsername' => $UN,
                'prefillPassword' => $PW,
                'register_success' => true
            ];
            $this->loadView('login.php', $data);
        } else {
            $errorMessage = $result['message'];
            $this->loadView('register.php', ['message' => $errorMessage]);
        }
        } else {
            $this->loadView('register.php');
        }
    }   
    private function callAPIRegister($username, $password, $name, $passwordagain) {
        $data = json_encode(["username" => $username, "password" => $password, "name" => $name, "passwordagain" => $passwordagain]);

        $ch = curl_init($this->apiUrlRegister);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function forgotPassword() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'] ?? '';

            $response = $this->callAPICheckUser($username);
            $result = json_decode($response, true);

            if ($result && $result['status'] === 'success') {
                $this->loadView('forgot.php', ['username' => $username, 'step' => 'reset']);
            } else {
                $msg = $result['message'] ?? 'Không tìm thấy tài khoản';
                $this->loadView('forgot.php', ['message' => $msg]);
            }
        } else {
            $this->loadView('forgot.php');
        }
    }
    private function callAPICheckUser($username) {
        $data = json_encode(['username' => $username]);
        $ch = curl_init($this->apiUrlCheckUser);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    public function updatePassword() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $user_id = $_SESSION['user']['user_id'] ?? 0;
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordagain = $_POST['passwordagain'] ?? '';
            $context = $_POST['context'] ?? 'forgot';

            $response = $this->callAPIResetPassword($username, $password, $passwordagain, $user_id);
            $result = json_decode($response, true);

            if ($result && $result['status'] === 'success') {
                $_SESSION['user']['password'] = $password;
                if ($context === 'profile') {
                    //Nếu đổi từ profile: ở lại trang và thông báo
                    $this->loadView('profile.php', [
                        'changepass_success' => true    
                    ]);
                } else {
                    // Nếu đổi từ forgot password: quay về login
                    $this->loadView('login.php', [
                        'prefillUsername' => $username,
                        'changepass_success' => true
                    ]);
                }
            } else {
                $msg = $result['message'] ?? 'Lỗi không xác định';
                $this->loadView('forgot.php', ['username' => $username, 'step' => 'reset', 'message' => $msg]);
            }
        }
    }
    private function callAPIResetPassword($username, $password, $passwordagain, $user_id) {
        $data = json_encode(['username' => $username, 'password' => $password, 'passwordagain' => $passwordagain, 'user_id' => $user_id]);
        $ch = curl_init($this->apiUrlResetPassword);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_SESSION['user']['username'] ?? '';
            $name = $_POST['name'] ?? '';
            $birthday = $_POST['birthday'] ?? '';
            $avatar = $_SESSION['user']['avatar'] ?? 'public/images/system/default-avatar.png';
            $user_id = $_SESSION['user']['user_id'];
            // --- Upload ảnh mới ---
            if (!empty($_FILES['avatar']['name'])) {
                $uploadDir = 'public/images/users/';
                $fileName = $user_id . '_' . basename($_FILES['avatar']['name']);
                $targetFile = $uploadDir . $fileName;

                // Di chuyển file upload
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                    $avatar = $targetFile;
                } else {
                    $msg = 'Lỗi khi tải ảnh lên!';
                    $this->loadView('updateProfile.php', ['message' => $msg]);
                    return;
                }
            }

            // --- Gửi API ---
            $response = $this->callAPIupdateProfile($username, $name, $birthday, $avatar, $user_id);
            $result = json_decode($response, true);

            if ($result && $result['status'] === 'success') {
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['birthday'] = $birthday;
                $_SESSION['user']['avatar'] = $avatar;

                $this->showProfile(['changeProfile_success' => true]);
            } else {
                $msg = $result['message'] ?? 'Lỗi không xác định';
                $this->loadView('updateProfile.php', ['message' => $msg]);
            }
        }
    }

    private function callAPIupdateProfile($username, $name, $birthday, $avatar, $user_id) {
        $data = json_encode(['username' => $username, 'name' => $name, 'birthday' => $birthday, 'avatar' => $avatar, 'user_id' => $user_id]);
        $ch = curl_init($this->apiUrlUpdateProfile);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
?>