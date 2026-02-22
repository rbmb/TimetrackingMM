-- Migration: Add is_admin column to users table
-- Run this if you already have the database created without is_admin

ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0 AFTER display_name;

-- Set the existing admin user as admin
UPDATE users SET is_admin = 1 WHERE username = 'admin';
