<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Affiliate.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/TransferController.php';

use Controllers\AuthController;
use Controllers\TransferController;

// Tính base path động
$basePath = '/baby3'; // Hardcode vì dùng .htaccess
$request = strtok($_SERVER['REQUEST_URI'], '?');
$request = str_replace($basePath, '', $request);
$request = rtrim($request, '/');

error_log("Base Path: " . $basePath);
error_log("Raw Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Processed Request: " . $request);
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

$controller = null;

switch ($request) {
    case '':
    case '/':
    case '/login':
        $controller = new AuthController();
        $controller->login();
        break;
    case '/dashboard':
        $controller = new TransferController();
        $controller->dashboard();
        break;
    default:
        http_response_code(404);
        echo "404 - Không tìm thấy trang: " . htmlspecialchars($request);
        error_log("404 - Không tìm thấy: " . $request);
        break;
}