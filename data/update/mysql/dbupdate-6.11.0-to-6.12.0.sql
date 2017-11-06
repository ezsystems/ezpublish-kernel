SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.12.0' WHERE name='ezpublish-version';

--
-- EZP-24744: Increase password security
--

ALTER TABLE ezuser CHANGE password_hash password_hash VARCHAR(255) default NULL;
