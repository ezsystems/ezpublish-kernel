UPDATE ezsite_data SET value='7.5.11' WHERE name='ezpublish-version';

--
-- EZP-30857: Files are not deleted from storage when removing archived versions
--

ALTER TABLE ezimagefile ADD COLUMN version INT(11) NOT NULL DEFAULT '0';

ALTER TABLE ezimagefile ADD KEY ezimage_version ('version', 'contentobject_attribute_id');

--
-- EZP-30857: end.
--
