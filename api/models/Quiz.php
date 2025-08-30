<?php

class Quiz {
    private $conn;
    public $note_id;
    public $questions;
    public $difficulty;
    public $score;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO quizzes (note_id, questions, difficulty, score, created_at)
                  VALUES (:note_id, :questions, :difficulty, :score, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':note_id', $this->note_id);
        $stmt->bindParam(':questions', $this->questions);
        $stmt->bindParam(':difficulty', $this->difficulty);
        $stmt->bindParam(':score', $this->score);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function readAllByUser($userId) {
        $query = "SELECT q.*
                  FROM quizzes q
                  INNER JOIN notes n ON q.note_id = n.id
                  WHERE n.user_id = :user_id
                  ORDER BY q.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt;
    }

    public function readById($id, $userId) {
        $query = "SELECT q.*
                  FROM quizzes q
                  INNER JOIN notes n ON q.note_id = n.id
                  WHERE q.id = :id AND n.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $quiz = $stmt->fetch();
        if ($quiz) {
            $quiz['questions'] = json_decode($quiz['questions'], true);
        }

        return $quiz;
    }

    public function update($id, $data) {
        $updateFields = [];
        $params = [':id' => $id];

        if (isset($data['score'])) {
            $updateFields[] = "score = :score";
            $params[':score'] = $data['score'];
        }

        if (isset($data['questions'])) {
            $updateFields[] = "questions = :questions";
            $params[':questions'] = json_encode($data['questions']);
        }

        if (empty($updateFields)) {
            return false;
        }

        $query = "UPDATE quizzes SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }
}
?>