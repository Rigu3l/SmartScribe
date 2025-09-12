<?php

class Goal {
    private $conn;
    public $user_id;
    public $title;
    public $description;
    public $target_type;
    public $target_value;
    public $current_value;
    public $deadline;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO learning_goals (user_id, title, description, target_type, target_value, current_value, deadline, status, created_at)
                  VALUES (:user_id, :title, :description, :target_type, :target_value, :current_value, :deadline, :status, NOW())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':target_type', $this->target_type);
        $stmt->bindParam(':target_value', $this->target_value);
        $stmt->bindParam(':current_value', $this->current_value);
        $stmt->bindParam(':deadline', $this->deadline);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function readAllByUser($userId) {
        $query = "SELECT * FROM learning_goals WHERE user_id = :user_id ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt;
    }

    public function readById($id, $userId) {
        $query = "SELECT * FROM learning_goals WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function update($id, $data) {
        $updateFields = [];
        $params = [':id' => $id];

        if (isset($data['title'])) {
            $updateFields[] = "title = :title";
            $params[':title'] = $data['title'];
        }

        if (isset($data['description'])) {
            $updateFields[] = "description = :description";
            $params[':description'] = $data['description'];
        }

        if (isset($data['target_type'])) {
            $updateFields[] = "target_type = :target_type";
            $params[':target_type'] = $data['target_type'];
        }

        if (isset($data['target_value'])) {
            $updateFields[] = "target_value = :target_value";
            $params[':target_value'] = $data['target_value'];
        }

        if (isset($data['current_value'])) {
            $updateFields[] = "current_value = :current_value";
            $params[':current_value'] = $data['current_value'];
        }

        if (isset($data['deadline'])) {
            $updateFields[] = "deadline = :deadline";
            $params[':deadline'] = $data['deadline'];
        }

        if (isset($data['status'])) {
            $updateFields[] = "status = :status";
            $params[':status'] = $data['status'];
        }

        if (empty($updateFields)) {
            return false;
        }

        $query = "UPDATE learning_goals SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }

    public function updateProgress($id, $userId, $newValue) {
        $query = "UPDATE learning_goals SET current_value = :current_value, updated_at = NOW() WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':current_value', $newValue);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);

        return $stmt->execute();
    }

    public function delete($id, $userId) {
        $query = "DELETE FROM learning_goals WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);

        return $stmt->execute();
    }

    public function getActiveGoalsCount($userId) {
        $query = "SELECT COUNT(*) as count FROM learning_goals WHERE user_id = :user_id AND status = 'active'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function getCompletedGoalsThisMonth($userId) {
        $query = "SELECT COUNT(*) as count FROM learning_goals
                  WHERE user_id = :user_id AND status = 'completed'
                  AND MONTH(updated_at) = MONTH(CURRENT_DATE())
                  AND YEAR(updated_at) = YEAR(CURRENT_DATE())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
?>