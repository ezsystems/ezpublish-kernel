SET default_storage_engine=InnoDB;

-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.5.0' WHERE name='ezpublish-version';

--
-- Table structure for table `ezpasswordblacklist`
--
DROP TABLE IF EXISTS `ezpasswordblacklist`;
CREATE TABLE `ezpasswordblacklist` (
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
   `password` varchar(255) NOT NULL DEFAULT '',
   PRIMARY KEY (`id`),
   UNIQUE KEY `ezpasswordblacklist_password` (`password`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
