UPDATE ezsite_data SET value='8.0.0-beta2' WHERE name='ezpublish-version';

ALTER TABLE ezcontentclass_attribute ALTER COLUMN data_text1 TYPE varchar(255);
