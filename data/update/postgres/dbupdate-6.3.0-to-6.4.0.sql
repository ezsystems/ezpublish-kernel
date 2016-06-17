-- Set storage engine schema version number
UPDATE ezsite_data SET value='6.4.0' WHERE name='ezpublish-version';

--
-- EZP-25817: Invalid subtree path when assigning role with subtree limitation
--

UPDATE ezuser_role SET limit_value = CONCAT(limit_value, '/') WHERE limit_identifier = 'Subtree' AND limit_value ~ '[[:digit:]]$';
