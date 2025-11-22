<?php
require_once 'models/Users.php';
require_once 'models/Logs.php';

class UserController {
    private $userModel;
    private $LogsModel;

    public function __construct($db, $momgoDB) {
        $this->userModel = new Users($db);
        $this->LogsModel = new Logs($momgoDB);
    }

    // API đăng nhập trả về JSON
    public function login() {
        header('Content-Type: application/json; charset=UTF-8');
        
        // Chỉ nhận POST
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $data = json_decode(file_get_contents("php://input"), true);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';

            if (!empty($username) && !empty($password)) {
                $user = $this->userModel->authenticate($username, $password);

                if ($user) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "Đăng nhập thành công!",
                        "data" => $user
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Sai tài khoản hay mật khẩu!"
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Bạn đã nhập thiếu thông tin!"
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Lỗi đường truyền!"
            ]);
        }
    }

    public function register() {
        //Trả về dữ liệu JSON
        header('Content-Type: application/json; charset=UTF-8');

        // Chỉ nhận POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }
        // Nhận dữ liệu JSON
        $data = json_decode(file_get_contents("php://input"), true);
        $username = trim($data['username'] ?? '');
        $displayName = trim($data['name'] ?? '');
        $password = $data['password'] ?? '';
        $passwordAgain = $data['passwordagain'] ?? '';

        // Kiểm tra rỗng
        if (!$username || !$displayName || !$password || !$passwordAgain) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập đầy đủ thông tin']);
            return;
        }
        // Kiểm tra trùng mật khẩu
        if ($password !== $passwordAgain) {
            echo json_encode(['status' => 'error', 'message' => 'Mật khẩu nhập lại không khớp']);
            return;
        }

        // Kiểm tra username đã tồn tại chưa
        if ($this->userModel->exists($username)) {
            echo json_encode(['status' => 'error', 'message' => 'Tên tài khoản đã tồn tại']);
            return;
        }

        // Hash mật khẩu
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Lưu DB
        $id = $this->userModel->create($username, $hashedPassword, $displayName);
        $action = "Bạn đã tạo tài khoản Book Project!";
        $keyword = "account";
        $logs = $this->LogsModel->AddALog($id, $action, $keyword);
        if (isset($id) && $logs) {
            echo json_encode(['status' => 'success', 'message' => 'Đăng ký thành công']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống, thử lại sau']);
        }
    }
    
    public function checkUser() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $username = trim($data['username'] ?? '');

        if (!$username) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập tên tài khoản']);
            return;
        }

        if ($this->userModel->exists($username)) {
            echo json_encode(['status' => 'success', 'message' => 'Tài khoản tồn tại']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tài khoản không tồn tại']);
        }
    }
    public function resetPassword() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $passwordAgain = $data['passwordagain'] ?? '';
        $user_id = $data['user_id'] ?? 0;
        if(!$user_id || $user_id == 0){
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập lại và thử lại']);
            return;
        }
        if (!$username || !$password || !$passwordAgain) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập đầy đủ thông tin']);
            return;
        }

        if ($password !== $passwordAgain) {
            echo json_encode(['status' => 'error', 'message' => 'Mật khẩu nhập lại không khớp']);
            return;
        }

        if (!$this->userModel->exists($username)) {
            echo json_encode(['status' => 'error', 'message' => 'Tài khoản không tồn tại']);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $success = $this->userModel->updatePassword($username, $hashedPassword);
        $action = "Bạn đã đổi mật khẩu tài khoản Book Project!";
        $keyword = "account";
        $logs = $this->LogsModel->AddALog($user_id, $action, $keyword);
        if ($success && $logs) {
            echo json_encode(['status' => 'success', 'message' => 'Đổi mật khẩu thành công']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không thể cập nhật mật khẩu']);
        }
    }

    public function updateProfile() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $username = trim($data['username'] ?? '');
        $name = $data['name'] ?? '';
        $birthday = $data['birthday'] ?? '';
        $avatar = $data['avatar'] ?? '';
        $user_id = $data['user_id'] ?? 0;
        if(!$user_id || $user_id == 0){
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập lại và thử lại']);
            return;
        }
        $success = $this->userModel->updateProfile($username, $name, $birthday, $avatar);
        $action = "Bạn đã đổi thông tin cá nhân tài khoản Book Project!";
        $keyword = "account";
        $logs = $this->LogsModel->AddALog($user_id, $action, $keyword);
        if ($success && $logs) {
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật hồ sơ thành công']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không thể cập nhật hồ sơ']);
        }
    }
    public function getUserBalance() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $data['user_id'] ?? '';
        $balance = $this->userModel->checkUserBalance($user_id);

        if ($balance) {
            echo json_encode(['status' => 'success', 'balance' => $balance ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi lấy dữ liệu']);
        }
    }
    public function getLogs() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "GET") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }
        $user_id = $_GET['user_id'];

        if (!$user_id) {
            echo json_encode(["error" => "Lỗi đăng nhập"]);
            return;
        }
        $logs = $this->LogsModel->getLogsByUserId($user_id, 5);

        if ($logs || $logs['status'] === 'success') {
            echo json_encode($logs['result']);
        } else {  echo json_encode($logs);}
    }
    public function deleteNotification() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $_id = $data['id'] ?? '';

        if (!$_id) {
            echo json_encode(['status' => 'error', 'message' => "Lỗi thông báo"]);
            return;
        }
        $logs = $this->LogsModel->deleteLogById($_id);

        if ($logs && $logs['status'] === "success") {
            echo json_encode($logs);
        } else {  echo json_encode($logs);}
    }

    public function viewAllLogs() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $data['user_id'] ?? '';

        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => "Đăng nhập và thử lại"]);
            return;
        }
        $logs = $this->LogsModel->getLogsByUserId($user_id);

        if ($logs || $logs['status'] === 'success') {
            echo json_encode(['status' => 'success', 'logs' => $logs['result']]);
        } else {  echo json_encode(['status' => 'error', 'error' => 'lỗi lấy logs']);}
    }

}
?>