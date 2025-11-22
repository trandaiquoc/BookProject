<?php
require_once('config/config.inc.php');

function autoloadMVC($directory)
{
    foreach (glob($directory . '/*.php') as $filename) {
        require_once($filename);
    }
}
autoloadMVC('models');
autoloadMVC('controllers');

$action = "";
$db = getDB();
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "home";


switch ($action) {
    case "login":
        $controller = new UserController($db, $mongoDB);
        $controller->login();
        break;
    case "register":
        $controller = new UserController($db, $mongoDB);
        $controller->register();
        break;
    case "checkUser":
        $controller = new UserController($db, $mongoDB);
        $controller->checkUser();
        break;
    case "resetPassword":
        $controller = new UserController($db, $mongoDB);
        $controller->resetPassword();
        break;
    case "updateProfile":
        $controller = new UserController($db, $mongoDB);
        $controller->updateProfile();
        break;
    case "getUploadedBooks":
        $controller = new BookController($db,  $mongoDB);
        $controller->getUploadedBooks();
        break;
    case "getFavoritedBooks":
        $controller = new BookController($db,  $mongoDB);
        $controller->getFavoritedBooks();
        break;
    case "getBook":
        $controller = new BookController($db,  $mongoDB);
        $controller->getBookInfo();
        break;
    case "getBookComments":
        $controller = new BookController($db,  $mongoDB);
        $controller->getBookComments();
        break;
    case "submitComment":
        $controller = new BookController($db,  $mongoDB);
        $controller->submitComment();
        break;
    case "saveReadingState":
        $controller = new BookController($db,  $mongoDB);
        $controller->saveReadingState();
        break;
    case "getTransaction":
        $controller = new TransactionController($db, $mongoDB);
        $controller->getTransaction();
        break;
    case "manageBooks":
        $controller = new BookController($db, $mongoDB);
        $controller->getAllManageBooks();
        break;
    case "saveBook":
        $controller = new BookController($db, $mongoDB);
        $controller->favoriteBook();
        break;
    case "unSaveBook":
        $controller = new BookController($db, $mongoDB);
        $controller->unFavoriteBook();
        break;
    case "createTransaction":
        $controller = new TransactionController($db, $mongoDB);
        $controller->createTransaction();
        break;
    case "createZaloPayOrder":
        $controller = new TransactionController($db, $mongoDB);
        $controller->createZaloPayOrder();
        break;
    case "getNewTransaction":
        $controller = new TransactionController($db, $mongoDB);
        $controller->getNewTransaction();
        break;
    case "deleteTransaction":
        $controller = new TransactionController($db, $mongoDB);
        $controller->deleteTransaction();
        break;
    case "cancelTransaction":
        $controller = new TransactionController($db, $mongoDB);
        $controller->cancelTransaction();
        break;
    case "getUserBalance":
        $controller = new UserController($db,$mongoDB);
        $controller->getUserBalance();
        break;
    case "getUserLogs":
        $controller = new UserController($db,$mongoDB);
        $controller->getLogs();
        break;
    case "deleteNotification":
        $controller = new UserController($db,$mongoDB);
        $controller->deleteNotification();
        break;
    case "viewAllLogs":
        $controller = new UserController($db,$mongoDB);
        $controller->viewAllLogs();
        break;
    case "search":
        $controller = new BookController($db,$mongoDB);
        $controller->search();
        break;
    default:
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            echo json_encode([
                "status" => "error",
                "message" => "Invalid action or API not found",
                "url_requested" => $requestUri
            ]);
        break;
}
?>