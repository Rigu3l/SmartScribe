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
        error_log("=== QUIZ GENERATION START ===");
        $userId = $this->getUserIdFromHeader();
        error_log("User ID from header: " . ($userId ?: 'null'));

        if (!$userId) {
            error_log("QUIZ GENERATION: Unauthorized - no user ID");
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "error" => "Unauthorized"
            ]);
            return;
        }

        $rawInput = file_get_contents('php://input');
        error_log("Raw input length: " . strlen($rawInput));
        error_log("Raw input content: " . substr($rawInput, 0, 200) . "...");

        $data = json_decode($rawInput, true);
        error_log("JSON decode result: " . ($data ? 'success' : 'failed'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
        }

        if (!isset($data['text'])) {
            error_log("QUIZ GENERATION: Missing text parameter");
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing text']);
            return;
        }

        $difficulty = $data['difficulty'] ?? 'medium';
        $questionCount = $data['questionCount'] ?? 5;
        $noteTitle = $data['noteTitle'] ?? 'this study material';

        error_log("QUIZ GENERATION: Parameters - difficulty: $difficulty, questionCount: $questionCount, noteTitle: $noteTitle");
        error_log("QUIZ GENERATION: Text length: " . strlen($data['text']));

        $options = [
            'difficulty' => $difficulty,
            'questionCount' => $questionCount,
            'noteTitle' => $noteTitle
        ];

        error_log("QUIZ GENERATION: Calling GPT service...");
        $quiz = $this->gptService->generateQuiz($data['text'], $options);
        error_log("QUIZ GENERATION: GPT service returned: " . (is_array($quiz) ? 'array with ' . count($quiz) . ' keys' : gettype($quiz)));

        if (is_array($quiz) && isset($quiz['questions'])) {
            error_log("QUIZ GENERATION: Questions count: " . count($quiz['questions']));
        }

        // Always return quiz data - either AI-generated or fallback
        $response = [
            "success" => true,
            "data" => $quiz
        ];

        error_log("QUIZ GENERATION: Sending response with success=true");
        echo json_encode($response);
        error_log("=== QUIZ GENERATION END ===");
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