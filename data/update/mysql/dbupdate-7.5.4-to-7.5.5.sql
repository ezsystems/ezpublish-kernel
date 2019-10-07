--
-- EZP-30797: As an administrator, I want to configure a password expiration for users
--

ALTER TABLE ezuser ADD COLUMN password_updated_at INT(11) NULL;

UPDATE ezuser SET password_updated_at = UNIX_TIMESTAMP();

--
-- EZP-30797: end.
--
