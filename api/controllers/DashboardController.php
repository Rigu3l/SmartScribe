<?php

class DashboardController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getStats() {
        try {
            // Get user ID from header
            $userId = $this->getUserIdFromHeader();

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            // Get total notes count
            $totalNotesQuery = "SELECT COUNT(*) as total FROM notes WHERE user_id = :user_id";
            $stmt = $this->db->prepare($totalNotesQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $totalNotesResult = $stmt->fetch();
            $totalNotes = $totalNotesResult ? (int)$totalNotesResult['total'] : 0;

            // Get notes created this week
            $weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
            $notesThisWeekQuery = "SELECT COUNT(*) as count FROM notes WHERE user_id = :user_id AND created_at >= :week_ago";
            $stmt = $this->db->prepare($notesThisWeekQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':week_ago', $weekAgo);
            $stmt->execute();
            $notesThisWeekResult = $stmt->fetch();
            $notesThisWeek = $notesThisWeekResult ? (int)$notesThisWeekResult['count'] : 0;

            // Get quiz statistics
            $quizStatsQuery = "
                SELECT
                    COUNT(*) as total_quizzes,
                    AVG(score) as average_score,
                    COUNT(CASE WHEN score >= 80 THEN 1 END) as high_scores
                FROM quizzes q
                INNER JOIN notes n ON q.note_id = n.id
                WHERE n.user_id = :user_id
            ";
            $stmt = $this->db->prepare($quizStatsQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $quizData = $stmt->fetch() ?: [];

            $totalQuizzes = $quizData['total_quizzes'] ?? 0;
            $averageScore = $quizData['average_score'] ? round($quizData['average_score'], 1) : 0;

            // Estimate study time based on note creation patterns
            // This is a simple estimation - in a real app you'd track actual study sessions
            $studyTimeQuery = "
                SELECT
                    COUNT(*) as notes_today,
                    TIMESTAMPDIFF(MINUTE, MIN(created_at), MAX(created_at)) as time_span_minutes
                FROM notes
                WHERE user_id = :user_id
                AND DATE(created_at) = CURDATE()
            ";
            $stmt = $this->db->prepare($studyTimeQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $studyData = $stmt->fetch() ?: ['notes_today' => 0, 'time_span_minutes' => 0];

            // Estimate study hours (simple calculation)
            $estimatedHours = 0;
            if ($studyData['notes_today'] > 0) {
                // Assume 15 minutes per note on average
                $estimatedHours = round(($studyData['notes_today'] * 15) / 60, 1);
            }

            // Get recent activity (last 5 notes)
            $recentActivityQuery = "
                SELECT
                    title,
                    created_at,
                    'note_created' as activity_type
                FROM notes
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT 5
            ";
            $stmt = $this->db->prepare($recentActivityQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $recentNotes = $stmt->fetchAll() ?: [];

            // Format recent activity
            $recentActivity = [];
            foreach ($recentNotes as $note) {
                $recentActivity[] = [
                    'title' => 'Created note: ' . substr($note['title'], 0, 30) . '...',
                    'time' => $this->formatTimeAgo($note['created_at']),
                    'icon' => ['fas', 'file-alt'],
                    'iconColor' => 'bg-blue-600'
                ];
            }

            // If no recent activity, add a placeholder
            if (empty($recentActivity)) {
                $recentActivity[] = [
                    'title' => 'Welcome to SmartScribe!',
                    'time' => 'Just now',
                    'icon' => ['fas', 'rocket'],
                    'iconColor' => 'bg-green-600'
                ];
            }

            $stats = [
                'totalNotes' => (int)$totalNotes,
                'notesThisWeek' => (int)$notesThisWeek,
                'studyHours' => (float)$estimatedHours,
                'studyHoursThisWeek' => (float)$estimatedHours, // Simplified for now
                'quizAverage' => (float)$averageScore,
                'quizzesCompleted' => (int)$totalQuizzes,
                'recentActivity' => $recentActivity,
                'lastUpdated' => date('Y-m-d H:i:s')
            ];

            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch dashboard statistics: ' . $e->getMessage()
            ]);
        }
    }

    private function formatTimeAgo($datetime) {
        $now = new DateTime();
        $created = new DateTime($datetime);
        $diff = $now->diff($created);

        if ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
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