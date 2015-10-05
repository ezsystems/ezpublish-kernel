--
-- The `ezrole` table was switched back to its original state
-- (only one PRIMARY KEY being ID column)
--

ALTER TABLE ezrole DROP CONSTRAINT ezrole_pkey, ADD CONSTRAINT ezrole_pkey PRIMARY KEY (id);
