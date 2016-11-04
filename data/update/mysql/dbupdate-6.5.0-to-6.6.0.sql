SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.6.0' WHERE name='ezpublish-version';

--
-- EZP-26397: marked User.user_account as not searchable
--
UPDATE ezcontentclass_attribute SET is_searchable = 0 WHERE data_type_string = 'ezuser';
