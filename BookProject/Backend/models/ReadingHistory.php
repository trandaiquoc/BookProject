<?php
use MongoDB\BSON\UTCDateTime;

class Readinghistory {

    private $mongoDB;

    public function __construct($mongoDB) {
        $this->mongoDB = $mongoDB;
    }

    public function uplog_Read($user_id, $book_id, $last_page, $total_page) {
        $collection = $this->mongoDB->reading_history;

        $progress = $total_page > 0 ? ($last_page / $total_page) * 100 : 0;

        $timestamp = new UTCDateTime();

        $doc = [
            'user_id' => $user_id,
            'book_id' => $book_id,
            'timestamp' => $timestamp,
            'last_page' => $last_page,
            'progress' => $progress
        ];

        try {
            // 1. Xóa tất cả entry cũ hơn timestamp cho cùng user/book
            $collection->deleteMany([
                'user_id' => $user_id,
                'book_id' => $book_id
            ]);

            // 2. Thêm entry mới
            $result = $collection->insertOne($doc);

            return $result->getInsertedCount() === 1; // true nếu insert thành công
        } catch (\Exception $e) {
            return false;
        }
    }
    public function checkBooksProcess($user_id) {
        $collection = $this->mongoDB->reading_history;

        try {
            // aggregate: nhóm theo book_id, lấy entry mới nhất
            $pipeline = [
                ['$match' => ['user_id' => $user_id]],
                ['$sort' => ['timestamp' => -1]], // sort giảm dần theo thời gian
                ['$group' => [
                    '_id' => '$book_id',
                    'user_id' => ['$first' => '$user_id'],
                    'book_id' => ['$first' => '$book_id'],
                    'last_page' => ['$first' => '$last_page'],
                    'progress' => ['$first' => '$progress'],
                    'timestamp' => ['$first' => '$timestamp']
                ]],
                ['$sort' => ['timestamp' => -1]] // optional: sắp xếp entry mới nhất trước
            ];

            $result = $collection->aggregate($pipeline)->toArray();

            return $result; // trả về mảng các document
        } catch (\Exception $e) {
            return [];
        }
    }
}
?>
