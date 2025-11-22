<?php
class Books {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function checkBookUploaded($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM Book WHERE upload_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
        return $books;
    }
    
    public function checkBookFavorite($user_id) {
        $stmt = $this->db->prepare("SELECT * 
                                    FROM Book b 
                                    JOIN FavoriteBooks fb ON b.book_id = fb.book_id
                                    WHERE fb.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
        return $books;
    }

    // Lấy chi tiết sách + thể loại
    public function getBookDetails($book_id) {
        $book = [];
        $categories = [];

        // 1. Lấy thông tin sách
        $stmt = $this->db->prepare("SELECT b.*, avg(r.score) as rating
                                    FROM book b
                                    LEFT JOIN rating r ON b.book_id = r.book_id
                                    WHERE b.book_id = ?
                                    GROUP BY b.book_id");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        if (!$book) return null;

        // 2. Lấy danh sách thể loại
        $categories = $this->checkBookCategories($book_id);

        // Trả về mảng info + categories
        return [
            'info' => $book,
            'categories' => $categories
        ];
    }
    public function checkBookCategories($book_id) {
        $stmt2 = $this->db->prepare("
            SELECT c.name, c.category_id
            FROM bookCategory bc
            JOIN Category c ON bc.category_id = c.category_id
            WHERE bc.book_id = ?
        ");
        $stmt2->bind_param("i", $book_id);
        $stmt2->execute();
        $categories = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        return $categories;
    }
    //Kiểm tra có yêu thích sách hay chưa
    public function checkBookSaved($book_id, $user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt
                                    FROM `favoritebooks`
                                    WHERE book_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $book_id, $user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $saved = $row['cnt'] > 0 ? 1 : 0;

        $result->free();   // ✔ GIẢI PHÓNG KẾT QUẢ
        $stmt->close();    // ✔ ĐÓNG STATEMENT

        return $saved;
    }
    // Kiểm tra có mua sách hay chưa
    public function checkPurchasedBook($book_id, $user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt
                                    FROM `transaction`
                                    WHERE book_id = ? AND user_id = ? AND status = 'success'");
        $stmt->bind_param("ii", $book_id, $user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $purchased = $row['cnt'] > 0 ? 1 : 0;

        $result->free();   // ✔ GIẢI PHÓNG KẾT QUẢ
        $stmt->close();    // ✔ ĐÓNG STATEMENT

        return $purchased;
    }
    public function getBookComments($book_id, $limit = 20, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT c.comment_id, c.user_id, c.book_id, c.content, c.created_at, u.username, u.avatar, r.score
            FROM Comment c
            JOIN User u ON c.user_id = u.user_id
            JOIN Rating r ON r.user_id = u.user_id
            WHERE c.book_id = ? AND r.book_id = ?
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iiii", $book_id, $book_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        return $comments;
    }

    // Tổng số bình luận
    public function getTotalComments($book_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Comment WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return intval($row['total'] ?? 0);
    }

    // Bình luận sách
    public function comment($book_id, $user_id, $content) {
        $created_at = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("INSERT INTO comment (user_id, book_id, content, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $book_id, $content, $created_at);
        return $stmt->execute();
    }
    // Đánh giá sách
    public function rating($book_id, $user_id, $score) {
        $count = 0;
        // Kiểm tra xem user đã đánh giá chưa
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rating WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            // Nếu đã đánh giá, update
            $stmt = $this->db->prepare("UPDATE rating SET score = ? WHERE user_id = ? AND book_id = ?");
            $stmt->bind_param("iii", $score, $user_id, $book_id);
            return $stmt->execute();
        } else {
            // Nếu chưa đánh giá, insert
            $stmt = $this->db->prepare("INSERT INTO rating (user_id, book_id, score) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $book_id, $score);
            return $stmt->execute();
        }
    }
    // Đọc sách
    public function read($book_id) {
        $stmt = $this->db->prepare("UPDATE book SET visits = visits + 1 WHERE book_id = ?");
        $stmt->bind_param("i",$book_id);
        return $stmt->execute();
    }

    // Kiểm tra tất cả sách đã mua
    public function checkAllPurchasedBook($user_id) {
        $stmt = $this->db->prepare("SELECT b.*
                                    FROM book b
                                    JOIN `transaction` t ON t.book_id = b.book_id
                                    WHERE user_id = ? AND status = 'success'");
        $stmt->bind_param("i" , $user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $purchased = $result->fetch_all(MYSQLI_ASSOC);
        if (!$purchased) return null;
            return $purchased ?: [];
    }

    // Yêu thích sách(lưu sách)
    public function save($book_id, $user_id) {

        // 1. Kiểm tra xem sách đã được lưu chưa
        $result = $this->checkBookSaved($book_id, $user_id);

        // Nếu đã tồn tại → không được INSERT → trả về “đã lưu”
        if ($result) {
            return [
                "status" => "error",
                "message" => "Book already saved"
            ];
        }

        // 2. INSERT vì chưa có
        $created_at = date("Y-m-d H:i:s");

        $stmt = $this->db->prepare(
            "INSERT INTO favoritebooks (user_id, book_id, created_at) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iis", $user_id, $book_id, $created_at);
        $stmt->execute();

        return [
            "status" => "success",
            "message" => "Book saved"
        ];
    }
    // Hủy yêu thích sách(lưu sách)
    public function unSave($book_id, $user_id) {
        $stmt = $this->db->prepare("DELETE FROM favoritebooks WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $user_id, $book_id);
        return $stmt->execute();
    }
    public function getBookNameById($book_id) {
        $query = "SELECT name FROM book WHERE book_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row ? $row['name'] : null; // Trả về null nếu không có
    }
    public function searchBooks($keyword, $limit) {
        $sql = "SELECT * FROM book WHERE name LIKE ? LIMIT ?";
        $stmt = $this->db->prepare($sql);

        $like = "%{$keyword}%";
        $stmt->bind_param("si", $like, $limit);

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}