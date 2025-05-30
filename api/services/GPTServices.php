<?php
// api/services/GPTService.php
require_once __DIR__ . '/../../vendor/autoload.php';

class GPTService {
    private $apiKey;
    
    public function __construct() {
        // Load API key from environment or config file
        $this->apiKey = 'your_openai_api_key_here'; // Replace with your actual API key
    }
    
    public function generateSummary($text, $length = 'medium') {
        $lengthPrompt = [
            'short' => 'in about 50 words',
            'medium' => 'in about 100 words',
            'long' => 'in about 200 words'
        ];
        
        $prompt = "Summarize the following text {$lengthPrompt[$length]}:\n\n$text";
        
        return $this->callOpenAI($prompt);
    }
    
    public function generateQuiz($text, $difficulty = 'medium', $count = 5) {
        $prompt = "Create a multiple-choice quiz with $count questions at $difficulty difficulty level based on this text:\n\n$text";
        
        return $this->callOpenAI($prompt, 1000);
    }
    
    public function extractKeywords($text, $count = 5) {
        $prompt = "Extract the $count most important keywords or concepts from this text:\n\n$text";
        
        $response = $this->callOpenAI($prompt, 100);
        
        // Process the response to get an array of keywords
        $keywords = explode(',', $response);
        $keywords = array_map('trim', $keywords);
        
        return $keywords;
    }
    
    private function callOpenAI($prompt, $maxTokens = 500) {
        $url = 'https://api.openai.com/v1/completions';
        
        $data = [
            'model' => 'gpt-4o',
            'prompt' => $prompt,
            'max_tokens' => $maxTokens,
            'temperature' => 0.7,
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        if ($err) {
            return null;
        }
        
        $responseData = json_decode($response, true);
        return $responseData['choices'][0]['text'] ?? null;
    }
}