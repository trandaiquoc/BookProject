<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>

.payment-container  {
    display: flex;
    flex-direction: column;
    justify-content: center; /* canh giữa ngang */
    align-items: center;
}

.payment-items {
    background-color: #fff;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    width: 50%;
    text-align: center;
}

.payment-items img {
    height: 50px;
    margin-bottom: 20px;
}

.payment-items h2 {
    margin-bottom: 20px;
    color: #485550;
}

.payment-container p {
    font-size: 1.1em;
    color: #485550;
    margin: 8px 0;
}

.btn-zalopay {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 25px;
    margin-top: 25px;
    background-color: #c0eb6a;
    color: #485550;
    text-decoration: none;
    font-weight: bold;
    font-size: 1.1em;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-zalopay:hover {
    background-color: #485550;
    color:#f4f6f0;
    transform: translateY(-2px);
}

</style>
</head>
<body>
<div class="payment-container">
    <div class="payment-items">
        <img src="public/images/system/zalopay-logo.png" alt="ZaloPay Logo"/>
        <h2>Thông tin thanh toán</h2>
        <p><strong>Sách:</strong> <?= htmlspecialchars($book_name) ?></p>
        <p><strong>Người dùng:</strong> <?= htmlspecialchars($user_name) ?></p>
        <p><strong>Giá:</strong> <?= number_format($price,0,',','.') ?> VNĐ</p>

        <a class="btn-zalopay" href="index.php?action=processZaloPay&transaction_id=<?= $transaction_id ?>">Thanh toán</a>
    </div>
</div>
</body>
</html>