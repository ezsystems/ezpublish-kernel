SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.4.5' WHERE name='ezpublish-version';

--
-- EZP-29324: ezcontentobject_tree_contentobject_id_path_string index column size is too large
-- This shortens indexes so that 4-byte content can fit.
--

ALTER TABLE `ezcontentobject_tree` DROP KEY `ezcontentobject_tree_contentobject_id_path_string`;
ALTER TABLE `ezcontentobject_tree` ADD UNIQUE KEY `ezcontentobject_tree_contentobject_id_path_string` (`path_string` (191), `contentobject_id`);
