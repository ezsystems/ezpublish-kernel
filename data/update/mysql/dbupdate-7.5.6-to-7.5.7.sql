UPDATE ezsite_data SET value='7.5.7' WHERE name='ezpublish-version';
--
-- EZP-31299: Searching in languages
--

ALTER TABLE `ezsearch_object_word_link`
ADD COLUMN `language_mask` BIGINT NOT NULL DEFAULT 0;

-- SET DEFAULT MASK VALUE SINCE REINDEX
UPDATE `ezsearch_object_word_link` SET `language_mask` = 9223372036854775807;

--
-- EZP-31299: end.
--
