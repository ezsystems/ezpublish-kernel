UPDATE ezsite_data SET value='7.5.11' WHERE name='ezpublish-version';

--
-- EZP-30857: Files are not deleted from storage when removing archived versions
--

ALTER TABLE ezimagefile ADD COLUMN version integer DEFAULT 0 NOT NULL;

CREATE INDEX ezimage_version ON ezimagefile (version);

--
-- EZP-30857: end.
