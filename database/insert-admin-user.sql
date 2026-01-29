-- Quick Fix: Insert Default Admin User
-- Use this if you're unable to login

-- First, check if admin user exists
SELECT * FROM users WHERE username = 'admin';

-- If no result, insert the admin user
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `is_active`) VALUES
('admin', 'admin@realvibe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 1);

-- Verify the user was created
SELECT id, username, email, full_name, role, is_active FROM users;

-- Login Credentials:
-- Username: admin
-- Password: admin123
