-- eZ Publish users who has upgraded will get warning that table was missing,
-- this is fine as you'll already removed it when upgrading to 5.4

DROP TABLE ezsearch_return_count;
