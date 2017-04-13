ALTER TABLE "ezkeyword_attribute_link" ADD COLUMN "version" int;

UPDATE "ezkeyword_attribute_link"
-- set version to current version of content object
  SET "version" = (
    SELECT "current_version"
    FROM "ezcontentobject_attribute" AS "atr"
      JOIN "ezcontentobject" AS "cnt" ON "atr"."contentobject_id" = "cnt"."id" AND "atr"."version" = "cnt"."current_version"
    WHERE "atr"."id" = "ezkeyword_attribute_link"."objectattribute_id"
  );

ALTER TABLE "ezkeyword_attribute_link" ALTER  COLUMN "version" SET NOT NULL;

CREATE INDEX "ezkeyword_attr_link_oaid_ver" ON "ezkeyword_attribute_link" ("objectattribute_id", "version");
