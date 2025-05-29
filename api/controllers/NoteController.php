<?php

class NoteController {
    private $conn;
    private $note;

    public function __construct($db) {
        $this->db = $db;
        $this->note = new Note($db);
    }

    public function uploadProcessedNote($userId, $files, $data) {
        $imagePath = null;

        // Handle optional image upload
        if (isset($files['image'])) {
            $fileUpload = new FileUpload();
            $uploadResult = $fileUpload->uploadImage($files['image']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagePath = $uploadResult['file_path'];
        }

        // Save the note
        $this->note->user_id = $userId;
        $this->note->title = $data->title;
        $this->note->original_text = $data->text;
        $this->note->image_path = $imagePath;

        $noteId = $this->note->create();

        if ($noteId) {
            return [
                "success" => true,
                "message" => "Note saved successfully",
                "note_id" => $noteId
            ];
        } else {
            return [
                "success" => false,
                "message" => "Failed to save note"
            ];
        }
    }

    public function store($requestData) {
        $userId = $requestData["user_id"] ?? null;
        $files = $_FILES ?? [];
        $data = json_decode(file_get_contents("php:/input"));

        // Fallback in case JSON wasn't used for the request body
        if (!$data && isset($requestData["title"]) && isset($requestData['text'])) {
            $data = (object) [
                'title'=> $requestData['title'],
                'text'=> $requestData['text'],
            ];
    }

    $result = $this->uploadProcessedNote($userId, $files, $data);
    echo json_encode($result);

}

    public function getUserNotes($userId) {
        $this->note->user_id = $userId;
        $stmt = $this->note->readAllByUser();
        
        $notes = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notes[] = [
                "note_id" => $row['note_id'],
                "title" => $row['title'],
                "created_at" => $row['created_at'],
                "has_summary" => $row['summary_count'] > 0
            ];
        }

        return [
            "success" => true,
            "notes" => $notes
        ];
    }
}
