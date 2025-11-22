const submitBtn = document.getElementById('submit-comment');
const commentInput = document.getElementById('comment');
const errorDiv = document.getElementById('comment-error');
const commentsList = document.querySelector('#reviews .comments-list');

let bannedWords = [];
let selectedRating = 0; // Biến lưu số sao người dùng chọn
const stars = document.querySelectorAll('#reviews .rating .star');

// --- Load từ cấm ---
fetch('public/banWords.txt')
    .then(res => res.text())
    .then(text => {
        bannedWords = text.split('\n').map(w => w.trim().toLowerCase()).filter(w => w.length > 0);
    });

// --- Kiểm tra từ cấm ---
function containsBannedWord(text){
    const lowerText = text.toLowerCase();
    return bannedWords.some(word => {
        if(!word) return false;
        const escapedWord = word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const pattern = new RegExp(`(^|[\\s.,!?;:()\"'])${escapedWord}(?=[\\s.,!?;:()\"']|$)`, 'i');
        return pattern.test(lowerText);
    });
}

// --- Chọn sao ---
stars.forEach(star => {
    const val = parseInt(star.dataset.value);
    star.addEventListener('mouseover', () => {
        stars.forEach(s => s.classList.toggle('hover', parseInt(s.dataset.value) <= val));
    });
    star.addEventListener('mouseout', () => {
        stars.forEach(s => {
            s.classList.remove('hover');
            s.classList.toggle('selected', parseInt(s.dataset.value) <= selectedRating);
        });
    });
    star.addEventListener('click', () => {
        selectedRating = val;
        stars.forEach(s => s.classList.toggle('selected', parseInt(s.dataset.value) <= selectedRating));
    });
});

// --- Hàm tạo HTML sao đẹp ---
function createStarsHTML(rating){
    const maxRating = 5;
    let starsHtml = '';
    for(let i = 1; i <= maxRating; i++){
        if(i <= rating){
            starsHtml += '<span class="sao selected">&#9733;</span>';
        } else {
            starsHtml += '<span class="sao">&#9733;</span>';
        }
    }
    return starsHtml;
}

// --- Submit comment ---
submitBtn.addEventListener('click', () => {
    const comment = commentInput.value.trim();
    if(comment === '' || selectedRating === 0){
        errorDiv.textContent = 'Vui lòng nhập bình luận và chọn sao!';
        return;
    }
    if(containsBannedWord(comment)){
        errorDiv.textContent = 'Bình luận chứa từ vi phạm nguyên tắc cộng đồng!';
        return;
    }

    errorDiv.textContent = '';

    fetch(`http://localhost/BookProject/BookProject/Backend/index.php?action=submitComment`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
            comment,
            rating: selectedRating,
            book_id: BOOK_ID,
            user_id: USER_ID
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            // Tạo comment mới
            const div = document.createElement('div');
            div.classList.add('comment-item');
            div.innerHTML = `
                <img src="${USER_AVATAR}" class="comment-avatar">
                <div class="comment-content">
                    <div class="comment-header">
                        <strong>${NAME}</strong>
                        <span class="comment-stars">
                            ${createStarsHTML(selectedRating)}
                        </span>
                    </div>
                    <div class="comment-text">${comment}</div>
                </div>
            `;
            commentsList.prepend(div);

            // Reset input và sao
            commentInput.value = '';
            selectedRating = 0;
            stars.forEach(s => s.classList.remove('selected'));
        } else {
            errorDiv.textContent = data.message || 'Có lỗi xảy ra';
        }
    })
    .catch(err => errorDiv.textContent = 'Có lỗi xảy ra');
});
