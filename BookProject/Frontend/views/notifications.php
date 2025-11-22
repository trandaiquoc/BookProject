<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/notifications.css">
</head>
<body>
    <div class="title">
        <h1>Thông Báo</h1>
    </div>
    <?php if($notifications): ?>
        <div class="allNotif">
            <?php foreach ($notifications as $i=>$n): ?>
            <div class="notifications-item">
                <div class="notif">
                    <strong><?= $n['action'] ?></strong> <br/>
                    <small class="text-muted"><?= $n['time'] ?></small>
                    <button class="btn remove-log-btn" data-id="<?= $n['_id'] ?>">
                        X
                    </button>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p> Hiện chưa có thông báo!</p>
        </div>
    <?php endif; ?>
<script>
document.querySelectorAll('.remove-log-btn').forEach(button => {
    button.addEventListener('click', function() {
        const logId = this.dataset.id;

        Swal.fire({
            title: 'Bạn có chắc xóa thông báo?',
            text: "Thông báo sẽ mất và không thể khôi phục!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#c0eb6a',
            confirmButtonText: 'Có, xóa ngay!',
            cancelButtonText: 'Giữ lại!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Gửi form POST qua fetch để hủy transaction
                fetch('http://localhost/BookProject/BookProject/Backend/index.php?action=deleteNotification', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: logId })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success'){
                        Swal.fire(
                            'Đã xóa!',
                            'Thông báo đã được xóa!',
                            'success'
                        ).then(()=> location.reload());
                    } else {
                        Swal.fire(
                            'Lỗi!',
                            data.message || 'Không thể xóa thông báo',
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