<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Note.php';
require_once __DIR__ . '/../helpers/FileUpload.php';
require_once __DIR__ . '/../services/AIKeywordService.php';

class NoteController extends BaseController {
    private $note;
    private $keywordService;

    public function __construct($db = null) {
        error_log("NoteController::__construct() - Starting construction");
        if ($db) {
            $this->db = $db;
            error_log("NoteController::__construct() - Database connection provided");
        }
        parent::__construct();
        error_log("NoteController::__construct() - Parent constructor called");

        try {
            $this->note = new Note($this->db);
            error_log("NoteController::__construct() - Note model instantiated");
        } catch (Exception $e) {
            error_log("NoteController::__construct() - Error instantiating Note model: " . $e->getMessage());
            throw $e;
        }

        try {
            $this->keywordService = new AIKeywordService();
            error_log("NoteController::__construct() - AIKeywordService instantiated");
        } catch (Exception $e) {
            error_log("NoteController::__construct() - Error instantiating AIKeywordService: " . $e->getMessage());
            throw $e;
        }

        error_log("NoteController::__construct() - Construction completed");
    }

    public function index() {
        try {
            error_log("NoteController::index() - ===== NOTES API REQUEST STARTED =====");
            error_log("NoteController::index() - Request method: " . $_SERVER['REQUEST_METHOD']);
            error_log("NoteController::index() - Request URI: " . $_SERVER['REQUEST_URI']);
            error_log("NoteController::index() - Query string: " . ($_SERVER['QUERY_STRING'] ?? 'none'));
            error_log("NoteController::index() - Headers: " . json_encode(getallheaders()));
            error_log("NoteController::index() - Starting authentication");

            // Authenticate user first
            if (!$this->authenticateUser()) {
                error_log("NoteController::index() - Authentication failed");
                $this->unauthorizedResponse();
                return;
            }

            // Get user ID from header
            $userId = $this->getUserId();
            error_log("NoteController::index() - User ID: " . ($userId ?: 'null'));

            if (!$userId) {
                error_log("NoteController::index() - No user ID found");
                $this->unauthorizedResponse();
                return;
            }

            $query = "SELECT n.*
                      FROM notes n
                      WHERE user_id = :user_id
                      ORDER BY created_at DESC";

            error_log("NoteController::index() - Executing query: " . $query);
            error_log("NoteController::index() - User ID for query: " . $userId);

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("NoteController::index() - Query executed, found " . count($notes) . " notes");
            error_log("NoteController::index() - About to call successResponse");

            $this->successResponse($notes, 'Notes retrieved successfully');
            error_log("NoteController::index() - successResponse called");

        } catch (Exception $e) {
            $this->errorResponse('Failed to retrieve notes: ' . $e->getMessage());
        }
    }

    public function store() {
        try {
            // Authenticate user first
            if (!$this->authenticateUser()) {
                $this->unauthorizedResponse();
                return;
            }

            // Get user ID from header
            $userId = $this->getUserId();

            if (!$userId) {
                $this->unauthorizedResponse();
                return;
            }

            $title = null;
            $text = null;

            // Check if this is a JSON request (text-only notes) or FormData (with possible image)
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                // Handle JSON data
                $jsonData = json_decode(file_get_contents('php://input'), true);
                if ($jsonData) {
                    $title = $jsonData['title'] ?? null;
                    $text = $jsonData['text'] ?? null;
                }
            } else {
                // Handle FormData
                $title = $_POST['title'] ?? null;
                $text = $_POST['text'] ?? null;
            }

            // Validate required fields
            if (!$title || !$text) {
                $this->badRequestResponse('Missing title or text');
                return;
            }

            $imagePath = null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileUpload = new FileUpload();
                $uploadResult = $fileUpload->uploadImage($_FILES['image']);
                if (!$uploadResult['success']) {
                    $this->errorResponse($uploadResult['error']);
                    return;
                }
                $imagePath = $uploadResult['file_path'];
            }

            $this->note->user_id = $userId;
            $this->note->title = $this->sanitizeInput($title);
            $this->note->original_text = $this->sanitizeInput($text);
            $this->note->image_path = $imagePath;

            // Debug logging
            error_log("NoteController::store() - About to create note");
            error_log("NoteController::store() - User ID: $userId");
            error_log("NoteController::store() - Title: " . $this->sanitizeInput($title));
            error_log("NoteController::store() - Text length: " . strlen($this->sanitizeInput($text)));
            error_log("NoteController::store() - Image path: " . ($imagePath ?: 'null'));

            $noteId = $this->note->createNote([
                'user_id' => $userId,
                'title' => $this->sanitizeInput($title),
                'original_text' => $this->sanitizeInput($text),
                'image_path' => $imagePath
            ]);

            error_log("NoteController::store() - Note creation result: " . ($noteId ? "Success (ID: $noteId)" : "Failed"));

