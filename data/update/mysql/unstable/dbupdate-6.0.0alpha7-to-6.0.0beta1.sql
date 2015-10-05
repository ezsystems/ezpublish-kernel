--
-- The `ezrole` table was switched back to its original state
-- (only one PRIMARY KEY being ID column)
--

ALTER TABLE ezrole DROP PRIMARY KEY, ADD PRIMARY KEY(id);
