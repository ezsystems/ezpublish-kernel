SET default_storage_engine=InnoDB;
-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.1.0' WHERE name='ezpublish-version';

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
