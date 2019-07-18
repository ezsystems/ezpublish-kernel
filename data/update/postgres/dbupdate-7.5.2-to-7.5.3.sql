UPDATE ezsite_data SET value='7.5.3' WHERE name='ezpublish-version';

ALTER TABLE ezcontentclass_attribute ALTER COLUMN data_text1 TYPE varchar(255);
