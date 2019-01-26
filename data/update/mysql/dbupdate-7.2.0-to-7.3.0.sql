SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.3.0' WHERE name='ezpublish-version';

--
-- EZP-28881: Add a field to support "date object was trashed"
--

ALTER TABLE ezcontentobject_trash add  trashed int(11) NOT NULL DEFAULT '0';

--
-- PR-2495: Fixing The maximum column size is 767 bytes.
--

ALTER TABLE `ezcontentobject_tree` DROP KEY `ezcontentobject_tree_contentobject_id_path_string`;
ALTER TABLE `ezcontentobject_tree` ADD KEY `ezcontentobject_tree_contentobject_id_path_string` (`path_string` (191), `contentobject_id`);