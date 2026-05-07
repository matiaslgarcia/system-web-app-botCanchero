-- Fix for soccer_field column naming mismatch
ALTER TABLE soccer_field RENAME COLUMN price_per_hour TO price_hour;
