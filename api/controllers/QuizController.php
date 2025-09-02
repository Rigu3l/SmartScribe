<?php
require_once __DIR__ . '/../models/Quiz.php';

class QuizController {
    private $db;
    private $quiz;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->quiz = new Quiz($db);
    }

    public function index() {
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized"
            ]);
            return;
        }

        $query = "SELECT q.*
                  FROM quizzes q
                  INNER JOIN notes n ON q.note_id = n.id
                  WHERE n.user_id = :user_id
                  ORDER BY q.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        echo json_encode([
            "success" => true,
            "data" => $stmt->fetchAll()
        ]);
    }

    public function store() {
        error_log("=== QUIZ STORE START ===");
        $userId = $this->getUserIdFromHeader();
        error_log("QUIZ STORE: User ID from header: " . ($userId ?: 'null'));

        if (!$userId) {
            error_log("QUIZ STORE: Unauthorized - no user ID");
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized"
            ]);
            return;
        }

        $rawInput = file_get_contents('php://input');
        error_log("QUIZ STORE: Raw input length: " . strlen($rawInput));
        error_log("QUIZ STORE: Raw input content: " . substr($rawInput, 0, 200) . "...");

        $data = json_decode($rawInput, true);
        error_log("QUIZ STORE: JSON decode result: " . ($data ? 'success' : 'failed'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("QUIZ STORE: JSON decode error: " . json_last_error_msg());
        }

        if (!isset($data['note_id']) || !isset($data['questions'])) {
            error_log("QUIZ STORE: Missing note_id or questions");
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing note_id or questions']);
            return;
        }

        error_log("QUIZ STORE: note_id: " . $data['note_id']);
        error_log("QUIZ STORE: questions count: " . (is_array($data['questions']) ? count($data['questions']) : 'not array'));

        // Verify the note belongs to the authenticated user
        $noteQuery = "SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id";
        $stmt = $this->db->prepare($noteQuery);
        $stmt->bindParam(':note_id', $data['note_id']);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $noteResult = $stmt->fetch();
        error_log("QUIZ STORE: Note verification result: " . ($noteResult ? 'found' : 'not found'));

        if (!$noteResult) {
            error_log("QUIZ STORE: Access denied - note doesn't belong to user");
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }

        $this->quiz->note_id = $data['note_id'];
        $this->quiz->questions = json_encode($data['questions']);
        $this->quiz->difficulty = $data['difficulty'] ?? 'medium';
        $this->quiz->score = $data['score'] ?? null;

        // Optional metadata for multi-note quizzes
        if (isset($data['source'])) {
            error_log("QUIZ STORE: Quiz source: " . $data['source']);
        }
        if (isset($data['note_count'])) {
            error_log("QUIZ STORE: Note count: " . $data['note_count']);
        }

        error_log("QUIZ STORE: Quiz object prepared - note_id: {$this->quiz->note_id}, difficulty: {$this->quiz->difficulty}");

        $quizId = $this->quiz->create();
        error_log("QUIZ STORE: Quiz creation result: " . ($quizId ? "success (ID: $quizId)" : 'failed'));

        if ($quizId) {
            error_log("QUIZ STORE: Quiz created successfully with ID: $quizId");
            echo json_encode([
                "success" => true,
                "message" => "Quiz created successfully",
                "quiz_id" => $quizId
            ]);
        } else {
            error_log("QUIZ STORE: Failed to create quiz");
            echo json_encode([
                "success" => false,
                "error" => "Failed to create quiz"
            ]);
        }
        error_log("=== QUIZ STORE END ===");
    }

    public function show($id) {
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized"
            ]);
            return;
        }

        $query = "SELECT q.*
                  FROM quizzes q
                  INNER JOIN notes n ON q.note_id = n.id
                  WHERE q.id = :id AND n.user_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $quiz = $stmt->fetch();

        if ($quiz) {
            // Decode questions JSON
            $quiz['questions'] = json_decode($quiz['questions'], true);
            echo json_encode([
                "success" => true,
                "data" => $quiz
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Quiz not found']);
        }
    }

    public function update($id) {
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized"
            ]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Verify the quiz belongs to the authenticated user
        $quizQuery = "SELECT q.id FROM quizzes q
                      INNER JOIN notes n ON q.note_id = n.id
                      WHERE q.id = :id AND n.user_id = :user_id";
        $stmt = $this->db->prepare($quizQuery);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }

        $updateFields = [];
        $params = [':id' => $id];

        if (isset($data['score'])) {
            $updateFields[] = "score = :score";
            $params[':score'] = $data['score'];
        }

        if (isset($data['questions'])) {
            $updateFields[] = "questions = :questions";
            $params[':questions'] = json_encode($data['questions']);
        }

        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No valid fields to update']);
            return;
        }

        $query = "UPDATE quizzes SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute($params)) {
            echo json_encode([
                "success" => true,
                "message" => "Quiz updated successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Failed to update quiz"
            ]);
        }
    }

    public function destroy($id) {
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized"
            ]);
            return;
        }

        // Verify the quiz belongs to the authenticated user
        $quizQuery = "SELECT q.id FROM quizzes q
                      INNER JOIN notes n ON q.note_id = n.id
                      WHERE q.id = :id AND n.user_id = :user_id";
        $stmt = $this->db->prepare($quizQuery);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied or quiz not found']);
            return;
        }

        if ($this->quiz->delete($id, $userId)) {
            echo json_encode([
                "success" => true,
                "message" => "Quiz deleted successfully"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "Failed to delete quiz"
            ]);
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
}
?>