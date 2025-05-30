<?php
// public/index.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', trim($request_uri, '/'));

// Remove 'api' from the beginning if present
if ($uri_parts[0] === 'api') {
    array_shift($uri_parts);
}

// Get the resource and id
$resource = $uri_parts[0] ?? '';
$id = $uri_parts[1] ?? null;

require_once __DIR__ . '/../api/config/database.php';
$db = getDbConnection();

// Include the appropriate controller based on the resource
switch ($resource) {
    case 'auth':
        require_once __DIR__ . '/../api/controllers/AuthController.php';
        $controller = new AuthController();
        break;
    case 'notes':
        require_once __DIR__ . '/../api/controllers/NoteController.php';
        $controller = new NoteController($db);
        break;
    case 'summaries':
        require_once __DIR__ . '/../api/controllers/SummaryController.php';
        $controller = new SummaryController();
        break;
    case 'quizzes':
        require_once __DIR__ . '/../api/controllers/QuizController.php';
        $controller = new QuizController();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
        exit;
}

if ($resource !== 'auth') {
    // Handle the request based on the HTTP method
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;
        case 'POST':
            $controller->store();
           break;
        case 'PUT':
            $controller->update($id);
            break;
        case 'DELETE':
            $controller->destroy($id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} else {
    if ($uri_parts[1] == 'login') {
        $controller->login();
    }

    if ($uri_parts[1] == 'register') {
        $controller->register();
    }
}