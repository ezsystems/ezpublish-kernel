UPDATE ezsite_data SET value='8.0.0' WHERE name='ezpublish-version';

ALTER TABLE ezcontentclass_attribute ALTER COLUMN data_text1 TYPE varchar(255);

ALTER TABLE ezcontentclass_attribute ADD is_thumbnail boolean DEFAULT false NOT NULL;

--
-- EZP-31471: Keywords versioning
--

ALTER TABLE "ezkeyword_attribute_link" ADD COLUMN "version" INT;

UPDATE `ezkeyword_attribute_link` SET `version` = (
    SELECT `current_version`
    FROM `ezcontentobject_attribute` AS `cattr`
    JOIN `ezcontentobject` AS `contentobj` ON `cattr`.`contentobject_id` = `contentobj`.`id` AND `cattr`.`version` = `contentobj`.`current_version`
    WHERE `cattr`.`id` = `ezkeyword_attribute_link`.`objectattribute_id`
);

ALTER TABLE "ezkeyword_attribute_link" ALTER  COLUMN "version" SET NOT NULL;

CREATE INDEX "ezkeyword_attr_link_oaid_ver" ON "ezkeyword_attribute_link" ("objectattribute_id", "version");

--
-- EZP-31471: end.
--
