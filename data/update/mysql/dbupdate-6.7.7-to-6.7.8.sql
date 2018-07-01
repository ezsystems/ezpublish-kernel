SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.7.8' WHERE name='ezpublish-version';

CREATE INDEX `ezcontentobject_tree_contentobject_id_path_string` ON `ezcontentobject_tree` (`path_string`, `contentobject_id`);
CREATE INDEX `ezcontentobject_section` ON `ezcontentobject` (`section_id`);
