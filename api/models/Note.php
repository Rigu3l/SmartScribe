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

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function readAllByUser() {
        $query = "SELECT n.*, 
                         (SELECT COUNT(*) FROM summaries s WHERE s.note_id = n.note_id) as summary_count
                  FROM notes n
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();

        return $stmt;
    }
}
