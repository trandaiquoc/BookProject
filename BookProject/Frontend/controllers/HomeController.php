<?php

class HomeController extends Controller {
    private $apiGetAllLogs;
    public function __construct() {
        // Đường dẫn API backend
        $this->apiGetAllLogs = "http://localhost/BookProject/BookProject/Backend/index.php?action=viewAllLogs";
    }
    public function index()
    {
        $this->loadView('home.php');
    }

    public function viewAllLogs()
    {
        $user_id = $_SESSION['user']['user_id'];
        $response = $this->callAPIgetAllLogs($user_id);
        $result = json_decode($response, true);
        if ($response === false || $result === null) {
            $this->loadView('error.php', [
            'message' => 'Không nhận được phản hồi từ server hoặc dữ liệu không hợp lệ.',
            ]);
            return;
        }
        if (isset($result['status']) && $result['status'] === 'success') {         
           $this->loadView('notifications.php', 
            ['notifications' => $result['logs'] ?? []]);
        } else {
            $this->loadView('error.php', ['message' => $result['message']]);
        }
    }

    private function callAPIgetAllLogs($userid) {
        $data = json_encode(['user_id' => $userid]);
        $ch = curl_init($this->apiGetAllLogs);
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