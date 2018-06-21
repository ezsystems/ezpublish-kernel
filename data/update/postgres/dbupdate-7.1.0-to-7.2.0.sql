-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.2.0' WHERE name='ezpublish-version';

--
-- EZP-29146: As a developer, I want a API to manage bookmarks
--

CREATE INDEX ezcontentbrowsebookmark_user_location ON ezcontentbrowsebookmark USING btree (node_id, user_id);
CREATE INDEX ezcontentbrowsebookmark_location ON ezcontentbrowsebookmark USING btree (node_id);

ALTER TABLE ezcontentbrowsebookmark
ADD CONSTRAINT ezcontentbrowsebookmark_location_fk
  FOREIGN KEY (node_id)
  REFERENCES ezcontentobject_tree (node_id)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

ALTER TABLE ezcontentbrowsebookmark
ADD CONSTRAINT ezcontentbrowsebookmark_user_fk
  FOREIGN KEY (user_id)
  REFERENCES ezuser (contentobject_id)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

--
-- EZEE-2081: Move NotificationBundle into AdminUI
--

CREATE TABLE eznotification (
    id SERIAL,
    owner_id integer DEFAULT 0 NOT NULL ,
    is_pending integer DEFAULT 1 NOT NULL,
    type character varying(128) NOT NULL,
    created integer DEFAULT 0 NOT NULL,
    data bytea
);

ALTER TABLE ONLY eznotification
    ADD CONSTRAINT eznotification_pkey PRIMARY KEY (id);

CREATE INDEX eznotification_owner_id ON eznotification USING btree (owner_id);
CREATE INDEX eznotification_owner_id_is_pending ON eznotification USING btree (owner_id, is_pending);
