<?php
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;
use \MongoDB\Driver\Exception\Exception;

class Logs {
    private $mongoDB;
    private $collection;

    public function __construct($mongoDB) {
        $this->mongoDB = $mongoDB;
        $this->collection = $this->mongoDB->Logs;
    }

    public function AddALog($user_id, $action, $keyword) {

        $time = new UTCDateTime();

        $doc = [
            'user_id' => $user_id,
            'action' => $action,
            'keyword' => $keyword,
            'time' => $time
        ];

        try {
            $result = $this->collection->insertOne($doc);

            return $result->getInsertedCount() === 1; // true nếu insert thành công
        } catch (\Exception $e) {
            return false;
        }
    }
   public function getLogsByUserId($user_id, $offset = 0) {
        try {
            $options = ["sort" => ["time" => -1]];

            if ($offset > 0) {
                $options["limit"] = intval($offset); // chỉ lấy số bản ghi mới nhất
            }

            // Nếu user_id trong DB là string thì bỏ intval
            $cursor = $this->collection->find(
                ["user_id" => intval($user_id)],
                $options
            );

            $result = [];
            foreach ($cursor as $log) {
                $createdAt = "";
                if (isset($log["time"])) {
                    if ($log["time"] instanceof MongoDB\BSON\UTCDateTime) {
                        $createdAt = $log["time"]->toDateTime()->format("Y-m-d H:i:s");
                    } else {
                        $createdAt = (string)$log["time"];
                    }
                }

                $result[] = [
                    "_id" => (string)($log["_id"] ?? ""),
                    "action" => $log["action"] ?? "",
                    "time" => $createdAt
                ];
            }

            // Trả về cấu trúc chuẩn
            return [
                "status" => "success",
                "result" => $result
            ];
        } catch (\Exception $e) {
            // Nếu có lỗi DB
            return [
                "status" => "error",
                "result" => [],
                "message" => "Lỗi DB: " . $e->getMessage()
            ];
        }
    }
    public function deleteLogById($logId) {
        $collection = $this->collection;
        try {
            $result = $this->collection->deleteOne(['_id' => new ObjectId($logId)]);
            if ($result->getDeletedCount() > 0) {
                return ['status' => 'success', 'message' => 'Xóa log thành công'];
            } else {
                return ['status' => 'error', 'message' => 'Không tìm thấy log hoặc đã bị xóa trước đó'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Lỗi khi xóa log: ' . $e->getMessage()];
        }
    }

}
?>