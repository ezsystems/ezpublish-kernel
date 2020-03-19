UPDATE ezsite_data SET value='8.0.0' WHERE name='ezpublish-version';

ALTER TABLE ezcontentclass_attribute ALTER COLUMN data_text1 TYPE varchar(255);

ALTER TABLE ezcontentclass_attribute ADD is_thumbnail boolean DEFAULT false NOT NULL;

--
-- EZP-31471: Keywords versioning
--

ALTER TABLE `ezkeyword_attribute_link` ADD COLUMN `versions` TEXT NULL DEFAULT NULL;

--
-- EZP-31471: end.
--