            if ($noteId) {
                // Auto-extract keywords from the note content
                $keywords = $this->keywordService->extractKeywords($this->sanitizeInput($text), 5);
                $keywordsString = implode(',', $keywords);

                // Update the note with extracted keywords
                $updateQuery = "UPDATE notes SET keywords = :keywords WHERE id = :id AND user_id = :user_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':keywords', $keywordsString);
                $updateStmt->bindParam(':id', $noteId);
                $updateStmt->bindParam(':user_id', $userId);
                $updateStmt->execute();

                error_log("NoteController::store() - Keywords extracted and saved: " . $keywordsString);

                $this->successResponse([
                    'note_id' => $noteId,
                    'keywords' => $keywords
                ], 'Note saved successfully with auto-extracted keywords', 201);
            } else {
                $this->errorResponse('Failed to save note');
            }

        } catch (Exception $e) {
            $this->errorResponse('Failed to create note: ' . $e->getMessage());
        }
    }

    public function show($id) {
        // Authenticate user first
        if (!$this->authenticateUser()) {
            $this->unauthorizedResponse();
            return;
        }

        $userId = $this->getUserId();

        if (!$userId) {
            $this->unauthorizedResponse();
            return;
        }

        $query = "SELECT n.*,
                         DATE_FORMAT(n.created_at, '%M %e, %Y at %l:%i %p') as last_edited,
                         s.content as summary
                  FROM notes n
                  LEFT JOIN summaries s ON n.id = s.note_id
                  WHERE n.id = :id AND n.user_id = :user_id
                  ORDER BY s.created_at DESC
                  LIMIT 1";

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
        error_log("NoteController::update() - ===== UPDATE REQUEST STARTED =====");
        error_log("NoteController::update() - Note ID: $id");

        // Authenticate user first
        if (!$this->authenticateUser()) {
            error_log("NoteController::update() - Authentication failed");
            $this->unauthorizedResponse();
            return;
        }

        $userId = $this->getUserId();
        error_log("NoteController::update() - User ID: $userId");

        if (!$userId) {
            error_log("NoteController::update() - No user ID found");
            $this->unauthorizedResponse();
            return;
        }

        $title = $_POST['title'] ?? NULL;
        $text = $_POST['text'] ?? NULL;
        $summary = $_POST['summary'] ?? NULL;
        $keywords = $_POST['keywords'] ?? NULL;
        $isFavorite = isset($_POST['is_favorite']) ? (bool)$_POST['is_favorite'] : NULL;

        error_log("NoteController::update() - Received data:");
        error_log("NoteController::update() - Title: " . ($title ?: 'NULL'));
        error_log("NoteController::update() - Text: " . ($text ? substr($text, 0, 50) . '...' : 'NULL'));
        error_log("NoteController::update() - is_favorite: " . ($isFavorite !== NULL ? ($isFavorite ? 'true' : 'false') : 'NULL'));

        // For favorite updates, we only need the is_favorite field
        $isFavoriteOnly = ($title === NULL && $text === NULL && $isFavorite !== NULL);
        error_log("NoteController::update() - Is favorite-only update: " . ($isFavoriteOnly ? 'YES' : 'NO'));

        if (!$isFavoriteOnly && (!$title || !$text)) {
            error_log("NoteController::update() - Validation failed: missing required fields");
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

        // Build dynamic UPDATE query based on provided fields
        $updateFields = [];
        $params = [];

        if ($title !== NULL) {
            $updateFields[] = "title = :title";
            $params[':title'] = $title;
        }

        if ($text !== NULL) {
            $updateFields[] = "original_text = :text";
            $params[':text'] = $text;
        }

        if ($isFavorite !== NULL) {
            $updateFields[] = "is_favorite = :is_favorite";
            $params[':is_favorite'] = $isFavorite;
        }

        // Always update the updated_at timestamp
        $updateFields[] = "updated_at = NOW()";

        $query = "UPDATE notes SET " . implode(', ', $updateFields) . " WHERE id = :id AND user_id = :user_id";
        error_log("NoteController::update() - Generated query: $query");
        error_log("NoteController::update() - Query parameters: " . json_encode($params));

        $stmt = $this->db->prepare($query);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);

        if ($stmt->execute()) {
            error_log("NoteController::update() - Database update successful");
            // If summary or keywords were provided, we could store them separately
            // For now, we'll just log them since the table doesn't support them
            if ($summary) {
                error_log("Note update: Summary provided but not stored (table doesn't have summary column): " . substr($summary, 0, 100));
            }
            if ($keywords) {
                error_log("Note update: Keywords provided but not stored (table doesn't have keywords column): " . $keywords);
            }

            echo json_encode([
                "success" => true,
                "message" => "Note updated successfully"
            ]);
        } else {
            error_log("NoteController::update() - Database update failed");
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "Failed to update note"
            ]);
        }
    }

    public function destroy($id) {
        // Authenticate user first
        if (!$this->authenticateUser()) {
            $this->unauthorizedResponse();
            return;
        }

        $userId = $this->getUserId();

        if (!$userId) {
            $this->unauthorizedResponse();
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


}
?>
