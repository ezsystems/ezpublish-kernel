--
-- EZP-24744: Increase password security
--

ALTER TABLE ezuser ALTER COLUMN password_hash TYPE VARCHAR(255);
