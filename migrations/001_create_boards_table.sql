-- Migration: Create boards table for kanban boards
-- Date: 2025-10-25

-- Create boards table
CREATE TABLE IF NOT EXISTS boards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    position INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add board_id column to tasks table
ALTER TABLE tasks
ADD COLUMN board_id INT NULL AFTER id,
ADD COLUMN position INT NOT NULL DEFAULT 0 AFTER status,
ADD INDEX idx_board_id (board_id),
ADD INDEX idx_position (position),
ADD CONSTRAINT fk_tasks_board
    FOREIGN KEY (board_id) REFERENCES boards(id)
    ON DELETE SET NULL;

-- Insert default board
INSERT INTO boards (name, description, position)
VALUES ('Основная доска', 'Доска задач по умолчанию', 0);

-- Update existing tasks to use the default board
UPDATE tasks
SET board_id = (SELECT id FROM boards WHERE name = 'Основная доска' LIMIT 1)
WHERE board_id IS NULL;
