BEGIN;

-- Set storage engine schema version number
UPDATE ezsite_data SET value='7.5.0' WHERE name='ezpublish-version';

CREATE TABLE IF NOT EXISTS ezpasswordblacklist (
  id SERIAL,
  password character varying(255) NOT NULL,
  CONSTRAINT ezpasswordblacklist_pkey PRIMARY KEY (id)
);

DROP INDEX IF EXISTS ezpasswordblacklist_password;
CREATE INDEX ezpasswordblacklist_password ON ezpasswordblacklist USING btree (password);

COMMIT;
