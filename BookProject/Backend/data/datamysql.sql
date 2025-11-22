USE BookProject;

-- Thêm User
INSERT INTO `User` (username, password, name, balance, birthday, role, status)
VALUES 
('alice', '$2y$10$ye23ebvMCAwVD4wclEmLreAGidhMYjS/RzNspj8SRryrB0iLXPEVa', 'Alice Nguyễn', 50000000,'2000-01-01', 'user', 'active'),
('bob', '$2y$10$ye23ebvMCAwVD4wclEmLreAGidhMYjS/RzNspj8SRryrB0iLXPEVa', 'Bob Trần', 30000000,'1999-05-15', 'user', 'active'),
('admin', '$2y$10$ye23ebvMCAwVD4wclEmLreAGidhMYjS/RzNspj8SRryrB0iLXPEVa', 'Quản trị viên', 99999999999, '1990-12-12', 'admin', 'active');

-- Thêm Category
INSERT INTO `Category` (name)
VALUES ('Giáo Dục'), ('Thông Tin'), ('Kinh Doanh');

-- Thêm dữ liệu vào Book
INSERT INTO `Book` (name, avatar, author, language, edition, url, upload_id, verified, visits, `describe`)
VALUES
('Nhà Đầu Tư 1970 - Phần 2', '', 'Việt Nam', 'Tiếng Việt', '1st', 'public/books/1_Nha Dau Tu 1970_P2.pdf', 1, 'verified', 2344, 'không có gì để nói ở đây'),
('NHỮNG THỦ THUẬT AN NINH MẠNG TỪ MỘT CỰU HACKER', '', 'Hiếu PC', 'Tiếng Việt', '1st', 'public/books/1_NHỮNG THỦ THUẬT AN NINH MẠNG TỪ MỘT CỰU HACKER - HIEUPC.pdf', 1, 'verified', 20, 'đây chỉ là 1 dự án nhỏ'),
('Bài tập bổ trợ nâng cao tiếng anh 7', '', 'Bộ GD&ĐT', 'Tiếng Việt', '2nd', 'public/books/2_Bài tập bổ trợ nâng cao tiếng anh 7.pdf', 2, 'verified',222, 'helloooooooo');

-- Gán Book vào Category
INSERT INTO `BookCategory` (book_id, category_id) VALUES
  (1, 3),
  (2, 2),
  (3, 1);

-- FavoriteBooks
INSERT INTO `FavoriteBooks` (user_id, book_id) VALUES
  (1, 2),
  (2, 1);


-- Transaction (Mua sách)
INSERT INTO `Transaction` (user_id, book_id, amount, status)
VALUES 
(1, 1, 50000, 'success'), 
(2, 3, 120000, 'pending'); 

-- Comment
INSERT INTO `Comment` (user_id, book_id, content)
VALUES 
(1, 1, 'Wow'),
(2, 2, 'Hayyy');

-- Rating
INSERT INTO `Rating` (user_id, book_id, score)
VALUES 
(1, 1, 5), 
(2, 2, 4); 

-- Report
INSERT INTO `Report` (user_id, book_id, reason, status)
VALUES 
(2, 1, 'Nội dung không chính xác', 'pending');

-- Follow
INSERT INTO `Follow` (follower_id, following_id)
VALUES 
(1, 2), 
(2, 1);
