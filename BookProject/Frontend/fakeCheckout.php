<?php
$transaction_id = $_GET['transaction_id'] ?? 0;
$balance = $_GET['balance'] ?? 0;
$amount = $_GET['amount'] ?? 0;

$status = ($balance >= $amount) ? 1 : 0;

// --- Gọi callback ---
$callbackUrl = "http://localhost/BookProject/BookProject/Frontend/fakeCallback.php"
    . "?transaction_id={$transaction_id}"
    . "&status={$status}";

file_get_contents($callbackUrl);

// --- Sau đó redirect user về paymentResult ---
header("Location: index.php?action=paymentResult&transaction_id={$transaction_id}&status={$status}");
exit;
