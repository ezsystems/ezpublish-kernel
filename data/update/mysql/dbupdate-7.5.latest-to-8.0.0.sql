UPDATE ezsite_data SET value='8.0.0' WHERE name='ezpublish-version';

ALTER TABLE ezcontentclass_attribute MODIFY data_text1 VARCHAR(255);

ALTER TABLE ezcontentclass_attribute ADD COLUMN is_thumbnail TINYINT(1) NOT NULL DEFAULT '0';
