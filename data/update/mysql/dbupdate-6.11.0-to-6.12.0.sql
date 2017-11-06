--
-- EZP-24744: Increase password security
--

ALTER TABLE ezuser CHANGE password_hash password_hash VARCHAR(255) default NULL;
