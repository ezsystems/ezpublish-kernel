--
-- The `ezrole` table has changed. `version` has been added to the primary key, after the `id`. This is because role
-- drafts are now handled differently. A draft now has the same `id` as the published role, and `version` decides which
-- is which. You should make sure you don't have any role drafts in the database when upgrading. To find drafts, select
-- entries in the `ezrole` table where `version` is not zero. Before this change, the `version` of a draft equals the
-- `id` of the role. The safe way to get rid of stray role drafts is to edit the role in question, and then cancel
-- editing.
--

ALTER TABLE ezrole DROP PRIMARY KEY, ADD PRIMARY KEY(id,version);
