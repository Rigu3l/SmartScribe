<?php
require_once __DIR__ . '/../services/GPTServices.php';
require_once __DIR__ . '/../models/Note.php';

class GPTController {
    private $db;
    private $gptService;
    private $note;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->gptService = new GPTService();
        $this->note = new Note($db);
    }

    public function generateSummary() {
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

        if (!isset($data['text'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing text']);
            return;
        }

        $length = $data['length'] ?? 'medium';
        $summary = $this->gptService->generateSummary($data['text'], $length);

        // Always return a summary - either AI-generated or fallback
        if ($summary) {
            echo json_encode([
                "success" => true,
                "data" => $summary
            ]);
        } else {
            // Use fallback summary if AI generation fails
            $wordCount = str_word_count($data['text']);
            $sentences = preg_split('/[.!?]+/', $data['text'], -1, PREG_SPLIT_NO_EMPTY);
            $firstSentence = trim($sentences[0] ?? '');

            switch ($length) {
                case 'short':
                    $fallbackSummary = "Summary: " . substr($firstSentence, 0, 100) . "... (" . $wordCount . " words)";
                    break;
                case 'medium':
                    $secondSentence = isset($sentences[1]) ? " " . trim($sentences[1]) : "";
                    $fallbackSummary = "Summary: " . $firstSentence . $secondSentence . " (Total: " . $wordCount . " words)";
                    break;
                case 'long':
                    $summaryText = implode(" ", array_slice($sentences, 0, 3));
                    $fallbackSummary = "Detailed Summary: " . $summaryText . "... (Total: " . $wordCount . " words, " . count($sentences) . " sentences)";
                    break;
                default:
                    $fallbackSummary = "Generated summary for " . $wordCount . " words of content.";
            }

            echo json_encode([
                "success" => true,
                "data" => $fallbackSummary
            ]);
        }
    }

    public function generateQuiz() {
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

        if (!isset($data['text'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing text']);
            return;
        }

        $difficulty = $data['difficulty'] ?? 'medium';
        $questionCount = $data['questionCount'] ?? 5;
        $quiz = $this->gptService->generateQuiz($data['text'], $difficulty, $questionCount);

        // Always return quiz data - either AI-generated or fallback
        echo json_encode([
            "success" => true,
            "data" => $quiz
        ]);
    }

    public function extractKeywords() {
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

        if (!isset($data['text'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing text']);
            return;
        }

        $count = $data['count'] ?? 5;
        $keywords = $this->gptService->extractKeywords($data['text'], $count);

        // Always return keywords - either AI-generated or fallback
        echo json_encode([
            "success" => true,
            "data" => $keywords
        ]);
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