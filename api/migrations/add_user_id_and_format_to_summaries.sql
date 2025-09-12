-- Add user_id and format columns to summaries table
ALTER TABLE summaries
ADD COLUMN user_id INT NOT NULL AFTER note_id,
ADD COLUMN format ENUM('paragraph','bullet_points') DEFAULT 'paragraph' AFTER length,
ADD INDEX idx_user_id (user_id),
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Update existing records to set user_id based on note ownership
UPDATE summaries s
INNER JOIN notes n ON s.note_id = n.id
SET s.user_id = n.user_id
WHERE s.user_id IS NULL OR s.user_id = 0;