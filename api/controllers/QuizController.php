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

        if (!isset($data['note_id']) || !isset($data['questions'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing note_id or questions']);
            return;
        }

        // Verify the note belongs to the authenticated user
        $noteQuery = "SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id";
        $stmt = $this->db->prepare($noteQuery);
        $stmt->bindParam(':note_id', $data['note_id']);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }

        $this->quiz->note_id = $data['note_id'];
        $this->quiz->questions = json_encode($data['questions']);
        $this->quiz->difficulty = $data['difficulty'] ?? 'medium';
        $this->quiz->score = $data['score'] ?? null;

        $quizId = $this->quiz->create();

        if ($quizId) {
            echo json_encode([
                "success" => true,
                "message" => "Quiz created successfully",
                "quiz_id" => $quizId
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Failed to create quiz"
            ]);
        }
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

    private function getUserIdFromHeader() {
        $headers = getallheaders();
        if (isset($headers['X-User-ID'])) {
            return intval($headers['X-User-ID']);
        }
        return null;
    }
}
?>