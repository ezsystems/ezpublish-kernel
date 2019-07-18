UPDATE ezsite_data SET value='7.5.3' WHERE name='ezpublish-version';

ALTER TABLE ezcontentclass_attribute MODIFY data_text1 VARCHAR(255);
