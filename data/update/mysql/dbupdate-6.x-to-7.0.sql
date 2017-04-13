ALTER TABLE `ezkeyword_attribute_link`
  ADD COLUMN `version` int(11) NOT NULL,
  ADD KEY `ezkeyword_attr_link_oaid_ver` (`objectattribute_id`, `version`)
;

UPDATE `ezkeyword_attribute_link`
-- set version to current version of content object
  SET `version` = (
    SELECT `current_version`
    FROM `ezcontentobject_attribute` AS `atr`
      JOIN `ezcontentobject` AS `cnt` ON `atr`.`contentobject_id` = `cnt`.`id` AND `atr`.`version` = `cnt`.`current_version`
    WHERE `atr`.`id` = `ezkeyword_attribute_link`.`objectattribute_id`
  );
