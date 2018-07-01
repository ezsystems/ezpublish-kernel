-- Set storage engine schema version number
BEGIN;
UPDATE ezsite_data SET value='6.13.4' WHERE name='ezpublish-version';
COMMIT;

-- If the queries below fail, it means database is already updated

CREATE INDEX ezcontentobject_tree_contentobject_id_path_string ON ezcontentobject_tree (path_string, contentobject_id);
CREATE INDEX ezcontentobject_section ON ezcontentobject (section_id);
