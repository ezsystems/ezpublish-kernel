--
-- EZP-30797: As an administrator, I want to configure a password expiration for users
--

ALTER TABLE ezuser ADD COLUMN password_updated_at integer;

UPDATE ezuser SET password_updated_at = cast(extract(epoch from CURRENT_TIMESTAMP) as integer);

--
-- EZP-30797: end.
--
