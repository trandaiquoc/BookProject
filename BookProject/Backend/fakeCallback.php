<?php
// Fake Callback ZaloPay gửi về backend

header("Content-Type: application/json; charset=UTF-8");

$transaction_id = intval($_GET['transaction_id'] ?? 0);
$status = $_GET['status'] ?? "0"; // 1=success, 0=failed

if ($transaction_id <= 0) {
    echo json_encode([
        'return_code' => -1,
        'return_message' => 'Invalid transaction ID'
    ]);
    exit;
}

// Gửi callback tới backend
$callbackData = json_encode([
    "transaction_id" => $transaction_id,
    "status" => $status
]);

$backendUrl = "http://localhost/BookProject/BookProject/Backend/index.php?action=getNewTransaction";

$ch = curl_init($backendUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $callbackData,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);

$resp = curl_exec($ch);
curl_close($ch);

echo json_encode([
    'return_code' => 1,
    'return_message' => 'Callback processed',
    'backend_response' => json_decode($resp, true)
]);