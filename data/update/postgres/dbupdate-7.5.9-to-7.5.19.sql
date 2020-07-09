UPDATE ezsite_data SET value='7.5.10' WHERE name='ezpublish-version';

-- Begin EZP-31511
ALTER TABLE ezurlalias_ml
    DROP CONSTRAINT ezurlalias_ml_pkey,
    ADD CONSTRAINT ezurlalias_ml_pkey PRIMARY KEY (parent, text_md5, lang_mask);
-- End EZP-31511
