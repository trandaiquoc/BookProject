<?php

class ZaloPayModel {

    // Thêm $balance vào params
    public function createOrder($transaction_id, $amount = 0, $user = "user_default", $balance = 0) {

        return [
            'success' => true,
            'checkout_url' => "http://localhost/BookProject/BookProject/Frontend/fakeCheckout.php"
                . "?transaction_id={$transaction_id}"
                . "&amount={$amount}"
                . "&user={$user}"
                . "&balance={$balance}", // truyền balance vào URL
            'zp_trans_token' => "sandbox_token_{$transaction_id}",
            'app_trans_id' => date("ymd") . "_" . $transaction_id,
            'balance' => $balance
        ];
    }
}
?>
