<?php
// api/services/AIQuizService.php
require_once __DIR__ . '/GeminiAPIClient.php';

class AIQuizService {
    private $apiClient;

    public function __construct() {
        $this->apiClient = new GeminiAPIClient();
    }

    /**
     * Generate quiz from text content
     */
    public function generateQuiz($text, $options = []) {
        if (empty($text)) {
            return ['quiz' => 'No content provided', 'questions' => []];
        }

        // Extract options with defaults
        $difficulty = $options['difficulty'] ?? 'medium';
        $count = $options['questionCount'] ?? 5;
        $noteTitle = $options['noteTitle'] ?? 'this study material';

        if (!$this->apiClient->isAvailable()) {
            return $this->generateFallbackQuiz($text, $difficulty, $count, $noteTitle);
        }

        $prompt = $this->buildQuizPrompt($text, $difficulty, $count, $noteTitle);
        $response = $this->apiClient->call($prompt, ['temperature' => 0.2, 'maxTokens' => 1500]);

        if (!$response) {
            return $this->generateFallbackQuiz($text, $difficulty, $count, $noteTitle);
        }

        return $this->parseQuizResponse($response);
    }

    /**
     * Build quiz prompt for AI
     */
    private function buildQuizPrompt($text, $difficulty, $count, $noteTitle) {
        $difficultyLevels = [
            'easy' => 'simple recall questions testing basic facts and direct information',
            'medium' => 'questions requiring comprehension and understanding of key concepts and relationships',
            'hard' => 'analytical questions requiring critical thinking, inference, and application of concepts'
        ];

        $level = $difficultyLevels[$difficulty] ?? $difficultyLevels['medium'];

        return "You are an expert educator creating a comprehensive exam-style quiz based on the study material. Create {$count} high-quality multiple-choice questions that test deep understanding and critical thinking about the subject matter.

CONTENT TO CREATE QUIZ FROM:
{$text}

QUIZ REQUIREMENTS:
1. Create questions in a traditional exam format - DO NOT reference the note or source material
2. Questions should be about the subject matter itself, not about the content source
3. Questions should test {$level} with varying complexity
4. Each question must be based on specific concepts, facts, or information from the content
5. Create sophisticated distractors (wrong answers) that represent common misconceptions or partial understanding
6. Questions should assess actual knowledge, understanding, application, and analysis

QUESTION TYPES TO INCLUDE (mix these throughout):
- Definition and Concept Identification: \"What is the primary function of...\"
- Relationship Analysis: \"How does [concept A] interact with [concept B]?\"
- Application Scenarios: \"In which situation would you apply...\"
- Comparative Analysis: \"Which of the following is most similar to...\"
- Process Understanding: \"What is the correct sequence of...\"
- Critical Evaluation: \"Which statement best represents...\"
- Problem-Solving: \"What would be the most effective approach to...\"
- Cause and Effect: \"What is the most likely outcome of...\"

ENHANCED ANSWER CHOICE REQUIREMENTS:
- All options should be realistic and educational
- Wrong answers should represent common mistakes or partial understanding
- Include options that show different levels of understanding
- Make distractors challenging but fair
- Ensure correct answer is clearly the best choice

EXAMPLE QUESTION FORMATS (exam-style):
- \"What is the primary function of [key concept] in this context?\"
- \"Which of the following best describes the relationship between [concept A] and [concept B]?\"
- \"In which scenario would [approach] be most appropriate?\"
- \"What is the most significant advantage of [method]?\"
- \"Which statement most accurately represents [principle]?\"
- \"What would be the most effective strategy for [situation]?\"
- \"How does [concept] contribute to [larger goal]?\"
- \"Which of the following is NOT a characteristic of [topic]?\"
- \"What is the correct sequence for implementing [concept]?\"
- \"How does [concept] interact with other key concepts in this domain?\"

DIFFICULTY VARIATION:
- Include a mix of straightforward and challenging questions
- Some questions should require synthesis of multiple concepts
- Include questions that test understanding of nuances and exceptions

FORMAT REQUIREMENTS:
Return ONLY valid JSON with this exact structure:
{
  \"questions\": [
    {
      \"question\": \"What is the primary function of [key concept] in this context?\",
      \"options\": [
        \"A) Correct and complete answer\",
        \"B) Partially correct but incomplete\",
        \"C) Common misconception about the concept\",
        \"D) Completely incorrect or unrelated\"
      ],
      \"correct_answer\": \"A\"
    }
  ]
}

CRITICAL REQUIREMENTS:
- Questions must be in EXAM FORMAT - no references to \"this note\" or \"according to the content\"
- Focus on testing knowledge of the SUBJECT MATTER, not the source material
- Wrong answers should be sophisticated distractors representing real misconceptions
- Questions should assess genuine understanding, application, and critical thinking
- Each question should test specific knowledge from the content
- Answer choices should be educational and reveal different levels of understanding

