document.addEventListener('DOMContentLoaded', () => {
    if (!window.pdfjsLib) {
        console.error('pdfjsLib chưa load!');
        return;
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.worker.min.js';

    // ==== Biến chung cho reader ====
    let pdfDoc = null; //object đại diện cho PDF đang mở, trả về từ pdfjsLib.getDocument().
    let pageNum = 1; //trang đầu tiên đang hiển thị (ở đây render 2 trang 1 lượt).
    let totalPages = 1; //tổng số trang PDF.
    let CURRENT_BOOK_ID = 0;
    let CURRENT_USER_ID = 0;
    let isZoomed = false;

    const overlay = document.getElementById("reader-overlay");
    const canvas = document.getElementById("pdf-canvas"); //dùng để vẽ đồ họa bằng JavaScript.
    const ctx = canvas.getContext('2d'); //Context 2D là đối tượng chứa các hàm vẽ 2D trên canvas.
    const pageInfoEl = document.getElementById("pageInfo");
    const prevBtn = document.getElementById("prevPage");
    const nextBtn = document.getElementById("nextPage");
    const zoomIn = document.getElementById("zoomIn");
    const zoomOut = document.getElementById("zoomOut");
    const closeBtn = document.getElementById("reader-close");


    if (!canvas || !ctx) console.error('Canvas hoặc context không tồn tại (id="pdf-canvas").');

    window.openReader = function(url, bookId, userId) {
        const relativePath = url.replace(/^\/?/, ''); //loại bỏ / đầu nếu có.
        const safeUrl = window.location.origin + '/BookProject/BookProject/Frontend/' + relativePath.replace(/ /g, '%20'); //encode space.
        console.log('PDF URL full:', safeUrl);

        CURRENT_BOOK_ID = bookId || 0;
        CURRENT_USER_ID = userId || 0;
        pageNum = 1;

        overlay.style.display = "flex"; //chuyển none qua flex

        fetch(safeUrl, { method: 'HEAD' }) //gửi request kiểm tra file có tồn tại, không tải toàn bộ.
            .finally(() => { //bất kể HEAD có lỗi hay không, vẫn thử load PDF.
                pdfjsLib.getDocument(safeUrl).promise.then(pdf => { //tải PDF.
                    pdfDoc = pdf;
                    totalPages = pdf.numPages || 1;
                    renderTwoPages(pageNum);
                }).catch(err => {
                    console.error('pdfjs getDocument lỗi:', err);
                    showReaderError('Không thể mở file PDF.');
                });
            });
    };

    function showReaderError(msg) {
        let errDiv = document.getElementById('reader-error-msg');
        if (!errDiv) {
            errDiv = document.createElement('div');
            errDiv.id = 'reader-error-msg';
            errDiv.style.position = 'absolute';
            errDiv.style.top = '60px';
            errDiv.style.left = '20px';
            errDiv.style.right = '20px';
            errDiv.style.padding = '12px';
            errDiv.style.background = '#ffe6e6';
            errDiv.style.color = '#900';
            errDiv.style.border = '1px solid #f5c2c2';
            errDiv.style.zIndex = 3000;
            document.getElementById('reader-container').appendChild(errDiv);
        }
        errDiv.textContent = msg;
    }

    function clearReaderError() { //xóa thông báo lỗi khi render lại
        const el = document.getElementById('reader-error-msg');
        if (el) el.remove();
    }

    function renderTwoPages(startPage) {
        if (isZoomed) {
            renderSinglePage(startPage);
            return;
        }
        if (!pdfDoc) return;
        clearReaderError();

        const gap = 10;

        Promise.all([ //chờ cả 2 Promise hoàn tất trước khi render.
            pdfDoc.getPage(startPage), //trả về Promise cho trang thứ n.
            pdfDoc.getPage(Math.min(startPage+1, totalPages)) //ránh vượt quá số trang cuối cùng.
        ]).then(([page1, page2]) => { //Khi cả 2 trang load xong, ta nhận mảng kết quả [page1, page2].
            const vp1 = page1.getViewport({ scale: 1 }); //Lấy viewport (kích thước) của 2 trang.
            const vp2 = page2.getViewport({ scale: 1 });
            //Math.floor → làm tròn xuống, tránh số thập phân gây lỗi render.
            canvas.width = Math.floor(vp1.width + vp2.width + gap); //tổng chiều rộng của 2 trang + khoảng cách giữa chúng (gap).
            canvas.height = Math.floor(Math.max(vp1.height, vp2.height)); //lấy chiều cao lớn nhất giữa 2 trang để cả hai trang vừa khít trong canvas.
            //Xóa toàn bộ canvas trước khi render trang mới.
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            /*Tham số	Ý nghĩa
                x	Tọa độ X của góc trái trên của hình chữ nhật cần xóa
                y	Tọa độ Y của góc trái trên của hình chữ nhật cần xóa
                width	Chiều rộng của vùng cần xóa
                height	Chiều cao của vùng cần xóa*/
            page1.render({ canvasContext: ctx, viewport: vp1 }).promise.then(() => { //thực hiện tiếp khi render trang 1 xong.
                //Cần trước khi dùng ctx.translate() để di chuyển origin sang phải.
                ctx.save(); //Lưu trạng thái hiện tại của canvas context (vị trí, transform, scale, ...)
                //Dịch origin của canvas sang phải bằng vp1.width + gap.
                //render trang 2 bên cạnh trang 1, không đè lên trang 1.
                //tất cả các trang sau sẽ được dịch chuyển theo (x, y)
                ctx.translate(vp1.width + gap, 0); 
                page2.render({ canvasContext: ctx, viewport: vp2 }).promise.finally(() => //quay lại origin ban đầu dù render thành công hay thất bại.
                ctx.restore()); //giúp các render sau không bị ảnh hưởng transform cũ.
            });

            pageInfoEl.textContent = `${startPage} - ${Math.min(startPage+1, totalPages)} / ${totalPages}`; //Cập nhật thông tin trang hiển thị
        }).catch(err => showReaderError('Không thể render 2 trang PDF.'));
    }
    function renderSinglePage(pageNumber) {
        if (!pdfDoc) return;
        clearReaderError();

        pdfDoc.getPage(pageNumber).then(page => {
            const container = document.getElementById('pdf-wrapper');
            const viewport = page.getViewport({ scale: 1 });
            // Tính tỉ lệ để vừa width container
            const scale = container.offsetWidth / viewport.width;
            const scaledViewport = page.getViewport({ scale });

            // Cập nhật canvas
            canvas.width = scaledViewport.width;
            canvas.height = scaledViewport.height;
            // set style để canvas vừa container
            canvas.style.width = container.offsetWidth + 'px';
            canvas.style.height = 'auto';


            ctx.clearRect(0, 0, canvas.width, canvas.height);
            page.render({ canvasContext: ctx, viewport: scaledViewport }).promise.catch(err => {
                showReaderError('Không thể render trang PDF.');
            });
            // Cho phép cuộn dọc
            container.style.overflowY = 'auto';
            container.style.overflowX = 'hidden';

            pageInfoEl.textContent = `${pageNumber} / ${totalPages}`;
        });
    }

    nextBtn.addEventListener('click', () => {
        if (isZoomed) {
            if (pageNum < totalPages) { pageNum += 1; renderSinglePage(pageNum); }
        } else {
            if (pageNum + 2 <= totalPages) { pageNum += 2; renderTwoPages(pageNum); }
        }
    });
    prevBtn.addEventListener('click', () => {
        if (isZoomed) {
            if (pageNum > 1) { pageNum -= 1; renderSinglePage(pageNum); }
        } else {
            if (pageNum - 2 >= 1) { pageNum -= 2; renderTwoPages(pageNum); }
        }
    });
    closeBtn.addEventListener('click', () => {
        overlay.style.display = 'none';
        // Nếu đang xem 2 trang → lưu trang phải
        if (!isZoomed) {
            pageNum = pageNum + 1;
        }
        saveReadingState(pageNum); // chỉ khi thoát
    });
    // Zoom PDF
    zoomIn.addEventListener('click', () => {
        if (!pdfDoc) return;
        isZoomed = true;
        renderSinglePage(pageNum);
        toggleZoomButtons();
    });

    zoomOut.addEventListener('click', () => {
        if (!pdfDoc) return;
        isZoomed = false;
        renderTwoPages(pageNum); // trở về 2 trang
        toggleZoomButtons();
    });

    function toggleZoomButtons() {
        zoomIn.style.display = isZoomed ? 'none' : 'inline-block';
        zoomOut.style.display = isZoomed ? 'inline-block' : 'none';
    }

    function saveReadingState(lastPage) {
        const params = new URLSearchParams({
            action: 'saveReadingState',
            book_id: CURRENT_BOOK_ID,
            user_id: CURRENT_USER_ID,
            last_page: lastPage,
            total_page: totalPages
        });

        fetch('http://localhost/BookProject/BookProject/Backend/index.php?' + params.toString(), { method: 'GET' })
            .then(res => res.json().catch(()=>({})))
            .then(data => console.log('Saved reading state', data))
            .catch(err => console.warn('Lưu trạng thái đọc lỗi:', err));
    }
});
