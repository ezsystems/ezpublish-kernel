UPDATE ezsite_data SET value='7.5.3' WHERE name='ezpublish-version';

ALTER TABLE ezcontentclass_attribute ALTER COLUMN data_text1 TYPE varchar(255);

--
-- EZP-30725: Removes content type drafts of non existing users
--

DELETE FROM ezcontentclass
  WHERE NOT EXISTS (
    SELECT 1 FROM ezuser
      WHERE ezuser.contentobject_id = ezcontentclass.modifier_id
  ) AND ezcontentclass.version = 1;

DELETE FROM ezcontentclass_attribute
  WHERE NOT EXISTS (
    SELECT 1 FROM ezcontentclass
      WHERE ezcontentclass.id = ezcontentclass_attribute.contentclass_id
      AND ezcontentclass.version = ezcontentclass_attribute.version
  );

DELETE FROM ezcontentclass_attribute_ml
  WHERE NOT EXISTS (
    SELECT 1 FROM ezcontentclass_attribute
      WHERE ezcontentclass_attribute.id = ezcontentclass_attribute_ml.contentclass_attribute_id
      AND ezcontentclass_attribute.version = ezcontentclass_attribute_ml.version
  );

DELETE FROM ezcontentclass_classgroup
  WHERE NOT EXISTS (
    SELECT 1 FROM ezcontentclass
      WHERE ezcontentclass.id = ezcontentclass_classgroup.contentclass_id
      AND ezcontentclass.version = ezcontentclass_classgroup.contentclass_version
  );

DELETE FROM ezcontentclass_name
  WHERE NOT EXISTS (
    SELECT 1 FROM ezcontentclass
      WHERE ezcontentclass.id = ezcontentclass_name.contentclass_id
      AND ezcontentclass.version = ezcontentclass_name.contentclass_version
  );

--
-- EZP-30725: end.
--
