<?php
require_once __DIR__ . '/../models/Note.php';
require_once __DIR__ . '/../helpers/FileUpload.php';

class NoteController {
    private $db;
    private $note;

    public function __construct($db) {
        $this->db = $db;
        $this->note = new Note($db);
    }

    public function index() {
        // Get user ID from header (now includes token validation)
        $userId = $this->getUserIdFromHeader();

        // Debug logging
        error_log("NoteController::index() - User ID: $userId");
        error_log("NoteController::index() - All headers: " . json_encode(getallheaders()));

        if (!$userId) {
            error_log("NoteController::index() - No valid authentication found");
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized - Invalid or missing authentication"
            ]);
            return;
        }

        $query = "SELECT n.*
                  FROM notes n
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $notes = $stmt->fetchAll();
        error_log("NoteController::index() - Found " . count($notes) . " notes for user $userId");

        // Also check total notes in database
        $totalStmt = $this->db->query("SELECT COUNT(*) as total FROM notes");
        $total = $totalStmt->fetch();
        error_log("NoteController::index() - Total notes in database: " . $total['total']);

        echo json_encode([
            "success" => true,
            "data" => $notes,
            "debug" => [
                "user_id" => $userId,
                "notes_found" => count($notes),
                "total_notes" => $total['total']
            ]
        ]);
    }

    public function store() {
        // Get user ID from header
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized - No user ID found in header"
            ]);
            return;
        }

        $title = $_POST['title'] ?? NULL;
        $text = $_POST['text'] ?? NULL;
        $files = $_FILES;

        // Debug logging
        error_log("NoteController::store() - User ID: $userId");
        error_log("NoteController::store() - Title: $title");
        error_log("NoteController::store() - Text: " . substr($text, 0, 100));
        error_log("NoteController::store() - POST data: " . json_encode($_POST));
        error_log("NoteController::store() - FILES data: " . json_encode($_FILES));
        error_log("NoteController::store() - Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("NoteController::store() - Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
        error_log("NoteController::store() - Raw input length: " . strlen(file_get_contents('php://input')));

        if (!$title || !$text) {
            error_log("NoteController::store() - Missing title or text");
            echo json_encode(['success' => false, 'error' => 'Missing title or text']);
            return;
        }

        $imagePath = null;

        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $fileUpload = new FileUpload();
            $uploadResult = $fileUpload->uploadImage($files['image']);
            if (!$uploadResult['success']) {
                echo json_encode($uploadResult);
                return;
            }
            $imagePath = $uploadResult['file_path'];
        }

        $this->note->user_id = $userId;
        $this->note->title = $title;
        $this->note->original_text = $text;
        $this->note->image_path = $imagePath;

        $noteId = $this->note->create();

        if ($noteId) {
            echo json_encode([
                "success" => true,
                "message" => "Note saved successfully",
                "note_id" => $noteId
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Failed to save note"
            ]);
        }
    }

    public function show($id) {
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized - No user ID found"
            ]);
            return;
        }

        $query = "SELECT n.*, DATE_FORMAT(n.created_at, '%M %e, %Y at %l:%i %p') as last_edited
                  FROM notes n
                  WHERE n.id = :id AND n.user_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $note = $stmt->fetch();

        if ($note) {
            echo json_encode([
                "success" => true,
                "data" => $note
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "error" => "Note not found"
            ]);
        }
    }

    public function update($id) {
        $userId = $this->getUserIdFromHeader();

        if (!$userId) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized - No user ID found"
            ]);
            return;
        }

        $title = $_POST['title'] ?? NULL;
        $text = $_POST['text'] ?? NULL;
        $summary = $_POST['summary'] ?? NULL;
        $keywords = $_POST['keywords'] ?? NULL;

        if (!$title || !$text) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Missing title or text"
            ]);
            return;
        }

        // Check if note exists and belongs to user
        $checkQuery = "SELECT id FROM notes WHERE id = :id AND user_id = :user_id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->execute();

        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "error" => "Note not found"
            ]);
            return;
        }

        // Update the note
        $query = "UPDATE notes SET
                  title = :title,
                  original_text = :text,
                  summary = :summary,
                  keywords = :keywords,
                  updated_at = NOW()
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':text', $text);
        $stmt->bindParam(':summary', $summary);
        $stmt->bindParam(':keywords', $keywords);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Note updated successfully"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "Failed to update note"
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

        $query = "DELETE FROM notes WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Note deleted successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Failed to delete note"
            ]);
        }
    }

    private function getUserIdFromHeader() {
        $headers = getallheaders();

        // First try to validate token from Authorization header
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
                $userId = $this->validateToken($token);
                if ($userId) {
                    return $userId;
                }
            }
        }

        // Fallback to X-User-ID header (for backward compatibility)
        if (isset($headers['X-User-ID'])) {
            return intval($headers['X-User-ID']);
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
