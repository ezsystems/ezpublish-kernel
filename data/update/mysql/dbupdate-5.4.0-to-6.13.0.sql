SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.13.0' WHERE name='ezpublish-version';

-- IMPORTANT:
-- * This only covers eZ Platform kernel, see doc for all steps (incl. database schema) needed when updating: https://doc.ezplatform.com/en/2.5/updating/updating_ez_platform/

--
-- EZP-25880: Make ezuser.login case in-sensitive across databases, using case in-sensitive index
--

ALTER TABLE ezuser DROP KEY ezuser_login, ADD UNIQUE KEY ezuser_login (login);

--
-- EZP-26397: marked User.user_account as not searchable
--
UPDATE ezcontentclass_attribute SET is_searchable = 0 WHERE data_type_string = 'ezuser';


-- BEGIN script for EZP-26070: PHP API support for new Publish permission

-- Create content/publish policy for roles that have content/create or content/edit policies
INSERT INTO `ezpolicy` (`module_name`, `function_name`, `original_id`, `role_id`)
  SELECT DISTINCT 'content', 'publish', `original_id`, `role_id`
  FROM `ezpolicy`
  WHERE `module_name` = 'content' AND `function_name` IN ('create', 'edit')
    AND NOT EXISTS (SELECT 1 FROM `ezpolicy` WHERE `module_name` = 'content' AND `function_name` = 'publish');

-- Create limitations for just created policy (policies)
INSERT INTO `ezpolicy_limitation` (`identifier`, `policy_id`)
  SELECT DISTINCT `l0`.`identifier`, `p1`.`id`
  FROM `ezpolicy_limitation` AS `l0`
  JOIN `ezpolicy` AS `p0` ON `l0`.`policy_id` = `p0`.`id`
  JOIN `ezpolicy` AS `p1` ON `p0`.`role_id` = `p1`.`role_id` AND `p1`.`module_name` = 'content' AND `p1`.`function_name` = 'publish'
  WHERE `p0`.`module_name` = 'content' AND `p0`.`function_name` IN ('create', 'edit') AND `l0`.`identifier` NOT IN ('ParentOwner', 'ParentGroup', 'ParentClass', 'ParentDepth');

-- Create content/publish limitation values entries based on existing entries matched by limitation identifier and role
INSERT INTO `ezpolicy_limitation_value` (`limitation_id`, `value`)
  SELECT `l1`.`id`, `value`
  FROM `ezpolicy_limitation` AS `l0`
  JOIN `ezpolicy_limitation_value` AS `lv0` ON `lv0`.`limitation_id` = `l0`.`id`
  JOIN `ezpolicy` AS `p0` ON `p0`.`id` = `l0`.`policy_id`
  JOIN `ezpolicy_limitation` AS `l1` ON `l0`.`identifier` = `l1`.`identifier`
  JOIN `ezpolicy` AS `p1`
    ON `l1`.`policy_id` = `p1`.`id`
    AND `p1`.`module_name` = 'content'
    AND `p1`.`function_name` = 'publish'
    AND `p1`.`role_id` = `p0`.`role_id`;

-- END script for EZP-26070

--
-- EZP-24744: Increase password security
--

ALTER TABLE ezuser CHANGE password_hash password_hash VARCHAR(255) default NULL;
