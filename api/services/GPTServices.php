<?php
// api/services/GPTServices.php

class GPTService {
    private $geminiApiKey;
    private $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct() {
        $this->geminiApiKey = getenv('GOOGLE_GEMINI_API_KEY');
        if (!$this->geminiApiKey || $this->geminiApiKey === 'your_google_gemini_api_key_here') {
            error_log("Google Gemini API key not found or is placeholder in environment variables");
            $this->geminiApiKey = null;
        }
    }

    public function generateSummary($text, $length = 'auto') {
        if (empty($text)) {
            return null;
        }

        // Auto-detect appropriate length based on input text
        if ($length === 'auto') {
            $wordCount = str_word_count($text);
            $sentenceCount = preg_match_all('/[.!?]+/', $text, $matches);

            if ($wordCount < 50 || $sentenceCount < 3) {
                $length = 'short';
            } elseif ($wordCount < 500 || $sentenceCount < 10) {
                $length = 'medium';
            } else {
                $length = 'long';
            }

            error_log("Auto-detected summary length: {$length} (words: {$wordCount}, sentences: {$sentenceCount})");
        }

        if (!$this->geminiApiKey) {
            return $this->generateFallbackSummary($text, $length);
        }

        $prompt = $this->buildSummaryPrompt($text, $length);
        return $this->callGemini($prompt);
    }

    public function generateQuiz($text, $difficulty = 'medium', $count = 5) {
        if (empty($text)) {
            return ['quiz' => 'No content provided', 'questions' => []];
        }

        if (!$this->geminiApiKey) {
            return $this->generateFallbackQuiz($text, $difficulty, $count);
        }

        $prompt = $this->buildQuizPrompt($text, $difficulty, $count);
        $response = $this->callGemini($prompt);

        if (!$response) {
            return $this->generateFallbackQuiz($text, $difficulty, $count);
        }

        return $this->parseQuizResponse($response);
    }

    public function extractKeywords($text, $count = 5) {
        if (empty($text)) {
            return [];
        }

        if (!$this->geminiApiKey) {
            return $this->extractKeywordsFallback($text, $count);
        }

        $prompt = "Extract {$count} most important keywords from the following text. Return only the keywords separated by commas, no other text:\n\n{$text}";
        $response = $this->callGemini($prompt);

        if (!$response) {
            return $this->extractKeywordsFallback($text, $count);
        }

        return array_map('trim', explode(',', $response));
    }

    private function callGemini($prompt) {
        $url = $this->geminiUrl . '?key=' . $this->geminiApiKey;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 1000
            ]
        ];

        $headers = [
            'Content-Type: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("Gemini API cURL Error: " . $err);
            return null;
        }

        if ($httpCode !== 200) {
            error_log("Gemini API HTTP Error: " . $httpCode . " Response: " . $response);
            return null;
        }

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Gemini API JSON Decode Error: " . json_last_error_msg());
            return null;
        }

        if (isset($responseData['error'])) {
            error_log("Gemini API Error: " . $responseData['error']['message']);
            return null;
        }

        // Extract text from Gemini response
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($responseData['candidates'][0]['content']['parts'][0]['text']);
        }

        error_log("Unexpected Gemini API Response Format: " . json_encode($responseData));
        return null;
    }

    private function buildSummaryPrompt($text, $length) {
        $lengthInstructions = [
            'short' => 'Create a brief 2-3 sentence summary that captures the main idea.',
            'medium' => 'Create a concise summary of 4-6 sentences covering the key points.',
            'long' => 'Create a detailed summary covering all main points, key details, and important context.'
        ];

        $instruction = $lengthInstructions[$length] ?? $lengthInstructions['medium'];

        return "{$instruction} Here is the text to summarize:\n\n{$text}\n\nPlease provide only the summary, no additional commentary.";
    }

    private function buildQuizPrompt($text, $difficulty, $count) {
        $difficultyLevels = [
            'easy' => 'simple questions with straightforward answers from the text',
            'medium' => 'moderately challenging questions requiring understanding of the content',
            'hard' => 'complex questions requiring analysis and critical thinking about the material'
        ];

        $level = $difficultyLevels[$difficulty] ?? $difficultyLevels['medium'];

        return "Create a quiz with {$count} multiple-choice questions based on the following text. Each question should have 4 options (A, B, C, D) with one correct answer. Make the questions {$level}.

Format your response as JSON with this exact structure:
{
  \"questions\": [
    {
      \"question\": \"Question text here?\",
      \"options\": [\"A) Option 1\", \"B) Option 2\", \"C) Option 3\", \"D) Option 4\"],
      \"correct_answer\": \"A\"
    }
  ]
}

Text to create quiz from:
{$text}

Return only the JSON, no other text.";
    }

    private function parseQuizResponse($response) {
        // Clean the response by removing markdown code blocks if present
        $cleanResponse = preg_replace('/```json\s*|\s*```/', '', $response);

        // Try to parse JSON response
        $json = json_decode($cleanResponse, true);
        if ($json && isset($json['questions'])) {
            return $json;
        }

        // Try to extract JSON from the response if it's embedded in text
        if (preg_match('/\{.*\}/s', $cleanResponse, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json && isset($json['questions'])) {
                return $json;
            }
        }

        // Fallback if JSON parsing fails
        return $this->generateFallbackQuiz("", "medium", 5);
    }

    private function generateFallbackSummary($text, $length) {
        $wordCount = str_word_count($text);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $firstSentence = trim($sentences[0] ?? '');

        // If auto-detection was used, apply the same logic here
        if ($length === 'auto') {
            if ($wordCount < 50 || count($sentences) < 3) {
                $length = 'short';
            } elseif ($wordCount < 500 || count($sentences) < 10) {
                $length = 'medium';
            } else {
                $length = 'long';
            }
        }

        switch ($length) {
            case 'short':
                return "Summary: " . substr($firstSentence, 0, 100) . "... (" . $wordCount . " words total)";
            case 'medium':
                $summary = $firstSentence;
                if (isset($sentences[1])) {
                    $summary .= " " . trim($sentences[1]);
                }
                return "Summary: " . $summary . " (Total: " . $wordCount . " words)";
            case 'long':
                $summary = implode(" ", array_slice($sentences, 0, 3));
                return "Detailed Summary: " . $summary . "... (Total: " . $wordCount . " words, " . count($sentences) . " sentences)";
        }

        return "Generated summary for " . $wordCount . " words of content.";
    }

    private function generateFallbackQuiz($text, $difficulty, $count) {
        return [
            'quiz' => 'Quiz generation temporarily unavailable - using fallback mode',
            'questions' => array_fill(0, $count, [
                'question' => 'Sample question about the content',
                'options' => ['A) Option A', 'B) Option B', 'C) Option C', 'D) Option D'],
                'correct_answer' => 'A'
            ])
        ];
    }

    private function extractKeywordsFallback($text, $count) {
        $words = str_word_count(strtolower($text), 1);
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those'];

        $filteredWords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });

        $wordCount = array_count_values($filteredWords);
        arsort($wordCount);
        return array_slice(array_keys($wordCount), 0, $count);
    }
}