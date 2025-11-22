<?php

class BookController extends Controller {

    private $apiGetBook;
    private $apiGetComments;
    private $apiGetManageBooks;
    private $apiSaveBook;
    private $apiUnSaveBook;

    public function __construct() {
        // Đường dẫn API backend
        $this->apiGetBook = "http://localhost/BookProject/BookProject/Backend/index.php?action=getBook";
        $this->apiGetComments = "http://localhost/BookProject/BookProject/Backend/index.php?action=getBookComments";
        $this->apiGetManageBooks = "http://localhost/BookProject/BookProject/Backend/index.php?action=manageBooks";
        $this->apiSaveBook = "http://localhost/BookProject/BookProject/Backend/index.php?action=saveBook";
        $this->apiUnSaveBook = "http://localhost/BookProject/BookProject/Backend/index.php?action=unSaveBook";
    }

    public function showBookProfile() {
        // Lấy book_id từ URL GET
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
        $user_id = $_SESSION['user']['user_id'];
        $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $per_page = 5;
        if ($book_id <= 0) {
            $this->loadView('bookProfile.php', ['message' => 'ID sách không hợp lệ.']);
            return;
        }

        // Lấy thông tin sách từ backend API (GET)
        $book = $this->callAPIGetBookInfo($book_id, $user_id);
        
        // Lấy bình luận với phân trang
        $commentsData = $this->callAPIGetBookComments($book_id, $current_page, $per_page);

        // Kiểm tra lỗi
        if ($book === null || $commentsData === null
            || !isset($book['status']) || !isset($commentsData['status'])) {
            $message = 'Không thể kết nối đến server hoặc dữ liệu không hợp lệ.';
            $this->loadView('bookProfile.php', ['message' => $message]);
            return;
        }

        if ($book['status'] === 'success' && $commentsData['status'] === 'success') {
            $total_comments = intval($commentsData['total_comments'] ?? 0);
            $total_pages = ceil($total_comments / $per_page);

            $this->loadView('bookProfile.php', [
                'book' => $book['Info'] ?? [],
                'categories' => $book['Categories'] ?? [],
                'comments' => $commentsData['Comments'] ?? [],
                'current_page' => $current_page,
                'per_page' => $per_page,
                'total_pages' => $total_pages,
                'purchased' => $book['purchaseResult'] ?? 0,
                'saved' => $book['saved'] ?? 0
            ]);
        } else {
            $message = $book['message'] ?? $commentsData['message'] ?? 'Không tìm thấy sách hoặc bình luận.';
            $this->loadView('bookProfile.php', ['message' => $message]);
        }
    }

    // Gọi backend API getBook bằng GET
    private function callAPIGetBookInfo($book_id, $user_id) {
        $url = $this->apiGetBook . "&book_id=" . intval($book_id) . "&user_id=" . intval($user_id);
        $response = @file_get_contents($url);
        if ($response === false) return null;
        return json_decode($response, true);
    }

    // Gọi backend API getBookComments bằng GET, có phân trang
    private function callAPIGetBookComments($book_id, $page = 1, $per_page = 20) {
        $url = $this->apiGetComments
            . "&book_id=" . intval($book_id)
            . "&page=" . intval($page)
            . "&per_page=" . intval($per_page);

        $response = @file_get_contents($url);
        if ($response === false) return null;
        return json_decode($response, true);
    }

    public function showBookManagement() {
        $user_id = $_SESSION['user']['user_id'];
        $response = $this->callAPIManageBooks($user_id);
        $result = json_decode($response, true);
        if ($response === false || $result === null) {
            $this->loadView('manageBook.php', [
            'message' => 'Không nhận được phản hồi từ server hoặc dữ liệu không hợp lệ.',
            ]);
            return;
        }
        if (isset($result['status']) && $result['status'] === 'success') {         
           $this->loadView('manageBook.php', 
            ['favoritedBooks' => $result['favoritedBooks'] ?? [],
                    'purchasedBooks' => $result['purchasedBooks'] ?? [],
                    'readingBooks' => $result['readingBooks'] ?? [],]);
        } else {
            $this->loadView('manageBook.php', ['message' => $result['message']]);
        }
    }
    private function callAPIManageBooks($userid) {
        $data = json_encode(['user_id' => $userid]);
        $ch = curl_init($this->apiGetManageBooks);
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

    public function saveBook() {
        // Lấy book_id từ URL GET
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
        $user_id = $_SESSION['user']['user_id'];
        if ($book_id <= 0) {
            $this->loadView('bookProfile.php', ['message' => 'ID sách không hợp lệ.']);
            return;
        }
        // Lấy thông tin sách từ backend API (GET)
        $result = $this->callAPISaveBook($book_id, $user_id);
        if (isset($result['status']) && $result['status'] === 'success') {         
           header("Location: index.php?action=bookProfile&book_id=" . $book_id);
            exit;
        } else {
            $this->loadView('manageBook.php', ['message' => $result['message']]);
        }
    }
    // Gọi backend API apiSaveBook bằng GET
    private function callAPISaveBook($book_id,$user_id) {
        $url = $this->apiSaveBook
            . "&book_id=" . intval($book_id)
            . "&user_id=" . intval($user_id);

        $response = @file_get_contents($url);
        if ($response === false) return null;
        return json_decode($response, true);
    }

    public function unSaveBook() {
        // Lấy book_id từ URL GET
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
        $user_id = $_SESSION['user']['user_id'];
        if ($book_id <= 0) {
            $this->loadView('bookProfile.php', ['message' => 'ID sách không hợp lệ.']);
            return;
        }
        // Lấy thông tin sách từ backend API (GET)
        $result = $this->callAPIUnSaveBook($book_id, $user_id);
        if (isset($result['status']) && $result['status'] === 'success') {         
           header("Location: index.php?action=bookProfile&book_id=" . $book_id);
            exit;
        } else {
            $this->loadView('manageBook.php', ['message' => $result['message']]);
        }
    }
    // Gọi backend API apiSaveBook bằng GET
    private function callAPIUnSaveBook($book_id,$user_id) {
        $url = $this->apiUnSaveBook
            . "&book_id=" . intval($book_id)
            . "&user_id=" . intval($user_id);

        $response = @file_get_contents($url);
        if ($response === false) return null;
        return json_decode($response, true);
    }
}

?>
