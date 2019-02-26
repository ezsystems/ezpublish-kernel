-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.5.0' WHERE name='ezpublish-version';

--
-- EZP-29990: Add table for multilingual FieldDefinitions
--

DROP TABLE IF EXISTS ezcontentclass_attribute_ml;
CREATE TABLE ezcontentclass_attribute_ml (
    contentclass_attribute_id INT NOT NULL,
    version integer NOT NULL,
    language_id SERIAL NOT NULL,
    name character varying(255) NOT NULL,
    description text DEFAULT NULL,
    data_text text DEFAULT NULL,
    data_json text DEFAULT NULL
);

CREATE INDEX contentclass_attribute_id ON ezcontentclass_attribute_ml USING btree (contentclass_attribute_id, version, language_id);

ALTER TABLE ezcontentclass_attribute_ml
ADD CONSTRAINT ezcontentclass_attribute_ml_lang_fk
  FOREIGN KEY (language_id)
  REFERENCES ezcontent_language (id)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

--
-- EZP-30149: As a Developer I want uniform eznotification DB table definition across all DBMS-es
--

ALTER TABLE eznotification ALTER COLUMN is_pending TYPE BOOLEAN;
ALTER TABLE eznotification ALTER COLUMN is_pending SET DEFAULT true;

--
-- EZP-30139: As an editor I want to hide and reveal a content item
--

ALTER TABLE ezcontentobject ADD is_hidden boolean DEFAULT false NOT NULL;
