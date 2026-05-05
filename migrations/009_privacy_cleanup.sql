-- Migration 009: Privacy & PII Cleanup
-- Dropping columns that store sensitive customer data not required for business operations.
ALTER TABLE payment 
    DROP COLUMN cardholder_identification_number,
    DROP COLUMN cardholder_identification_type,
    DROP COLUMN card_name;
