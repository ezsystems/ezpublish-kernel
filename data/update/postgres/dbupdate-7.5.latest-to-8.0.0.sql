UPDATE ezsite_data SET value='8.0.0' WHERE name='ezpublish-version';

ALTER TABLE ezcontentclass_attribute ADD is_thumbnail boolean DEFAULT false NOT NULL;