Return only the JSON, no additional text or explanations.";
    }

    /**
     * Parse quiz response from AI
     */
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
        return $this->generateFallbackQuiz("", "medium", 5, "this study material");
    }

    /**
     * Generate fallback quiz when API is unavailable
     */
    private function generateFallbackQuiz($text, $difficulty, $count, $noteTitle) {
        // Extract key information from the text for exam-style questions
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = str_word_count(strtolower($text), 1);

        // Get some key terms from the text
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those'];
        $keyTerms = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });
        $keyTerms = array_unique($keyTerms);
        $keyTerms = array_slice($keyTerms, 0, min(10, count($keyTerms)));

        $questions = [];

        // Generate enhanced exam-style questions based on available content
        for ($i = 0; $i < $count; $i++) {
            if (count($sentences) > 0 && count($keyTerms) > 0) {
                $keyTerm = $keyTerms[$i % count($keyTerms)];

                // Enhanced question types with better variety
                $questionTypes = [
                    "What is the primary function of {$keyTerm} in this context?",
                    "Which of the following best describes the relationship between {$keyTerm} and the main topic?",
                    "In which scenario would {$keyTerm} be most appropriately applied?",
                    "What is the most significant advantage of using {$keyTerm}?",
                    "Which statement most accurately represents the concept of {$keyTerm}?",
                    "What would be the most effective approach when working with {$keyTerm}?",
                    "How does {$keyTerm} contribute to achieving the overall objectives?",
                    "Which of the following is NOT a characteristic of {$keyTerm}?",
                    "What is the correct sequence for implementing {$keyTerm}?",
                    "How does {$keyTerm} interact with other key concepts in this domain?"
                ];

                $question = $questionTypes[$i % count($questionTypes)];

                // Enhanced answer choices with realistic distractors
                $answerSets = [
                    [
                        "{$keyTerm} serves as a fundamental building block with comprehensive functionality",
                        "{$keyTerm} provides basic utility but lacks advanced features",
                        "{$keyTerm} is primarily decorative with limited practical application",
                        "{$keyTerm} functions independently without integration capabilities"
                    ],
                    [
                        "{$keyTerm} establishes direct connections and influences outcomes significantly",
                        "{$keyTerm} has minimal impact and operates in isolation",
                        "{$keyTerm} creates barriers rather than facilitating relationships",
                        "{$keyTerm} is completely unrelated to the core subject matter"
                    ],
                    [
                        "When complex problem-solving requires systematic analysis and {$keyTerm}",
                        "In situations demanding immediate action without planning",
                        "Chen working with unrelated concepts that don't involve {$keyTerm}",
                        "During routine tasks that don't benefit from {$keyTerm} implementation"
                    ],
                    [
                        "Provides comprehensive control and flexibility in implementation",
                        "Offers basic functionality with significant limitations",
                        "Creates unnecessary complexity without added benefits",
                        "Functions adequately but requires extensive workarounds"
                    ]
                ];

                $options = $answerSets[$i % count($answerSets)];
            } else {
                // Enhanced generic fallback with better questions
                $examQuestions = [
                    "What is the primary function of the main concept discussed?",
                    "Which of the following best describes the relationship between key ideas?",
                    "In which scenario would the discussed approach be most effective?",
                    "What is the most significant advantage of the presented methodology?",
                    "Which statement most accurately represents the core principle?",
                    "What would be the most effective strategy for implementation?",
                    "How do the concepts contribute to achieving the stated objectives?",
                    "Which of the following is NOT a characteristic of the main topic?",
                    "What is the correct sequence for applying the discussed principles?",
                    "How do the key concepts interact within this domain?"
                ];

                $question = $examQuestions[$i % count($examQuestions)];

                // Enhanced generic answer choices
                $genericAnswerSets = [
                    [
                        "A) Serves as a fundamental framework guiding the entire approach",
                        "B) Provides basic structure with limited scope and application",
                        "C) Functions as supplementary support rather than core functionality",
                        "D) Operates independently without connection to other elements"
                    ],
                    [
                        "A) They work synergistically to create comprehensive understanding",
                        "B) They function independently with minimal interaction",
                        "C) They compete with each other for dominance in the system",
                        "D) They are completely unrelated and serve different purposes"
                    ],
                    [
                        "A) When dealing with complex, multi-faceted challenges requiring systematic analysis",
                        "B) In straightforward situations needing immediate, simple solutions",
                        "C) When working with unrelated concepts that don't benefit from this approach",
                        "D) During routine operations that don't require specialized methods"
                    ],
                    [
                        "A) Enables comprehensive control and adaptability across different scenarios",
                        "B) Provides basic functionality with significant constraints and limitations",
                        "C) Introduces unnecessary complexity without proportional benefits",
                        "D) Functions adequately but requires extensive additional resources"
                    ]
                ];

                $options = $genericAnswerSets[$i % count($genericAnswerSets)];
            }

            $questions[] = [
                'question' => $question,
                'options' => $options,
                'correct_answer' => 'A'
            ];
        }

        return [
            'quiz' => 'AI quiz generation unavailable - using exam-style fallback questions',
            'questions' => $questions
        ];
    }
}
?>