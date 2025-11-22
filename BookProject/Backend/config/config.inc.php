<?php
require_once 'db.inc.php';

function getDB() {
    $dbConnection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($dbConnection->connect_error) {
        die("Connection failed: " . $dbConnection->connect_error);
    }
    return $dbConnection;
}

// MongoDB
require_once __DIR__ . '/../../vendor/autoload.php'; // cần thư viện mongodb/mongodb

try {
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    $mongoDB = $mongoClient->BookProject; // DB Mongo cũng tên BookProject
} catch (Exception $e) {
    die("Kết nối MongoDB thất bại: " . $e->getMessage());
}