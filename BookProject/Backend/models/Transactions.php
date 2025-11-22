<?php
class Transactions {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllTransaction($user_id) {
        $trans = [];

        // 1. Lấy thông tin sách
        $stmt = $this->db->prepare("SELECT u.name as username , b.name as bookname, t.*
                                    FROM `transaction` t
                                    JOIN book b ON b.book_id = t.book_id
                                    JOIN user u ON u.user_id = t.user_id
                                    WHERE t.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $trans = $result->fetch_all(MYSQLI_ASSOC);
        if (!$trans) return null;
        return $trans;
    }
    public function getTransaction($transaction_id) {
        $stmt = $this->db->prepare("SELECT t.*, b.name AS book_name, u.name AS user_name
                                    FROM `transaction` t
                                    JOIN Book b ON t.book_id = b.book_id
                                    JOIN User u ON t.user_id = u.user_id
                                    WHERE t.transaction_id=?");
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($result) {
        $result['amount'] = floatval($result['amount']);
    }
        return $result;
    }
    public function createTransaction($user_id, $book_id) {
        // 1. Kiểm tra xem giá sách
        $price = $this->checkPrice($book_id);
        $stmt = $this->db->prepare("INSERT INTO `transaction` (user_id, book_id, amount, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iid", $user_id, $book_id, $price);
        $stmt->execute();
        $transaction_id = $stmt->insert_id;
        $stmt->close();
        // Trả về cả id + giá
        return [
            'id' => $transaction_id,
            'price' => $price
        ];
    }
    public function checkPrice($book_id) {
        $stmt = $this->db->prepare("SELECT price FROM book WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $price = $row['price'];
        $result->free(); 
        $stmt->close();
        return $price;
    }

    public function updateStatus($transaction_id, $status) {
        $stmt = $this->db->prepare("UPDATE `transaction` SET status=? WHERE transaction_id=?");
        $stmt->bind_param("si", $status, $transaction_id);
        $stmt->execute();

        $success = $stmt->affected_rows > 0; // true nếu có row được update
        $stmt->close();

        return $success;
    }
    public function deleteTrans($transaction_id) {
        $stmt = $this->db->prepare("DELETE FROM `transaction` WHERE transaction_id=?");
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();
        return $deleted > 0; // true nếu xóa thành công
    }

    public function deductBalance($user_id, $amount) {
    $this->db->begin_transaction();

    $stmt = $this->db->prepare("
        UPDATE user 
        SET balance = balance - ? 
        WHERE user_id = ? AND balance >= ?
    ");
    $stmt->bind_param("dii", $amount, $user_id, $amount);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        $this->db->commit();
        return true;
    } else {
        $this->db->rollback();
        return false;
    }
}

    
}
?>