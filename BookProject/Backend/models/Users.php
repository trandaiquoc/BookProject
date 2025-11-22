<?php
class Users {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    // Xác thực đăng nhập
    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $stored_password = $row['password'];
            // So khớp hash
            if (password_verify($password, $stored_password)) {
                return $row;
            }
        }
        return null;
    }
    

    public function exists($username) {
        $stmt = $this->db->prepare("SELECT user_id FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    public function create($username, $password, $displayName) {
        $stmt = $this->db->prepare("INSERT INTO user (username, password, name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $displayName);
        
        if ($stmt->execute()) {
            return $stmt->insert_id; // ✅ trả về user_id
        }

        return null;
    }

    public function updatePassword($username, $hashedPassword) {
        $stmt = $this->db->prepare("UPDATE user SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashedPassword, $username);
        return $stmt->execute();
    }

    public function updateProfile($username, $name, $birthday, $avatar) {
        $stmt = $this->db->prepare("UPDATE user SET name = ?,  birthday = ?,  avatar = ?  WHERE username = ?");
        $stmt->bind_param("ssss", $name, $birthday, $avatar, $username);
        return $stmt->execute();
    }
    public function checkUserBalance($user_id) {
        $stmt = $this->db->prepare("SELECT balance FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc(); // Lấy row đầu tiên
        $stmt->close();

        if ($row) {
            return floatval($row['balance']); // Trả về balance dạng float
        } else {
            return 0; // hoặc null nếu user không tồn tại
        }
    }
}
?>