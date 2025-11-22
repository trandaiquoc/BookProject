document.addEventListener("DOMContentLoaded", function() {
    const userId = USER_ID;
    const icon = document.getElementById("notificationBtn");
    const dropdownEl = icon.closest('.dropdown');
    const notificationList = document.getElementById("notificationList");

    // Hàm cập nhật icon notification
    function updateNotificationIcon() {
        fetch(`http://localhost/BookProject/BookProject/Backend/index.php?action=getUserLogs&user_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                icon.src = data.length > 0 
                    ? "public/images/system/notification.png"
                    : "public/images/system/bell.png";
            });
    }

    // Hàm load notifications vào dropdown
    function loadNotifications() {
        fetch(`http://localhost/BookProject/BookProject/Backend/index.php?action=getUserLogs&user_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (!data || data.length === 0) {
                    notificationList.innerHTML = "<li class='dropdown-item text-center'>Không có thông báo</li>";
                        icon.src = "public/images/system/bell.png";
                    return;
                } else {
                    data.forEach(item => {
                        html += `
                            <li class="dropdown-item position-relative">
                                <div><strong>${item.action}</strong>
                                <small class="text-muted">${item.time}</small></div>
                                <button class="btn remove-log-btn" data-id="${item._id}"
                                style="position: absolute; top: 5px; right: 5px; padding: 0 5px; font-size: 10px; line-height: 1; color:#485550;">
                                    X
                                </button>
                            </li>
                        `;
                    });
                    html += `
                        <li class="dropdown-item text-center">
                            <a href="index.php?action=viewAllLogs" 
                            style="font-size: 12px; color: #485550; opacity: 0.6; text-decoration: none;">
                                Xem tất cả
                            </a>
                        </li>
                    `;
                }
                notificationList.innerHTML = html;

                // Gắn sự kiện xóa notification
                notificationList.querySelectorAll(".remove-log-btn").forEach(btn => {
                    btn.addEventListener("click", function(e) {
                        e.stopPropagation();
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
                        }).then(result => {
                            if (result.isConfirmed) {
                                fetch('http://localhost/BookProject/BookProject/Backend/index.php?action=deleteNotification', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ id: logId })
                                })
                                .then(res => res.json())
                                .then(resp => {
                                    if(resp.status === 'success'){
                                        Swal.fire('Đã xóa!', 'Thông báo đã được xóa!', 'success')
                                            .then(() => {// Xóa luôn element cũ thay vì reload toàn bộ dropdown
                                                btn.closest('li').remove();

                                                // Nếu không còn thông báo nào, hiển thị message
                                                if(notificationList.querySelectorAll('li.position-relative').length === 0){
                                                    notificationList.innerHTML = "<li class='dropdown-item text-center'>Không có thông báo</li>";
                                                    icon.src = "public/images/system/bell.png";
                                                }
                                            });
                                    } else {
                                        Swal.fire('Lỗi!', resp.message || 'Không thể xóa thông báo', 'error');
                                    }
                                });
                            }
                        });
                    });
                });
            });
    }

    // Khi dropdown mở, load notifications
    dropdownEl.addEventListener('show.bs.dropdown', loadNotifications);

    // Cập nhật icon khi load trang và định kỳ
    updateNotificationIcon();
    setInterval(updateNotificationIcon, 30000);
});