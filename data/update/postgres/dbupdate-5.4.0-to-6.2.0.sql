-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.2.0' WHERE name='ezpublish-version';

--
-- If you have used Platform API (PHP/REST) in 5.x/6.0.0 or UI in 6.0.0 for moving trees around
-- then the following will solve issues caused by visibility not taken into account (see EZP-24467)
--

UPDATE ezcontentobject_tree
SET is_invisible=1
WHERE path_string LIKE concat(
    (SELECT path_string FROM ezcontentobject_tree WHERE is_hidden=1),
    '%'
);

-- You may also execute the following SQL if you have locations marked as invisible when they should not be

UPDATE ezcontentobject_tree
SET is_invisible=0
WHERE path_string NOT LIKE concat(
    (SELECT path_string FROM ezcontentobject_tree WHERE is_hidden=1),
    '%'
);

--
-- EZP-19123: Make ezuser.login case in-sensitive across databases
--

ALTER TABLE ezuser ADD login_normalized character varying(150) DEFAULT ''::character varying NOT NULL;
UPDATE ezuser SET login_normalized=lower(login);

-- Note: As part of EZP-19123, it was decided to not allow duplicate login with different case, this should not have
-- been issue on mysql where code checking for existing use was implicit case-insensitive. However on postgres this was
-- not the case, meaning you'll need to manually handle conflicts if anyone occurs on the following line because of the
-- new "UNIQUE" constraint.
ALTER TABLE ezuser DROP CONSTRAINT ezuser_login, ADD CONSTRAINT ezuser_login UNIQUE KEY (login_normalized);
