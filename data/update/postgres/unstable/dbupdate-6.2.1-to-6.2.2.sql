--
-- EZP-25568: Cannot edit user class
--
UPDATE ezcontentclass_attribute SET is_searchable = 0 WHERE data_type_string = 'eztext' OR data_type_string = 'ezuser';
