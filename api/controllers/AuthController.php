<?php
// api/controllers/AuthController.php
require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate input
        if (!isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $this->db->prepare("INSERT INTO users (first_name, last_name, name, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['first_name'], $data['last_name'], $data['name'], $data['email'], $hashedPassword]);
        
        // Generate token (simple implementation)
        $userId = $this->db->lastInsertId();
        $token = bin2hex(random_bytes(32));
        
        // In a real app, you would store this token in a tokens table
        // For simplicity, we'll just return it
        
        http_response_code(201);
        echo json_encode([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $userId,
                'name' => $data['name'],
                'email' => $data['email']
            ],
            'token' => $token
        ]);
    }

    public function login() {
        error_log("Login method called");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

        // Get raw input data
        $rawInput = file_get_contents('php://input');
        error_log("Login raw input: " . $rawInput);

        $data = null;

        // Check content type to determine how to parse
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            // Handle JSON data
            $data = json_decode($rawInput, true);
            error_log("Login JSON decoded data: " . json_encode($data));

            if ($data === null && !empty($rawInput)) {
                error_log("JSON decode failed, trying alternative parsing");
                // Try to parse as form data if JSON fails
                parse_str($rawInput, $data);
                error_log("Login form parsed data: " . json_encode($data));
            }
        } else {
            // Handle form data
            $data = $_POST;
            error_log("Login POST data: " . json_encode($data));

            // If no POST data, try parsing raw input as form data
            if (empty($data) && !empty($rawInput)) {
                parse_str($rawInput, $data);
                error_log("Login raw input parsed as form data: " . json_encode($data));
            }
        }

        // Validate input
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            error_log("Login validation failed - missing email or password. Data: " . json_encode($data));
            http_response_code(400);
            echo json_encode(['error' => 'Missing email or password']);
            return;
        }

        error_log("Login validation successful - Email: " . $data['email']);

        // Find user
        $stmt = $this->db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        // Generate token
        $token = bin2hex(random_bytes(32));

        // Store token in database (create tokens table if needed)
        $this->storeToken($user['id'], $token);

        echo json_encode([
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ],
            'token' => $token
        ]);
    }

    public function logout() {
        $headers = getallheaders();

        // Get token from Authorization header
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
                // Remove token from database
                $stmt = $this->db->prepare("DELETE FROM user_tokens WHERE token = ?");
                $stmt->execute([$token]);
            }
        }

        echo json_encode(['message' => 'Logged out successfully']);
    }

    public function profile() {
        // Get user ID from header
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $stmt = $this->db->prepare("SELECT id, first_name, last_name, name, email, profile_picture, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'name' => $user['name'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                    'email' => $user['email'],
                    'profile_picture' => $user['profile_picture'] ?? null,
                    'created_at' => $user['created_at']
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    }

    public function updateProfile() {
        // Get user ID from header
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            return;
        }

        try {
            // Build update query dynamically
            $updateFields = [];
            $params = [];

            if (isset($data['name'])) {
                $updateFields[] = "name = ?";
                $params[] = $data['name'];
            }

            if (isset($data['email'])) {
                // Check if email is already taken by another user
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$data['email'], $userId]);
                if ($stmt->fetch()) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'error' => 'Email already taken']);
                    return;
                }
                $updateFields[] = "email = ?";
                $params[] = $data['email'];
            }

            if (empty($updateFields)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No valid fields to update']);
                return;
            }

            $params[] = $userId;
            $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($query);

            if ($stmt->execute($params)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update profile: ' . $e->getMessage()]);
        }
    }

    public function uploadProfilePicture() {
        // Get user ID from header
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        // Debug: Log file upload info
        error_log('Upload debug - FILES: ' . json_encode($_FILES));
        error_log('Upload debug - POST: ' . json_encode($_POST));

        // Check if file was uploaded
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            $error = $_FILES['profile_picture']['error'] ?? 'No file uploaded';
            error_log('Upload error: ' . $error);
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error: ' . $error]);
            return;
        }

        $file = $_FILES['profile_picture'];

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
            return;
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 5MB.']);
            return;
        }

        // Create uploads directory if it doesn't exist (in public directory for web access)
        // Use absolute path to ensure correct directory resolution
        $baseDir = dirname(__DIR__, 2); // Go up two levels from api/controllers/ to project root
        $uploadDir = $baseDir . '/public/uploads/profile_pictures/';

        // Ensure directory exists and is absolute path
        $uploadDir = realpath($uploadDir);
        if (!$uploadDir) {
            // If realpath fails, construct manually and ensure it exists
            $uploadDir = $baseDir . '/public/uploads/profile_pictures/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log('Upload debug - Failed to create directory: ' . $uploadDir);
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
                    return;
                }
            }
            $uploadDir = realpath($uploadDir);
        }

        $uploadDir = rtrim($uploadDir, '/') . '/';

        error_log('Upload debug - Base dir: ' . $baseDir);
        error_log('Upload debug - Upload dir: ' . $uploadDir);
        error_log('Upload debug - Current dir: ' . __DIR__);
        error_log('Upload debug - Directory exists: ' . (is_dir($uploadDir) ? 'YES' : 'NO'));
        error_log('Upload debug - Directory writable: ' . (is_writable($uploadDir) ? 'YES' : 'NO'));

        // Generate unique filename
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;

        error_log('Upload debug - File path: ' . $filePath);
        error_log('Upload debug - Temp file: ' . $file['tmp_name']);

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            error_log('Upload debug - File moved successfully');
            error_log('Upload debug - File exists after move: ' . (file_exists($filePath) ? 'YES' : 'NO'));
            error_log('Upload debug - File size after move: ' . (file_exists($filePath) ? filesize($filePath) : 'N/A'));
            // Delete old profile picture if exists
            $stmt = $this->db->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $oldPicture = $stmt->fetchColumn();

            error_log('Upload debug - Old picture: ' . ($oldPicture ?: 'NULL'));

            if ($oldPicture) {
                $baseDir = dirname(__DIR__, 2); // Go up two levels from api/controllers/ to project root
                $oldFilePath = $baseDir . '/public/' . $oldPicture;
                error_log('Upload debug - Old file path: ' . $oldFilePath);
                error_log('Upload debug - Old file exists: ' . (file_exists($oldFilePath) ? 'YES' : 'NO'));
                if (file_exists($oldFilePath)) {
                    $unlinkResult = unlink($oldFilePath);
                    error_log('Upload debug - Unlink result: ' . ($unlinkResult ? 'SUCCESS' : 'FAILED'));
                }
            }

            // Update database with new profile picture path (relative to public directory)
            $relativePath = 'uploads/profile_pictures/' . $fileName;
            $stmt = $this->db->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $result = $stmt->execute([$relativePath, $userId]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile picture uploaded successfully',
                    'profile_picture' => $relativePath
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update database']);
            }
        } else {
            $moveError = error_get_last();
            error_log('Upload debug - Move failed. Last error: ' . json_encode($moveError));
            error_log('Upload debug - File exists check: ' . (file_exists($file['tmp_name']) ? 'YES' : 'NO'));
            error_log('Upload debug - Target dir writable: ' . (is_writable($uploadDir) ? 'YES' : 'NO'));

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to save file to: ' . $filePath,
                'debug' => [
                    'temp_file_exists' => file_exists($file['tmp_name']),
                    'target_dir_writable' => is_writable($uploadDir),
                    'upload_dir' => $uploadDir,
                    'file_path' => $filePath,
                    'last_error' => $moveError
                ]
            ]);
        }
    }

    private function storeToken($userId, $token) {
        try {
            // Create tokens table if it doesn't exist
            $this->createTokensTable();

            // Remove any existing tokens for this user
            $stmt = $this->db->prepare("DELETE FROM user_tokens WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Store new token
            $stmt = $this->db->prepare("INSERT INTO user_tokens (user_id, token, created_at, expires_at) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))");
            $stmt->execute([$userId, $token]);

        } catch (Exception $e) {
            error_log("Token storage error: " . $e->getMessage());
        }
    }

    private function validateToken($token) {
        if (!$token) return null;

        try {
            $stmt = $this->db->prepare("SELECT user_id FROM user_tokens WHERE token = ? AND expires_at > NOW()");
            $stmt->execute([$token]);
            $result = $stmt->fetch();

            return $result ? $result['user_id'] : null;
        } catch (Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
            return null;
        }
    }

    private function createTokensTable() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS user_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(64) NOT NULL UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NOT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
        } catch (Exception $e) {
            error_log("Create tokens table error: " . $e->getMessage());
        }
    }

    private function getUserIdFromHeader() {
        $headers = getallheaders();

        // First try to validate token from Authorization header (case-insensitive)
        $authHeader = null;
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }

        if ($authHeader) {
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
                $userId = $this->validateToken($token);
                if ($userId) {
                    return $userId;
                }
            }
        }

        // Fallback to X-User-ID header (for backward compatibility)
        if (isset($headers['X-User-ID']) || isset($headers['x-user-id'])) {
            $userIdHeader = $headers['X-User-ID'] ?? $headers['x-user-id'];
            return intval($userIdHeader);
        }

        return null;
    }
}