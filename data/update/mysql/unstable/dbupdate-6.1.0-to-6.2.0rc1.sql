SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.2.0' WHERE name='ezpublish-version';

--
-- EZP-19123: Make ezuser.login case in-sensitive across databases
--

ALTER TABLE ezuser ADD COLUMN login_normalized varchar(150) NOT NULL DEFAULT '';
ALTER TABLE ezuser DROP KEY ezuser_login, ADD UNIQUE KEY ezuser_login (login_normalized);
