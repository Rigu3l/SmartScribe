<?php
require_once __DIR__ . '/../models/Summary.php';

class SummaryController {
    private $db;
    private $summary;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->summary = new Summary($db);
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

        $query = "SELECT s.*
                  FROM summaries s
                  INNER JOIN notes n ON s.note_id = n.id
                  WHERE n.user_id = :user_id
                  ORDER BY s.created_at DESC";

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

        if (!isset($data['note_id']) || !isset($data['content'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing note_id or content']);
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

        $this->summary->note_id = $data['note_id'];
        $this->summary->content = $data['content'];
        $this->summary->length = $data['length'] ?? 'medium';

        $summaryId = $this->summary->create();

        if ($summaryId) {
            echo json_encode([
                "success" => true,
                "message" => "Summary created successfully",
                "summary_id" => $summaryId
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Failed to create summary"
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

        $query = "SELECT s.*
                  FROM summaries s
                  INNER JOIN notes n ON s.note_id = n.id
                  WHERE s.id = :id AND n.user_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $summary = $stmt->fetch();

        if ($summary) {
            echo json_encode([
                "success" => true,
                "data" => $summary
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Summary not found']);
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