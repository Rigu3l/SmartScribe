<?php
require_once __DIR__ . '/../models/Summary.php';
require_once __DIR__ . '/../models/Note.php';
require_once __DIR__ . '/../services/GPTServices.php';

class SummaryController {
    private $db;
    private $summary;
    private $note;
    private $gptService;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->summary = new Summary($db);
        $this->note = new Note($db);
        $this->gptService = new GPTService();
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

        if (!isset($data['note_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing note_id']);
            return;
        }

        // Verify the note belongs to the authenticated user
        $note = $this->note->readById($data['note_id'], $userId);
        if (!$note) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied or note not found']);
            return;
        }

        $content = $data['content'] ?? null;
        $length = $data['length'] ?? 'medium';

        // If content is not provided, generate summary using AI
        if (!$content) {
            $generatedSummary = $this->gptService->generateSummary($note['original_text'], $length);

            // If AI generation fails, use fallback summary
            if (!$generatedSummary) {
                $wordCount = str_word_count($note['original_text']);
                $sentences = preg_split('/[.!?]+/', $note['original_text'], -1, PREG_SPLIT_NO_EMPTY);
                $firstSentence = trim($sentences[0] ?? '');

                switch ($length) {
                    case 'short':
                        $content = "Summary: " . substr($firstSentence, 0, 100) . "... (" . $wordCount . " words)";
                        break;
                    case 'medium':
                        $secondSentence = isset($sentences[1]) ? " " . trim($sentences[1]) : "";
                        $content = "Summary: " . $firstSentence . $secondSentence . " (Total: " . $wordCount . " words)";
                        break;
                    case 'long':
                        $summaryText = implode(" ", array_slice($sentences, 0, 3));
                        $content = "Detailed Summary: " . $summaryText . "... (Total: " . $wordCount . " words, " . count($sentences) . " sentences)";
                        break;
                    default:
                        $content = "Generated summary for " . $wordCount . " words of content.";
                }
            } else {
                $content = $generatedSummary;
            }
        }

        $this->summary->note_id = $data['note_id'];
        $this->summary->content = $content;
        $this->summary->length = $length;

        error_log("SummaryController::store() - Creating summary for note_id: {$data['note_id']}, content length: " . strlen($content));

        $summaryId = $this->summary->create();

        if ($summaryId) {
            error_log("SummaryController::store() - Summary created successfully with ID: $summaryId");

            echo json_encode([
                "success" => true,
                "message" => "Summary created successfully",
                "summary_id" => $summaryId,
                "generated" => !isset($data['content']), // Indicate if it was AI-generated
                "content_preview" => substr($content, 0, 100) // Add preview for debugging
            ]);
        } else {
            error_log("SummaryController::store() - Failed to create summary in database");
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