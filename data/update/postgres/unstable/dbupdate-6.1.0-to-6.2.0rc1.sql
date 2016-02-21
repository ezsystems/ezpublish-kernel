-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.1.0' WHERE name='ezpublish-version';

--
-- EZP-19123: Make ezuser.login case in-sensitive across databases
--

ALTER TABLE ezuser ADD login_normalized character varying(150) DEFAULT ''::character varying NOT NULL;
ALTER TABLE ezuser DROP CONSTRAINT ezuser_login, ADD CONSTRAINT ezuser_login UNIQUE KEY (login_normalized);
