<?php
require_once 'models/Transactions.php';
require_once 'models/Users.php';
require_once 'models/ZaloPay.php';
require_once 'models/Logs.php';
class TransactionController {
    private $transactionModel;
    private $zaloPayModel;
    private $userModel;
    private $logModel;

    public function __construct($db, $mongoDB) {
        $this->transactionModel = new Transactions($db);
        $this->userModel = new Users($db);
        $this->zaloPayModel = new ZaloPayModel();
        $this->logModel = new Logs($mongoDB);
    }

    public function getTransaction()
    {
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
        $trans = $this->transactionModel->getAllTransaction($user_id);
        if ($trans) {
            echo json_encode(['status' => 'success', 'message' => 'Thành công', "trans" => $trans]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'không có sách nào!']);
        }

    }
    public function createTransaction()
    {
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

        $book_id = $data['book_id'];
        if (!$book_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có Mã sách']);
            return;
        }
        $allTrans = $this->transactionModel->getAllTransaction($user_id);
        foreach ($allTrans as $trans) {
            if ($trans['status'] === 'uu') {
                // User vẫn còn transaction pending
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Bạn vẫn còn giao dịch đang chờ xử lý'
                ]);
                return;
            }
        }
        $transaction = $this->transactionModel->createTransaction($user_id,$book_id);
        if ($transaction) {
            echo json_encode(['status' => 'success', 'message' => 'Thành công', 
            "transaction_id" => $transaction['id'], "price" => $transaction['price']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi tạo đơn hàng!']);
        }

    }
    public function createZaloPayOrder()
    {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            echo json_encode(['status'=>'error','message'=>'Dữ liệu không hợp lệ']);
            return;
        }
        $transaction_id = $data['transaction_id'];
        if (!$transaction_id) {
            echo json_encode(['status' => 'error', 'message' => 'Không có mã giao dịch']);
            return;
        }
        // Lấy giao dịch để có amount + user_id
        $transaction = $this->transactionModel->getTransaction($transaction_id);

        if (!$transaction) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction không tồn tại']);
            return;
        }
        $balance = $this->userModel->checkUserBalance($transaction['user_id']);
        $order = $this->zaloPayModel->createOrder(
            $transaction_id,
            $transaction['amount'],
            $transaction['user_id'],
            $balance
        );
        if ($order) {
            echo json_encode([
                'status' => 'success',
                'checkout_url' => $order['checkout_url']
            ]);
        } else {
            echo json_encode([
            'status' => 'error',
            'message' => 'Lỗi thanh toán ZaloPay!'
        ]);
        }

    }
    public function getNewTransaction() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status'=>'error','message'=>'Phương thức không hợp lệ']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $transaction_id = intval($data['transaction_id'] ?? 0);
        $status = $data['status'] ?? '';

        if ($transaction_id <= 0 || !isset($data['status'])) {
            echo json_encode(['status'=>'error','message'=>'Dữ liệu không hợp lệ']);
            return;
        }
        $status_db = ($status === "1" || $status === 1) ? "success" : "failed";

        $this->transactionModel->updateStatus($transaction_id, $status_db);

        // Lấy transaction từ model
        $transaction = $this->transactionModel->getTransaction($transaction_id);
        if ($status_db === 'success') {
            $amount = floatval($transaction['amount']);
            $user_id = intval($transaction['user_id']);
            $book_name = intval($transaction['book_name']);

            $this->transactionModel->deductBalance($user_id, $amount);
            $this->logModel->AddALog($transaction['user_id'], "Bạn vừa thanh toán thành công sách " . $book_name, "purchase");
        }
        if (!$transaction) {
            echo json_encode(['status'=>'error','message'=>'Không tìm thấy transaction']);
            return;
        }
        echo json_encode(['status'=>'success','transaction'=>$transaction]);
    }
    public function deleteTransaction() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status'=>'error','message'=>'Phương thức không hợp lệ']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $transaction_id = intval($data['transaction_id'] ?? 0);

        if ($transaction_id <= 0) {
            echo json_encode(['status'=>'error','message'=>'Dữ liệu không hợp lệ']);
            return;
        }

        $deleted = $this->transactionModel->deleteTrans($transaction_id);
        if (!$deleted) {
            echo json_encode(['status'=>'error','message'=>'Không tìm thấy transaction hoặc lỗi khi hủy giao dịch']);
            return;
        }
        echo json_encode(['status'=>'success']);
    }
    public function cancelTransaction() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(['status'=>'error','message'=>'Phương thức không hợp lệ']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $transaction_id = intval($data['transaction_id'] ?? 0);

        if ($transaction_id <= 0) {
            echo json_encode(['status'=>'error','message'=>'Dữ liệu không hợp lệ']);
            return;
        }

        $canceled = $this->transactionModel->updateStatus($transaction_id, 'failed');
        if (!$canceled) {
            echo json_encode(['status'=>'error','message'=>'Không tìm thấy transaction hoặc lỗi khi hủy giao dịch']);
            return;
        }
        echo json_encode(['status'=>'success']);
    }
}
?>