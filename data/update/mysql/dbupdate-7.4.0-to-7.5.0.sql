SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.5.0' WHERE name='ezpublish-version';

--
-- EZP-29990 - Add table for multilingual FieldDefinitions
--

DROP TABLE IF EXISTS `ezcontentclass_attribute_ml`;
CREATE TABLE `ezcontentclass_attribute_ml` (
  `contentclass_attribute_id` INT NOT NULL,
  `version` INT NOT NULL,
  `language_id` BIGINT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `data_text` TEXT NULL,
  `data_json` TEXT NULL,
  PRIMARY KEY (`contentclass_attribute_id`, `version`, `language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `ezcontentclass_attribute_ml`
ADD CONSTRAINT `ezcontentclass_attribute_ml_lang_fk`
  FOREIGN KEY (`language_id`)
  REFERENCES `ezcontent_language` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

--
-- EZP-30149: As a Developer I want uniform eznotification DB table definition across all DBMS-es
--

ALTER TABLE `eznotification` MODIFY COLUMN `data` TEXT;

--
-- EZP-30139: As an editor I want to hide and reveal a content item
--

ALTER TABLE `ezcontentobject` ADD COLUMN `is_hidden` tinyint(1) NOT NULL DEFAULT '0';

--
-- EZP-29324: ezcontentobject_tree_contentobject_id_path_string index column size is too large
-- This shortens indexes so that 4-byte content can fit.
--

ALTER TABLE `ezcontentobject_tree` DROP KEY `ezcontentobject_tree_contentobject_id_path_string`;
ALTER TABLE `ezcontentobject_tree` ADD UNIQUE KEY `ezcontentobject_tree_contentobject_id_path_string` (`path_string` (191), `contentobject_id`);
