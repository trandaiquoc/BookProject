<?php
session_start();

// ==========================
// AUTOLOAD CONTROLLERS + MODELS
// ==========================
function autoloadMVC($directory)
{
    // nạp Controller.php trước
    $controllerBase = __DIR__ . '/' . $directory . '/Controller.php';
    if (file_exists($controllerBase)) {
        require_once($controllerBase);
    }

    // sau đó nạp các controller còn lại
    foreach (glob(__DIR__ . '/' . $directory . '/*.php') as $filename) {
        if (basename($filename) !== 'Controller.php') {
            require_once($filename);
        }
    }
}
autoloadMVC('models');
autoloadMVC('controllers');

// ==========================
// FRONTEND ROUTER
// ==========================
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'home';

// ==========================
// XỬ LÝ CÁC ACTION
// ==========================
switch ($action) {

    case "home":
        $controller = new HomeController();
        $controller->index();
        break;    
    case "login":
        $controller = new UserController(); // không cần $db ở frontend
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller->login(); // gọi API backend
        } else {
            $controller->showLogin(); // hiển thị trang login
        }
        break;
    case "logout":
        $controller = new UserController();
        $controller->logout();
        break;
    case "register":
        $controller = new UserController();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller->register(); // gọi API backend
        } else {
            $controller->showRegister(); // hiển thị trang login
        }
        break;
    case "updatePassword":
        $controller = new UserController();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller->updatePassword(); // gọi API backend
        } else {
            $controller->showForgot(); // hiển thị trang login
        }
        break;
    case "forgotPassword":
        $controller = new UserController();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller->forgotPassword(); // gọi API backend
        } else {
            $controller->showForgot(); // hiển thị trang login
        }
        break;
    case "profile":
        $controller = new UserController();
        $controller->showProfile([]); // mở trang xem profile
        break;
    case "updateProfile":
        $controller = new UserController();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller->updateProfile(); // gọi API backend
        } else {
            $controller->showUpdateProfile(); // hiển thị trang login
        }
        break;
    case "bookProfile":
        $controller = new BookController();
        $controller->showBookProfile();
        break;
    case "bookManagement":
        $controller = new BookController();
        $controller->showBookManagement();
        break;
    case "invoiceManagement":
        $controller = new TransactionController();
        $controller->showTransactionHistory();
        break;
    case "saveBook":
        $controller = new BookController();
        $controller->saveBook();
        break;
    case "unSaveBook":
        $controller = new BookController();
        $controller->unSaveBook();
        break;
    case "payment":
        $controller = new TransactionController();
        $controller->showPayment();
        break;
    case "processZaloPay":
        $controller = new TransactionController();
        $controller->processZaloPay();
        break;
    case "paymentResult":
        $controller = new TransactionController();
        $controller->paymentResult();
        break;
    case "viewAllLogs":
        $controller = new HomeController();
        $controller->viewAllLogs();
        break;
    default:
        include('views/error.php');
        break;
}
?>
