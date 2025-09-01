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

    public function generateQuiz($text, $options = []) {
        if (empty($text)) {
            return ['quiz' => 'No content provided', 'questions' => []];
        }

        // Extract options with defaults
        $difficulty = $options['difficulty'] ?? 'medium';
        $count = $options['questionCount'] ?? 5;
        $noteTitle = $options['noteTitle'] ?? 'this study material';

        if (!$this->geminiApiKey) {
            return $this->generateFallbackQuiz($text, $difficulty, $count, $noteTitle);
        }

        $prompt = $this->buildQuizPrompt($text, $difficulty, $count, $noteTitle);
        $response = $this->callGemini($prompt);

        if (!$response) {
            return $this->generateFallbackQuiz($text, $difficulty, $count, $noteTitle);
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

    private function analyzeContentStructure($text) {
        $wordCount = str_word_count($text);
        $sentenceCount = preg_match_all('/[.!?]+/', $text, $matches);
        $paragraphCount = preg_match_all('/\n\s*\n/', $text, $matches);

        // Detect content type patterns
        $contentType = 'general';
        $structureHints = [];

        // Check for different content types
        if (preg_match('/^(step|phase|stage)/im', $text)) {
            $contentType = 'process/methodology';
            $structureHints[] = 'Contains sequential steps or phases';
        }

        if (preg_match('/(definition|defined as|refers to)/i', $text)) {
            $contentType = 'conceptual/theoretical';
            $structureHints[] = 'Contains definitions and theoretical concepts';
        }

        if (preg_match('/(compare|contrast|versus|vs\.|difference|similar)/i', $text)) {
            $contentType = 'comparative';
            $structureHints[] = 'Contains comparisons and contrasts';
        }

        if (preg_match('/(example|case|scenario|situation)/i', $text)) {
            $contentType = 'practical/application';
            $structureHints[] = 'Contains practical examples and applications';
        }

        if (preg_match('/(problem|solution|challenge|issue)/i', $text)) {
            $contentType = 'problem-solution';
            $structureHints[] = 'Contains problem-solution analysis';
        }

        // Analyze structure
        $structure = [];
        if ($paragraphCount > 3) {
            $structure[] = 'Well-structured with multiple paragraphs';
        }

        if (preg_match_all('/(\d+\.|\•|\-)/', $text) > 3) {
            $structure[] = 'Contains lists or numbered items';
        }

        if (preg_match('/(important|key|critical|essential|note|remember)/i', $text)) {
            $structure[] = 'Contains emphasized key points';
        }

        if (preg_match('/(therefore|however|consequently|thus|hence|furthermore)/i', $text)) {
            $structure[] = 'Contains logical connectors and transitions';
        }

        // Extract key indicators
        $technicalTerms = [];
        $actionWords = [];

        // Look for technical/academic terms
        if (preg_match_all('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\b/', $text, $matches)) {
            $technicalTerms = array_slice($matches[0], 0, 5);
        }

        // Look for action/process words
        $actionPatterns = ['implement', 'apply', 'create', 'develop', 'analyze', 'evaluate', 'design', 'build', 'manage', 'process', 'system', 'method', 'approach', 'strategy', 'technique'];
        foreach ($actionPatterns as $pattern) {
            if (stripos($text, $pattern) !== false) {
                $actionWords[] = $pattern;
            }
        }

        $analysis = "CONTENT TYPE: {$contentType}\n";
        $analysis .= "WORD COUNT: {$wordCount}, SENTENCES: {$sentenceCount}, PARAGRAPHS: " . ($paragraphCount + 1) . "\n";

        if (!empty($structureHints)) {
            $analysis .= "CONTENT HINTS: " . implode(', ', $structureHints) . "\n";
        }

        if (!empty($structure)) {
            $analysis .= "STRUCTURE: " . implode(', ', $structure) . "\n";
        }

        if (!empty($technicalTerms)) {
            $analysis .= "KEY TERMS: " . implode(', ', $technicalTerms) . "\n";
        }

        if (!empty($actionWords)) {
            $analysis .= "ACTION CONCEPTS: " . implode(', ', array_slice($actionWords, 0, 5)) . "\n";
        }

        return $analysis;
    }

    private function callGemini($prompt) {
        $url = $this->geminiUrl . '?key=' . $this->geminiApiKey;

        // Adjust parameters based on task type
        $isSummary = strpos($prompt, 'summarize') !== false || strpos($prompt, 'SUMMARY') !== false;
        $isQuiz = strpos($prompt, 'quiz') !== false || strpos($prompt, 'QUIZ') !== false;

        $temperature = $isSummary ? 0.1 : ($isQuiz ? 0.2 : 0.3); // Lower temperature for summaries for more accuracy
        $maxTokens = $isSummary ? 1500 : ($isQuiz ? 2000 : 1000); // More tokens for complex tasks

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxTokens,
                'topP' => 0.9,
                'topK' => 40
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
        // Analyze content type and structure
        $contentAnalysis = $this->analyzeContentStructure($text);

        $lengthInstructions = [
            'short' => 'Create a concise 2-3 sentence summary that captures the absolute core essence, main takeaway, and most critical insight.',
            'medium' => 'Create a well-structured summary of 4-6 sentences covering key concepts, their interrelationships, practical applications, and essential insights.',
            'long' => 'Create a comprehensive, academically-structured summary with clear sections: core concepts, key relationships, practical applications, critical insights, and broader implications.'
        ];

        $instruction = $lengthInstructions[$length] ?? $lengthInstructions['medium'];

        return "You are an expert educator and content analyst. Your task is to create a comprehensive, well-structured summary that captures the complete essence of the provided content.

{$instruction}

CONTENT ANALYSIS:
{$contentAnalysis}

CONTENT TO SUMMARIZE:
{$text}

ENHANCED SUMMARY REQUIREMENTS:
1. STRUCTURE: Begin with the core concept/main idea, then systematically cover supporting elements
2. COMPREHENSIVENESS: Include ALL major concepts, their relationships, and interconnections
3. PRACTICAL VALUE: Highlight real-world applications, implications, and practical insights
4. TECHNICAL ACCURACY: Maintain precise terminology while ensuring clarity
5. LOGICAL FLOW: Create natural progression from basic concepts to advanced applications
6. CRITICAL INSIGHTS: Include important distinctions, nuances, exceptions, and limitations
7. EDUCATIONAL VALUE: Focus on insights that enhance understanding and application
8. SYNTHESIS: Show how different concepts work together as a cohesive system

CONTENT-SPECIFIC GUIDELINES:
- If this is a process/methodology: Explain the steps, rationale, and expected outcomes
- If this is a concept/theory: Cover definition, components, applications, and limitations
- If this is a comparison/analysis: Highlight similarities, differences, and implications
- If this is a problem/solution: Explain the problem, solution approach, and benefits
- If this is a case study/example: Extract generalizable principles and lessons learned

SUMMARY QUALITY STANDARDS:
- Start with the most fundamental concept or main idea
- Explain how components interact and support each other
- Include specific examples or applications where mentioned
- Highlight any critical distinctions or important nuances
- Connect ideas to show the bigger picture and broader implications
- End with key takeaways that reinforce the main value proposition

FORMATTING REQUIREMENTS:
- Use clear, natural language that flows conversationally
- Maintain academic rigor while ensuring accessibility
- Create logical paragraph breaks for better readability
- Ensure each sentence adds unique value to the summary
- Avoid redundancy while being comprehensive

IMPORTANT: Provide ONLY the summary text itself. Do NOT include any labels, prefixes, or titles like 'Summary:', 'Detailed Summary:', or similar. Start directly with the summary content and maintain a natural, flowing narrative that reads like a well-written article introduction.";
    }

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
- \"What is the primary function of [concept] in this context?\"
- \"Which of the following best describes the relationship between [concept A] and [concept B]?\"
- \"In which scenario would [approach] be most appropriate?\"
- \"What is the most significant advantage of [method]?\"
- \"Which statement most accurately represents [principle]?\"
- \"What would be the most effective strategy for [situation]?\"
- \"How does [concept] contribute to [larger goal]?\"
- \"Which of the following is NOT a characteristic of [topic]?\"

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

    private function generateFallbackSummary($text, $length) {
        // Enhanced content analysis
        $wordCount = str_word_count($text);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $paragraphs = preg_split('/\n\s*\n/', $text);

        // Extract first meaningful sentence
        $firstSentence = '';
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) > 20) {
                $firstSentence = $sentence;
                break;
            }
        }

        // Advanced keyword extraction with context
        $words = str_word_count(strtolower($text), 1);
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those', 'also', 'then', 'here', 'there', 'when', 'where', 'how', 'why', 'what', 'which', 'who', 'all', 'some', 'many', 'much', 'few', 'little'];

        // Extract meaningful terms with better filtering
        $keyTerms = [];
        foreach ($words as $word) {
            if (strlen($word) > 3 && !in_array($word, $stopWords) && ctype_alpha($word)) {
                $keyTerms[] = $word;
            }
        }

        // Get term frequency for better ranking
        $termFrequency = array_count_values($keyTerms);
        arsort($termFrequency);
        $keyTerms = array_keys($termFrequency);
        $keyTerms = array_slice($keyTerms, 0, min(10, count($keyTerms)));

        // Detect content type and structure
        $contentType = $this->detectContentType($text);
        $structureElements = $this->analyzeStructureElements($text);

        // Auto-detect length if needed
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
                $summary = $firstSentence ?: "This content explores key concepts and their applications.";
                if (!empty($keyTerms)) {
                    $mainConcept = $keyTerms[0];
                    $summary .= " The material centers on {$mainConcept} and its fundamental principles.";
                }
                return $summary;

            case 'medium':
                $summary = $firstSentence ?: "This educational content covers important concepts and their relationships.";

                // Add second sentence if available
                if (isset($sentences[1]) && trim($sentences[1]) !== $firstSentence) {
                    $secondSentence = trim($sentences[1]);
                    if (strlen($secondSentence) > 15) {
                        $summary .= " " . $secondSentence;
                    }
                }

                // Add key concepts and relationships
                if (count($keyTerms) >= 2) {
                    $primaryTerms = array_slice($keyTerms, 0, min(3, count($keyTerms)));
                    $summary .= " The discussion examines the connections between " . implode(" and ", array_slice($primaryTerms, 0, 2));
                    if (count($primaryTerms) > 2) {
                        $summary .= ", including " . $primaryTerms[2];
                    }
                    $summary .= ".";
                }

                // Add content type insights
                if ($contentType !== 'general') {
                    $summary .= " This {$contentType} content provides structured insights into the topic.";
                }

                return $summary;

            case 'long':
                $summary = $firstSentence ?: "This comprehensive content provides detailed insights into key concepts and their applications.";

                // Add multiple sentences for depth
                $addedSentences = 0;
                for ($i = 1; $i < count($sentences) && $addedSentences < 2; $i++) {
                    $sentence = trim($sentences[$i]);
                    if (strlen($sentence) > 20 && $sentence !== $firstSentence) {
                        $summary .= " " . $sentence;
                        $addedSentences++;
                    }
                }

                // Add comprehensive key concepts
                if (!empty($keyTerms)) {
                    $summary .= " Key concepts explored include " . implode(", ", array_slice($keyTerms, 0, min(5, count($keyTerms))));
                    if (count($keyTerms) > 5) {
                        $remaining = count($keyTerms) - 5;
                        $summary .= ", and " . min($remaining, 3) . " additional " . ($remaining === 1 ? "concept" : "concepts");
                    }
                    $summary .= ".";
                }

                // Add structural insights
                if (!empty($structureElements)) {
                    $summary .= " The content is structured around " . implode(" and ", array_slice($structureElements, 0, 2));
                    if (count($structureElements) > 2) {
                        $summary .= ", providing a comprehensive framework for understanding";
                    }
                    $summary .= ".";
                }

                // Add practical applications if detected
                if ($this->hasPracticalContent($text)) {
                    $summary .= " Practical applications and implementation considerations are emphasized throughout the material.";
                }

                return $summary;
        }

        // Fallback for any edge cases
        return "This educational content covers " . implode(", ", array_slice($keyTerms, 0, 3)) . " and related concepts, offering structured insights into their relationships and practical applications.";
    }

    private function detectContentType($text) {
        $textLower = strtolower($text);

        if (preg_match('/(step|phase|stage|process|method|procedure)/i', $text)) {
            return 'process-oriented';
        }

        if (preg_match('/(theory|concept|principle|framework|model)/i', $text)) {
            return 'theoretical';
        }

        if (preg_match('/(compare|contrast|versus|vs\.|analysis|difference)/i', $text)) {
            return 'comparative';
        }

        if (preg_match('/(application|practical|implementation|case|example|scenario)/i', $text)) {
            return 'applied';
        }

        if (preg_match('/(guide|tutorial|how-to|instruction)/i', $text)) {
            return 'instructional';
        }

        return 'general';
    }

    private function analyzeStructureElements($text) {
        $elements = [];

        if (preg_match_all('/(\d+\.|\•|\-|\*)/', $text) > 2) {
            $elements[] = 'organized lists';
        }

        if (preg_match('/(important|key|critical|essential|note|remember|highlight)/i', $text)) {
            $elements[] = 'key insights';
        }

        if (preg_match('/(therefore|however|consequently|thus|hence|furthermore|moreover)/i', $text)) {
            $elements[] = 'logical connections';
        }

        if (preg_match('/(example|case|scenario|illustration|instance)/i', $text)) {
            $elements[] = 'practical examples';
        }

        if (preg_match('/(benefit|advantage|outcome|result|impact)/i', $text)) {
            $elements[] = 'outcome analysis';
        }

        return $elements;
    }

    private function hasPracticalContent($text) {
        $practicalIndicators = [
            'application', 'practical', 'implementation', 'apply', 'use', 'utilize',
            'practice', 'real-world', 'hands-on', 'implementation', 'deployment'
        ];

        $textLower = strtolower($text);
        foreach ($practicalIndicators as $indicator) {
            if (strpos($textLower, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

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