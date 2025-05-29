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
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
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
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$data['name'], $data['email'], $hashedPassword]);
        
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
        $data = json_decode(file_get_contents('php://input'), true);
        // Validate input
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing email or password']);
            return;
        }
        
        // Find user
        $stmt = $this->db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }
        
        // Generate token (simple implementation)
        $token = bin2hex(random_bytes(32));
        
        // In a real app, you would store this token in a tokens table
        
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
        // In a real app, you would invalidate the token
        echo json_encode(['message' => 'Logged out successfully']);
    }
}