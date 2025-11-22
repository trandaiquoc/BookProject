<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/transaction.css">
</head>
<body>
    <?php if($transactioninfo): ?>
        <?php foreach ($transactioninfo as $i=>$t): ?>
        <div class="transaction-item">
            <div class="top-row">
                <span class="left"><strong>Người thanh toán: </strong><?= htmlspecialchars($t['username']) ?></span>
                <span class="right"><strong>Tên sách: </strong><?= htmlspecialchars($t['bookname']) ?></span>
            </div>
            <div class="middle-row">
                <span class="left"><strong>Tổng thanh toán: </strong><?= number_format($t['amount'], 0, ',', '.') ?> VNĐ</span>
                <span class="right"><strong>Thời gian: </strong><?= htmlspecialchars($t['created_at']) ?></span>
            </div>
            <div class="bottom-row">
                <span class="status <?= htmlspecialchars($t['status']) ?>"><?= htmlspecialchars($t['status']) ?></span>
                <?php if($t['status'] === 'pending'): ?>
                    <!-- Form hủy transaction -->
                    <?php if($t['status'] === 'pending'): ?>
                        <button class="cancel-transaction" data-id="<?= $t['transaction_id'] ?>">Hủy giao dịch</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p> Hiện chưa có giao dịch!</p>
    <?php endif; ?>
<?php if (isset($message)): ?>
    <p style="color: red;"><?php echo $message; ?></p>
<?php endif; ?>  
<script>
document.querySelectorAll('.cancel-transaction').forEach(button => {
    button.addEventListener('click', function() {
        const transactionId = this.dataset.id;

        Swal.fire({
            title: 'Bạn có chắc?',
            text: "Giao dịch sẽ bị hủy và không thể khôi phục!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#c0eb6a',
            confirmButtonText: 'Có, hủy ngay!',
            cancelButtonText: 'Không hủy!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Gửi form POST qua fetch để hủy transaction
                fetch('http://localhost/BookProject/BookProject/Backend/index.php?action=cancelTransaction', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ transaction_id: transactionId })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success'){
                        Swal.fire(
                            'Đã hủy!',
                            'Giao dịch đã được hủy.',
                            'success'
                        ).then(()=> location.reload());
                    } else {
                        Swal.fire(
                            'Lỗi!',
                            data.message || 'Không thể hủy giao dịch',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
</body>