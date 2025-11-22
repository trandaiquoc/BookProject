<?php

class TransactionController extends Controller {

    private $apiGetTrans;
    private $apiCreateTrans;
    private $apiCreateZaloPayOrders;
    private $apiGetNewTrans;
    private $apiDeleteTrans;
    private $apiGetUserBalance;

    public function __construct() {
        // Đường dẫn API backend
        $this->apiGetTrans = "http://localhost/BookProject/BookProject/Backend/index.php?action=getTransaction";
        $this->apiCreateTrans = "http://localhost/BookProject/BookProject/Backend/index.php?action=createTransaction";
        $this->apiCreateZaloPayOrders = "http://localhost/BookProject/BookProject/Backend/index.php?action=createZaloPayOrder";
        $this->apiGetNewTrans = "http://localhost/BookProject/BookProject/Backend/index.php?action=getNewTransaction";
        $this->apiDeleteTrans = "http://localhost/BookProject/BookProject/Backend/index.php?action=deleteTransaction";
        $this->apiGetUserBalance = "http://localhost/BookProject/BookProject/Backend/index.php?action=getUserBalance";
    }

    public function showTransactionHistory()
    {
        $user_id = $_SESSION['user']['user_id'];
        $response = $this->callAPIgetTrans($user_id);
        $result = json_decode($response, true);
        if ($response === false || $result === null) {
            $this->loadView('transaction.php', [
            'message' => 'Không nhận được phản hồi từ server hoặc dữ liệu không hợp lệ.',
            ]);
            return;
        }
        if (isset($result['status']) && $result['status'] === 'success') {         
           $this->loadView('transaction.php', 
            ['transactioninfo' => $result['trans'] ?? []]);
        } else {
            $this->loadView('transaction.php', ['message' => $result['message']]);
        }
    }
    private function callAPIgetTrans($userid) {
        $data = json_encode(['user_id' => $userid]);
        $ch = curl_init($this->apiGetTrans);
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

    public function showPayment()
    {
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
        $book_name = isset($_GET['book_name']) ? $_GET['book_name'] : '';
        $user_id = $_SESSION['user']['user_id'];
        $user_name = $_SESSION['user']['name'];
        $user_balance = $_SESSION['user']['balance'];
        if ($book_id <= 0) {
            $this->loadView('bookProfile.php', ['message' => 'ID sách không hợp lệ.']);
            return;
        }
        // Tạo transaction pending
        $response = $this->callAPIcreateTrans($user_id, $book_id);
        $result = json_decode($response, true);
        if ($response === false || $result === null) {
            header("Location: index.php?action=bookProfile&book_id=$book_id&error=" . urlencode('Không nhận được phản hồi từ server hoặc dữ liệu không hợp lệ.'));
            exit;
        }
        if ($user_balance < $result['price']) {
            $response2  = $this->callAPIDeleteTrans($result['transaction_id']);
            $result2 = json_decode($response2, true);
            if (isset($result2['status']) && $result2['status'] === 'success') {
                $_SESSION['paymentnotif'] = 'Bạn không đủ số dư để mua sách này';
                header("Location: index.php?action=bookProfile&book_id=$book_id");
                exit;
            } else {
                $message = $result2['message'];
                header("Location: index.php?action=bookProfile&book_id=$book_id&error=$message");
                exit;
            }
        }
        if (isset($result['status']) && $result['status'] === 'success') {         
          $this->loadView('payment.php',
                    ['book_id' => $book_id,
                            'book_name' => $book_name,
                            'price' => $result['price'],
                            'user_id' => $user_id,
                            'user_name' => $user_name,
                            'transaction_id' => $result['transaction_id']]);
        } else {
            $_SESSION['paymentnotif'] = $result['message'];
            header("Location: index.php?action=bookProfile&book_id=$book_id");
                exit;
        }
    }
    private function callAPIcreateTrans($userid, $book_id) {
        $data = json_encode(['user_id' => $userid, 'book_id' => $book_id]);
        $ch = curl_init($this->apiCreateTrans);
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
    private function callAPIDeleteTrans($transaction_id) {
        $data = json_encode(['transaction_id' => $transaction_id]);
        $ch = curl_init($this->apiDeleteTrans);
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
    public function processZaloPay()
    {
        $transaction_id = isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : 0;
        if ($transaction_id <= 0) {
            $this->loadView('error.php', ['message' => 'ID hóa đơn không hợp lệ.']);
            return;
        }
        // Tạo transaction pending
        $response = $this->createZaloPayOrder($transaction_id);
        $result = json_decode($response, true);

        if (isset($result['status']) && $result['status'] === 'success') {
            header("Location: ".$result['checkout_url']);
            exit;
        } else {
            $this->loadView('error.php', ['message' => $result['message']]);
        }
    }
    private function createZaloPayOrder($transaction_id) {
        $data = json_encode(['transaction_id' => $transaction_id]);
        $ch = curl_init($this->apiCreateZaloPayOrders);
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

    public function paymentResult() {
        $transaction_id = intval($_GET['transaction_id'] ?? 0);
        $status = $_GET['status'] ?? '';
        if ($transaction_id <= 0) {
            $this->loadView('error.php', ['message'=>'ID giao dịch không hợp lệ']);
            return;
        }
        // Step 1: Gọi backend update trạng thái transaction (giống callback)
        $response = $this->callAPIUpdateStatus($transaction_id, $status);
        $transactionData = json_decode($response, true);

        if ($transactionData['status'] !== 'success') {
            $this->loadView('error.php', ['message' => 'Cập nhật giao dịch thất bại']);
            return;
        }
        $transaction = $transactionData['transaction'] ?? [];
        // Step 2: Refresh thông tin balance user
        $userResponse = $this->callAPIGetUserBalance($_SESSION['user']['user_id']);
        $userData = json_decode($userResponse, true);

        if ($userData['status'] === 'success') {
            $_SESSION['user']['balance'] = $userData['balance'];
        }
        // Step 3: Load view kết quả thanh toán
        $this->loadView('paymentResult.php', [
            'transaction' => $transaction,
            'status' => $status
        ]);
    }


    private function callAPIGetUserBalance($user_id) {
        $data = json_encode(['user_id' => $user_id]);
        $ch = curl_init($this->apiGetUserBalance);
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


    private function callAPIUpdateStatus($transaction_id, $status) {
        $data = json_encode(['transaction_id' => $transaction_id, 'status' => $status]);
        $ch = curl_init($this->apiGetNewTrans);
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