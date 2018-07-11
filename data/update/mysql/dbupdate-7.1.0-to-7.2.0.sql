SET default_storage_engine=InnoDB;
BEGIN;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.2.0' WHERE name='ezpublish-version';

--
-- EZP-28950: MySQL UTF8 doesn't support 4-byte chars
-- This shortens indexes so that 4-byte content can fit.
-- After running these, convert the table character set, see doc/upgrade/7.2.md
--

ALTER TABLE `ezbasket` DROP KEY `ezbasket_session_id`;
ALTER TABLE `ezbasket` ADD KEY `ezbasket_session_id` (`session_id` (191));

ALTER TABLE `ezcollab_group` DROP KEY `ezcollab_group_path`;
ALTER TABLE `ezcollab_group` ADD KEY `ezcollab_group_path` (`path_string` (191));

ALTER TABLE `ezcontent_language` DROP KEY `ezcontent_language_name`;
ALTER TABLE `ezcontent_language` ADD KEY `ezcontent_language_name` (`name` (191));

ALTER TABLE `ezcontentobject_attribute` DROP KEY `sort_key_string`;
ALTER TABLE `ezcontentobject_attribute` ADD KEY `sort_key_string` (`sort_key_string` (191));

ALTER TABLE `ezcontentobject_name` DROP KEY `ezcontentobject_name_name`;
ALTER TABLE `ezcontentobject_name` ADD KEY `ezcontentobject_name_name` (`name` (191));

ALTER TABLE `ezcontentobject_trash` DROP KEY `ezcobj_trash_path`;
ALTER TABLE `ezcontentobject_trash` ADD KEY `ezcobj_trash_path` (`path_string` (191));

ALTER TABLE `ezcontentobject_tree` DROP KEY `ezcontentobject_tree_path`;
ALTER TABLE `ezcontentobject_tree` ADD KEY `ezcontentobject_tree_path` (`path_string` (191));

ALTER TABLE `ezimagefile` DROP KEY `ezimagefile_file`;
ALTER TABLE `ezimagefile` ADD KEY `ezimagefile_file` (`filepath` (191));

ALTER TABLE `ezkeyword` DROP KEY `ezkeyword_keyword`;
ALTER TABLE `ezkeyword` ADD KEY `ezkeyword_keyword` (`keyword` (191));

ALTER TABLE `ezorder_status` DROP KEY `ezorder_status_name`;
ALTER TABLE `ezorder_status` ADD KEY `ezorder_status_name` (`name` (191));

ALTER TABLE `ezpolicy_limitation_value` DROP KEY `ezpolicy_limitation_value_val`;
ALTER TABLE `ezpolicy_limitation_value` ADD KEY `ezpolicy_limitation_value_val` (`value` (191));

ALTER TABLE `ezprest_authcode` DROP PRIMARY KEY;
ALTER TABLE `ezprest_authcode` ADD PRIMARY KEY (`id` (191));

ALTER TABLE `ezprest_authcode` DROP KEY `authcode_client_id`;
ALTER TABLE `ezprest_authcode` ADD KEY `authcode_client_id` (`client_id` (191));

ALTER TABLE `ezprest_clients` DROP KEY `client_id_unique`;
ALTER TABLE `ezprest_clients` ADD UNIQUE KEY `client_id_unique` (`client_id` (191),`version`);

ALTER TABLE `ezprest_token` DROP PRIMARY KEY;
ALTER TABLE `ezprest_token` ADD PRIMARY KEY (`id` (191));

ALTER TABLE `ezprest_token` DROP KEY `token_client_id`;
ALTER TABLE `ezprest_token` ADD KEY `token_client_id` (`client_id` (191));

ALTER TABLE `ezsearch_object_word_link` DROP KEY `ezsearch_object_word_link_identifier`;
ALTER TABLE `ezsearch_object_word_link` ADD KEY `ezsearch_object_word_link_identifier` (`identifier` (191));

ALTER TABLE `ezsearch_search_phrase` DROP KEY `ezsearch_search_phrase_phrase`;
ALTER TABLE `ezsearch_search_phrase` ADD UNIQUE KEY `ezsearch_search_phrase_phrase` (`phrase` (191));

ALTER TABLE `ezurl` DROP KEY `ezurl_url`;
ALTER TABLE `ezurl` ADD KEY `ezurl_url` (`url` (191));

ALTER TABLE `ezurlalias` DROP KEY `ezurlalias_desturl`;
ALTER TABLE `ezurlalias` ADD KEY `ezurlalias_desturl` (`destination_url` (191));

ALTER TABLE `ezurlalias` DROP KEY `ezurlalias_source_url`;
ALTER TABLE `ezurlalias` ADD KEY `ezurlalias_source_url` (`source_url` (191));

--
-- EZP-29146: As a developer, I want a API to manage bookmarks
--

ALTER TABLE `ezcontentbrowsebookmark`
ADD INDEX `ezcontentbrowsebookmark_user_location` (`node_id`, `user_id`);

ALTER TABLE `ezcontentbrowsebookmark`
ADD INDEX `ezcontentbrowsebookmark_location` (`node_id`);

ALTER TABLE `ezcontentbrowsebookmark`
ADD CONSTRAINT `ezcontentbrowsebookmark_location_fk`
  FOREIGN KEY (`node_id`)
  REFERENCES `ezcontentobject_tree` (`node_id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

ALTER TABLE `ezcontentbrowsebookmark`
ADD CONSTRAINT `ezcontentbrowsebookmark_user_fk`
  FOREIGN KEY (`user_id`)
  REFERENCES `ezuser` (`contentobject_id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

--
-- EZEE-2081: Move NotificationBundle into AdminUI
--

CREATE TABLE IF NOT EXISTS `eznotification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL DEFAULT 0,
  `is_pending` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(128) NOT NULL DEFAULT '',
  `created` int(11) NOT NULL DEFAULT 0,
  `data` blob,
  PRIMARY KEY (`id`),
  KEY `eznotification_owner` (`owner_id`),
  KEY `eznotification_owner_is_pending` (`owner_id`, `is_pending`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

COMMIT;

-- If the queries below fail, it means database is already updated

CREATE INDEX `ezcontentobject_tree_contentobject_id_path_string` ON `ezcontentobject_tree` (`path_string`, `contentobject_id`);
CREATE INDEX `ezcontentobject_section` ON `ezcontentobject` (`section_id`);
