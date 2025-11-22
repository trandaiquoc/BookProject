<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán</title>
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

        .payment-items h2 {
            margin-bottom: 20px;
            color: #485550;
        }

        .payment-container p {
            font-size: 1.1em;
            color: #485550;
            margin: 8px 0;
        }
        .success{
            color:#c0eb6a;
        }
        .failed{
            color:#dc3545;
        }
        .btn-return {
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

        .btn-return:hover {
            background-color: #485550;
            color:#f4f6f0;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<div class="payment-container">
    <div class="payment-items">
        <h2>Kết quả thanh toán</h2>
        <p><strong>Sách:</strong> <?= htmlspecialchars($transaction['book_name']) ?></p>
        <p><strong>Người dùng:</strong> <?= htmlspecialchars($transaction['user_name']) ?></p>
        <p><strong>Giá:</strong> <?= number_format($transaction['amount'],0,',','.') ?> VNĐ</p>
        <p><strong>Trạng thái:</strong> 
            <?php if($transaction['status'] === 'success'): ?>
                <span class="success">Thành công</span>
            <?php elseif($transaction['status'] === 'failed'): ?>
                <span class="failed">Thất bại</span>
            <?php else: ?>
                <?= htmlspecialchars($transaction['status']) ?>
            <?php endif; ?>
        </p>
        <a class="btn-return" href="index.php?action=bookProfile&book_id=<?= $transaction['book_id'] ?>">Quay Lại Đọc Sách</a>
    </div>
</div>
</body>
</html>
