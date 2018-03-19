SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.2.0' WHERE name='ezpublish-version';

--
-- EZP-28950: MySQL UTF8 doesn't support 4-byte chars
--

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

ALTER TABLE `ezsearch_search_phrase` DROP KEY `ezsearch_search_phrase_phrase`;
ALTER TABLE `ezsearch_search_phrase` ADD UNIQUE KEY `ezsearch_search_phrase_phrase` (`phrase` (191));
