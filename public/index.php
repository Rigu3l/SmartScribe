<?php
// public/index.php

// CORS headers for all requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-ID, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Set JSON content type for API requests (but not for HTML serving or file exports)
if (!empty($_GET['resource']) || !empty($_GET['action'])) {
    $resource = $_GET['resource'] ?? '';
    if ($resource !== 'export') {
        header("Content-Type: application/json");
    }
}

// Check for query parameters first (for direct index.php calls)
$resource = $_GET['resource'] ?? '';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Debug logging for request parsing (can be removed in production)
error_log("API Request - Resource: '$resource', Action: '$action', ID: '$id'");

// If no resource is specified, serve the Vue.js app
if (empty($resource) && empty($action)) {
    error_log("DEBUG: No resource or action found, serving Vue.js app");
    include __DIR__ . '/index.html';
    exit;
}

// Set JSON content type for API requests (skip for export)
if ($resource !== 'export') {
    header("Content-Type: application/json");
}

// Debug logging
error_log("API Request - Resource: $resource, Action: $action, Method: " . $_SERVER['REQUEST_METHOD']);
error_log("API Request - URI: " . $_SERVER['REQUEST_URI']);
error_log("API Request - Query: " . $_SERVER['QUERY_STRING']);
error_log("API Request - Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("API Request - POST data: " . json_encode($_POST));
error_log("API Request - Raw input length: " . strlen(file_get_contents('php://input')));

// If no query parameters, parse from URI (for URL rewriting)
if (empty($resource)) {
    $request_uri = $_SERVER['REQUEST_URI'];
    $uri_parts = explode('/', trim($request_uri, '/'));

    // Remove 'SmartScribe-main' and 'public' from the beginning if present
    if ($uri_parts[0] === 'SmartScribe-main') {
        array_shift($uri_parts);
    }
    if ($uri_parts[0] === 'public') {
        array_shift($uri_parts);
    }

    // Get the resource and id
    $resource = $uri_parts[0] ?? '';
    $action = $uri_parts[1] ?? '';
    $id = $uri_parts[2] ?? null;
}

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
    case 'gpt':
        require_once __DIR__ . '/../api/controllers/GPTController.php';
        $controller = new GPTController();
        break;
    case 'dashboard':
        require_once __DIR__ . '/../api/controllers/DashboardController.php';
        $controller = new DashboardController($db);
        break;
    case 'progress':
        require_once __DIR__ . '/../api/controllers/ProgressController.php';
        $controller = new ProgressController($db);
        break;
    case 'settings':
        require_once __DIR__ . '/../api/controllers/SettingsController.php';
        $controller = new SettingsController($db);
        break;
    case 'export':
        require_once __DIR__ . '/../api/controllers/ExportController.php';
        $controller = new ExportController($db);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
        exit;
}

if ($resource === 'dashboard') {
    // Special handling for dashboard endpoints
    if ($action === 'stats') {
        $controller->getStats();
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Dashboard endpoint not found']);
    }
} elseif ($resource === 'progress') {
    // Special handling for progress endpoints
    $action = $uri_parts[1] ?? '';
    if ($action === 'stats') {
        $controller->getStats();
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Progress endpoint not found']);
    }
} elseif ($resource === 'gpt') {
    // Handle GPT endpoints
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'POST') {
        if ($action === 'generateSummary') {
            $controller->generateSummary();
        } elseif ($action === 'generateQuiz') {
            $controller->generateQuiz();
        } elseif ($action === 'extractKeywords') {
            $controller->extractKeywords();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'GPT action not found']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} elseif ($resource === 'settings') {
    // Handle settings endpoints
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
        $controller->getSettings();
    } elseif ($method === 'PUT' || $method === 'POST') {
        $controller->updateSettings();
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} elseif ($resource === 'export') {
    // Handle export endpoints
    $method = $_SERVER['REQUEST_METHOD'];
    $format = $_GET['format'] ?? 'pdf';

    if ($method === 'GET' && $id) {
        $controller->export($id, $format);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} elseif ($resource !== 'auth') {
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
            if ($id) {
                $controller->update($id);
            } else {
                $controller->store();
            }
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
    // Handle auth actions
    if ($resource === 'auth') {
        switch ($action) {
            case 'login':
                $controller->login();
                break;
            case 'register':
                $controller->register();
                break;
            case 'logout':
                $controller->logout();
                break;
            case 'profile':
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $controller->profile();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                    $controller->updateProfile();
                }
                break;
            case 'upload-profile-picture':
                $controller->uploadProfilePicture();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Auth action not found']);
                break;
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
    }
}