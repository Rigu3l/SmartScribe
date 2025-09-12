<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Quiz.php';

class QuizController extends BaseController {
    private $quiz;

    public function __construct() {
        parent::__construct();
        $this->quiz = new Quiz($this->db);
    }

    public function index() {
        if (!$this->authenticateUser()) {
            $this->unauthorizedResponse();
            return;
        }

        $userId = $this->getUserId();

        $query = "SELECT q.*
                  FROM quizzes q
                  INNER JOIN notes n ON q.note_id = n.id
                  WHERE n.user_id = :user_id
                  ORDER BY q.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $this->successResponse($stmt->fetchAll());
    }

    public function store() {
        error_log("=== QUIZ STORE START ===");

        if (!$this->authenticateUser()) {
            error_log("QUIZ STORE: Unauthorized - authentication failed");
            $this->unauthorizedResponse();
            return;
        }

        $userId = $this->getUserId();
        error_log("QUIZ STORE: User ID: " . ($userId ?: 'null'));

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
            $this->badRequestResponse('Missing note_id or questions');
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
            $this->errorResponse('Access denied', 403);
            return;
        }

        $this->quiz->note_id = $data['note_id'];
        $this->quiz->user_id = $userId;
        $this->quiz->questions = json_encode($data['questions']);
        $this->quiz->score = $data['score'] ?? null;
        $this->quiz->title = $data['title'] ?? 'Generated Quiz';
        $this->quiz->total_questions = is_array($data['questions']) ? count($data['questions']) : 0;

        // Optional metadata for multi-note quizzes
        if (isset($data['source'])) {
            error_log("QUIZ STORE: Quiz source: " . $data['source']);
        }
        if (isset($data['note_count'])) {
            error_log("QUIZ STORE: Note count: " . $data['note_count']);
        }
        if (isset($data['title'])) {
            error_log("QUIZ STORE: Quiz title: " . $data['title']);
        }
        if (isset($data['note_title'])) {
            error_log("QUIZ STORE: Note title: " . $data['note_title']);
        }

        error_log("QUIZ STORE: Quiz object prepared - note_id: {$this->quiz->note_id}");

        $quizId = $this->quiz->create();
        error_log("QUIZ STORE: Quiz creation result: " . ($quizId ? "success (ID: $quizId)" : 'failed'));

        if ($quizId) {
            error_log("QUIZ STORE: Quiz created successfully with ID: $quizId");
            $this->successResponse([
                "quiz_id" => $quizId
            ], 'Quiz created successfully', 201);
        } else {
            error_log("QUIZ STORE: Failed to create quiz");
            $this->errorResponse('Failed to create quiz');
        }
        error_log("=== QUIZ STORE END ===");
    }

    public function show($id) {
        if (!$this->authenticateUser()) {
            $this->unauthorizedResponse();
            return;
        }

        $userId = $this->getUserId();

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
            $this->successResponse($quiz);
        } else {
            $this->notFoundResponse('Quiz not found');
        }
    }

    public function update($id) {
        if (!$this->authenticateUser()) {
            $this->unauthorizedResponse();
            return;
        }

        $userId = $this->getUserId();

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
            $this->errorResponse('Access denied', 403);
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
            $this->badRequestResponse('No valid fields to update');
            return;
        }

        $query = "UPDATE quizzes SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute($params)) {
            $this->successResponse(null, 'Quiz updated successfully');
        } else {
            $this->errorResponse('Failed to update quiz');
        }
    }

    public function destroy($id) {
        if (!$this->authenticateUser()) {
            $this->unauthorizedResponse();
            return;
        }

        $userId = $this->getUserId();

        // Verify the quiz belongs to the authenticated user
        $quizQuery = "SELECT q.id FROM quizzes q
                      INNER JOIN notes n ON q.note_id = n.id
                      WHERE q.id = :id AND n.user_id = :user_id";
        $stmt = $this->db->prepare($quizQuery);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if (!$stmt->fetch()) {
            $this->errorResponse('Access denied or quiz not found', 403);
            return;
        }

        if ($this->quiz->delete($id, $userId)) {
            $this->successResponse(null, 'Quiz deleted successfully');
        } else {
            $this->errorResponse('Failed to delete quiz');
        }
    }
}
?>