SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.11.0' WHERE name='ezpublish-version';

-- eZ Publish users who has upgraded will get warning that table was missing,
-- this is fine as you'll already removed it when upgrading to 5.4

DROP TABLE ezsearch_return_count;
