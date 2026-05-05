-- Migration 007: Login Throttling
ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0;
ALTER TABLE users ADD COLUMN last_attempt DATETIME NULL;
