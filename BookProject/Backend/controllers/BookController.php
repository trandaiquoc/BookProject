<?php
require_once 'models/Books.php';
require_once ('models/ReadingHistory.php');
require_once ('models/Logs.php');
class BookController {
    private $bookModel;
    private $readingModel;
    private $logModel;

    public function __construct($db, $mongodb) {
        $this->bookModel = new Books($db);
        $this->readingModel = new Readinghistory($mongodb);
        $this->logModel = new Logs($mongodb);
    }
    public function getUploadedBooks() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $data['user_id'];

        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã tài khoản']);
            return;
        }

        $uploadedBooks = $this->bookModel->checkBookUploaded($user_id);
        if ($uploadedBooks) {
            echo json_encode(['status' => 'success', 'message' => 'Thành công', "uploadedBooks" => $uploadedBooks]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'không có sách nào!']);
        }
    }
    public function getFavoritedBooks() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $data['user_id'];

        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã tài khoản']);
            return;
        }

        $favoritedBooks = $this->bookModel->checkBookFavorite($user_id);
        if ($favoritedBooks) {
            echo json_encode(['status' => 'success', 'message' => 'Thành công', "favoritedBooks" => $favoritedBooks]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'không có sách nào!']);
        }
    }

    public function getBookInfo() {
        header('Content-Type: application/json; charset=UTF-8');


        // Lấy book_id
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

        if ($book_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Không có book_id hoặc ID không hợp lệ.']);
            return;
        }
        // Lấy user_id
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

        if ($user_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Không có user_id hoặc ID không hợp lệ.']);
            return;
        }

        $info = $this->bookModel->getBookDetails($book_id);
        $purchase = $this->bookModel->checkPurchasedBook($book_id,$user_id);
        $saved = $this->bookModel->checkBookSaved($book_id,$user_id);
        if ($info) {
            echo json_encode(['status' => 'success',
                                    'message' => 'Thành công',
                                    "Info" => $info['info'],
                                    "Categories" => $info['categories'],
                                    "purchaseResult" => $purchase,
                                    "saved" => $saved]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi thông tin!']);
        }
    }
    public function getBookComments() {
        header('Content-Type: application/json; charset=UTF-8');


        // Lấy book_id, page, per_page từ GET
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
        $page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;


        if ($book_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Không có book_id hoặc ID không hợp lệ.']);
            return;
        }

         // --- Lấy bình luận phân trang ---
        $offset = ($page - 1) * $per_page;
        $comments = $this->bookModel->getBookComments($book_id, $per_page, $offset);
        $total_comments = $this->bookModel->getTotalComments($book_id);

        // --- Trả về JSON ---
        echo json_encode([
            'status' => 'success',
            'Comments' => $comments,
            'total_comments' => $total_comments
        ]);
    }
    public function submitComment() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $data['user_id'];
        $book_id = $data['book_id'];
        $rating = $data['rating'];
        $comment = $data['comment'];

        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã tài khoản']);
            return;
        }
        if (!$book_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã sách']);
            return;
        }

        // Lưu comment và rating
        $result1 = $this->bookModel->comment($book_id, $user_id, $comment);
        $result2 = $this->bookModel->rating($book_id, $user_id, intval($rating));

        if ($result1 && $result2) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Thành công',
                'rating' => intval($rating),
                'comment' => $comment
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi nhập bình luận!']);
        }
    }
    public function saveReadingState() {
        header('Content-Type: application/json; charset=UTF-8');

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã tài khoản']);
            return;
        }
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
        if (!$book_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã sách']);
            return;
        }
        $last_page = isset($_GET['last_page']) ? intval($_GET['last_page']) : 0;
        $total_page = isset($_GET['total_page']) ? intval($_GET['total_page']) : 0;
        $result1 = $this->bookModel->read($book_id);
        $result2 = $this->readingModel->uplog_Read($user_id, $book_id, $last_page, $total_page);
        $bookName = $this->bookModel->getBookNameById($book_id);
        $result3 = $this->logModel->AddALog($user_id, "Bạn đang đọc sách ". $bookName . " tới trang " . $last_page, "reading");
        if ($result1 && $result2 && $result3) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Thành công',
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi nhập dữ liệu!']);
        }
    }

    public function getAllManageBooks() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $data['user_id'];

        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã tài khoản']);
            return;
        }

        try {
            // Sách đã lưu
            $favoritedBooksRaw = $this->bookModel->checkBookFavorite($user_id);
            // Sách đã mua
            $purchasedBooksRaw = $this->bookModel->checkAllPurchasedBook($user_id);
            // Sách đang đọc (có progress)
            $readingBooksRaw  = $this->readingModel->checkBooksProcess($user_id);

            // Hàm chuyển đổi raw book -> object bookDetails
            $formatBooks = function($books) {
                if (!$books) return [];
                $result = [];
                foreach ($books as $b) {
                    $bookDetail = $this->bookModel->getBookDetails($b['book_id']);
                    if ($bookDetail) {
                        $result[] = ['book' => $bookDetail];
                    }
                }
                return $result;
            };

            $favoritedBooks = $formatBooks($favoritedBooksRaw);
            $purchasedBooks = $formatBooks($purchasedBooksRaw);

            // readingBooks: thêm progress từ MongoDB
            $readingBooks = [];
            if ($readingBooksRaw) {
                foreach ($readingBooksRaw as $r) {
                    $bookDetail = $this->bookModel->getBookDetails($r->book_id);
                    if ($bookDetail) {
                        $readingBooks[] = [
                            'book' => $bookDetail,
                            'last_page' => $r->last_page,
                            'progress' => $r->progress,
                            'timestamp' => $r->timestamp
                        ];
                    }
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Thành công',
                'favoritedBooks' => $favoritedBooks,
                'purchasedBooks' => $purchasedBooks,
                'readingBooks' => $readingBooks
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function favoriteBook() {
        header('Content-Type: application/json; charset=UTF-8');

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã tài khoản']);
            return;
        }
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
        if (!$book_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã sách']);
            return;
        }
        $result = $this->bookModel->save($book_id, $user_id);
        $bookName = $this->bookModel->getBookNameById($book_id);
        $result2 = $this->logModel->AddALog($user_id, "Bạn đã lưu sách ". $bookName, "reading");
        if ($result && $result['status'] === 'success' && $result2) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Thành công',
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
        }
    }
    public function unFavoriteBook() {
        header('Content-Type: application/json; charset=UTF-8');

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã tài khoản']);
            return;
        }
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
        if (!$book_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã sách']);
            return;
        }
        $result = $this->bookModel->unSave($book_id, $user_id);
        $bookName = $this->bookModel->getBookNameById($book_id);
        $result2 = $this->logModel->AddALog($user_id, "Bạn đã hủy lưu sách ". $bookName, "reading");
        if ($result && $result2) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Thành công',
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi nhập dữ liệu!']);
        }
    }

    public function search() {
        header('Content-Type: application/json; charset=UTF-8');
        if (!isset($_GET['q'])) {
            echo json_encode(['status' => 'error', 'message' => 'Không có dữ liệu tìm kiếm']);
            return;
        }
        $q = trim($_GET['q']);
        $results = $this->bookModel->searchBooks($q, 5);
        echo json_encode($results);
        
    }
}
?>