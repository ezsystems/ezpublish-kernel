UPDATE ezsite_data SET value='7.5.8' WHERE name='ezpublish-version';
--
-- EZP-31471: Keywords versioning
--

ALTER TABLE `ezkeyword_attribute_link`
ADD COLUMN `versions` TEXT NULL DEFAULT NULL;

--
-- EZP-31471: end.
--
