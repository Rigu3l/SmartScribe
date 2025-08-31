<?php

class Note {
    private $conn;
    public $user_id;
    public $title;
    public $original_text;
    public $image_path;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO notes (user_id, title, original_text, image_path, created_at)
                  VALUES (:user_id, :title, :original_text, :image_path, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':original_text', $this->original_text);
        $stmt->bindParam(':image_path', $this->image_path);

        // Debug logging
        error_log("Note::create() - User ID: $this->user_id");
        error_log("Note::create() - Title: $this->title");
        error_log("Note::create() - Text: " . substr($this->original_text, 0, 100));
        error_log("Note::create() - Image path: $this->image_path");

        if ($stmt->execute()) {
            $lastInsertId = $this->conn->lastInsertId();
            error_log("Note::create() - Successfully created note with ID: $lastInsertId");
            return $lastInsertId;
        } else {
            $error = $stmt->errorInfo();
            error_log("Note::create() - Failed to create note: " . json_encode($error));
            return false;
        }
    }

    public function readAllByUser() {
        $query = "SELECT n.*,
                         (SELECT COUNT(*) FROM summaries s WHERE s.note_id = n.id) as summary_count
                  FROM notes n
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();

        return $stmt;
    }

    public function readById($id, $userId) {
        $query = "SELECT * FROM notes WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetch();
    }
}
