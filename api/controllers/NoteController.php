<?php
require_once __DIR__ . '/../models/Note.php';
require_once __DIR__ . '/../helpers/FileUpload.php';

class NoteController {
    private $db;
    private $note;

    public function __construct($db) {
        $this->db = $db;
        $this->note = new Note($db);
    }

    public function store() {
        // Ensure image & text posted
        $userId = 17; // Example — you can replace this with session or token-based user ID

        $title = $_POST['title'] ?? NULL;
        $text = $_POST['text'] ?? NULL;
        $files = $_FILES;

        if (!$title || !$text) {
            echo json_encode(['success' => false, 'error' => 'Missing title or text']);
            return;
        }

        $imagePath = null;

        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $fileUpload = new FileUpload();
            $uploadResult = $fileUpload->uploadImage($files['image']);
            if (!$uploadResult['success']) {
                echo json_encode($uploadResult);
                return;
            }
            $imagePath = $uploadResult['file_path'];
        }

        $this->note->user_id = $userId;
        $this->note->title = $title;
        $this->note->original_text = $text;
        $this->note->image_path = $imagePath;

        $noteId = $this->note->create();

        if ($noteId) {
            echo json_encode([
                "success" => true,
                "message" => "Note saved successfully",
                "note_id" => $noteId
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Failed to save note"
            ]);
        }
    }
}
?>
