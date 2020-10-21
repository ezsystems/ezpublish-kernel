UPDATE ezsite_data SET value='7.5.0' WHERE name='ezpublish-version';

-- IMPORTANT:
-- * This only covers eZ Platform kernel, see doc for all steps (incl. database schema) needed when updating: https://doc.ezplatform.com/en/2.5/updating/updating_ez_platform/


-- 6.13.0/7.1.0 - 7.2.0

--
-- EZP-29146: As a developer, I want a API to manage bookmarks
--

BEGIN;
CREATE INDEX ezcontentbrowsebookmark_user_location ON ezcontentbrowsebookmark USING btree (node_id, user_id);
CREATE INDEX ezcontentbrowsebookmark_location ON ezcontentbrowsebookmark USING btree (node_id);

ALTER TABLE ezcontentbrowsebookmark
ADD CONSTRAINT ezcontentbrowsebookmark_location_fk
  FOREIGN KEY (node_id)
  REFERENCES ezcontentobject_tree (node_id)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

ALTER TABLE ezcontentbrowsebookmark
ADD CONSTRAINT ezcontentbrowsebookmark_user_fk
  FOREIGN KEY (user_id)
  REFERENCES ezuser (contentobject_id)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

--
-- EZEE-2081: Move NotificationBundle into AdminUI
--

CREATE TABLE IF NOT EXISTS eznotification (
    id SERIAL,
    owner_id integer DEFAULT 0 NOT NULL ,
    is_pending integer DEFAULT 1 NOT NULL,
    type character varying(128) NOT NULL,
    created integer DEFAULT 0 NOT NULL,
    data text,
    CONSTRAINT eznotification_pkey PRIMARY KEY (id)
);

DROP INDEX IF EXISTS eznotification_owner_id;
CREATE INDEX eznotification_owner_id ON eznotification USING btree (owner_id);

DROP INDEX IF EXISTS eznotification_owner_id_is_pending;
CREATE INDEX eznotification_owner_id_is_pending ON eznotification USING btree (owner_id, is_pending);
COMMIT;

-- Since ezpublish-kernel 7.0 the PostgreSQL index names follow a convention imposed by the SERIAL type
BEGIN;
ALTER SEQUENCE ezapprove_items_s RENAME TO ezapprove_items_id_seq;
ALTER SEQUENCE ezbasket_s RENAME TO ezbasket_id_seq;
ALTER SEQUENCE ezcobj_state_s RENAME TO ezcobj_state_id_seq;
ALTER SEQUENCE ezcobj_state_group_s RENAME TO ezcobj_state_group_id_seq;
ALTER SEQUENCE ezcollab_group_s RENAME TO ezcollab_group_id_seq;
ALTER SEQUENCE ezcollab_item_s RENAME TO ezcollab_item_id_seq;
ALTER SEQUENCE ezcollab_item_message_link_s RENAME TO ezcollab_item_message_link_id_seq;
ALTER SEQUENCE ezcollab_notification_rule_s RENAME TO ezcollab_notification_rule_id_seq;
ALTER SEQUENCE ezcollab_profile_s RENAME TO ezcollab_profile_id_seq;
ALTER SEQUENCE ezcollab_simple_message_s RENAME TO ezcollab_simple_message_id_seq;
ALTER SEQUENCE ezcontentbrowsebookmark_s RENAME TO ezcontentbrowsebookmark_id_seq;
ALTER SEQUENCE ezcontentbrowserecent_s RENAME TO ezcontentbrowserecent_id_seq;
ALTER SEQUENCE ezcontentclass_s RENAME TO ezcontentclass_id_seq;
ALTER SEQUENCE ezcontentclass_attribute_s RENAME TO ezcontentclass_attribute_id_seq;
ALTER SEQUENCE ezcontentclassgroup_s RENAME TO ezcontentclassgroup_id_seq;
ALTER SEQUENCE ezcontentobject_s RENAME TO ezcontentobject_id_seq;
ALTER SEQUENCE ezcontentobject_attribute_s RENAME TO ezcontentobject_attribute_id_seq;
ALTER SEQUENCE ezvattype_s RENAME TO ezvattype_id_seq;
ALTER SEQUENCE ezcontentobject_link_s RENAME TO ezcontentobject_link_id_seq;
ALTER SEQUENCE ezcontentobject_tree_s RENAME TO ezcontentobject_tree_node_id_seq;
ALTER SEQUENCE ezcontentobject_version_s RENAME TO ezcontentobject_version_id_seq;
ALTER SEQUENCE ezcurrencydata_s RENAME TO ezcurrencydata_id_seq;
ALTER SEQUENCE ezdiscountrule_s RENAME TO ezdiscountrule_id_seq;
ALTER SEQUENCE ezdiscountsubrule_s RENAME TO ezdiscountsubrule_id_seq;
ALTER SEQUENCE ezenumvalue_s RENAME TO ezenumvalue_id_seq;
ALTER SEQUENCE ezforgot_password_s RENAME TO ezforgot_password_id_seq;
ALTER SEQUENCE ezgeneral_digest_user_settings_s RENAME TO ezgeneral_digest_user_settings_id_seq;
ALTER SEQUENCE ezimagefile_s RENAME TO ezimagefile_id_seq;
ALTER SEQUENCE ezinfocollection_s RENAME TO ezinfocollection_id_seq;
ALTER SEQUENCE ezinfocollection_attribute_s RENAME TO ezinfocollection_attribute_id_seq;
ALTER SEQUENCE ezisbn_group_s RENAME TO ezisbn_group_id_seq;
ALTER SEQUENCE ezisbn_group_range_s RENAME TO ezisbn_group_range_id_seq;
ALTER SEQUENCE ezisbn_registrant_range_s RENAME TO ezisbn_registrant_range_id_seq;
ALTER SEQUENCE ezkeyword_s RENAME TO ezkeyword_id_seq;
ALTER SEQUENCE ezkeyword_attribute_link_s RENAME TO ezkeyword_attribute_link_id_seq;
ALTER SEQUENCE ezmessage_s RENAME TO ezmessage_id_seq;
ALTER SEQUENCE ezmodule_run_s RENAME TO ezmodule_run_id_seq;
ALTER SEQUENCE ezmultipricedata_s RENAME TO ezmultipricedata_id_seq;
ALTER SEQUENCE eznode_assignment_s RENAME TO eznode_assignment_id_seq;
ALTER SEQUENCE eznotificationcollection_s RENAME TO eznotificationcollection_id_seq;
ALTER SEQUENCE eznotificationcollection_item_s RENAME TO eznotificationcollection_item_id_seq;
ALTER SEQUENCE eznotificationevent_s RENAME TO eznotificationevent_id_seq;
ALTER SEQUENCE ezoperation_memento_s RENAME TO ezoperation_memento_id_seq;
ALTER SEQUENCE ezorder_s RENAME TO ezorder_id_seq;
ALTER SEQUENCE ezorder_nr_incr_s RENAME TO ezorder_nr_incr_id_seq;
ALTER SEQUENCE ezorder_item_s RENAME TO ezorder_item_id_seq;
ALTER SEQUENCE ezorder_status_s RENAME TO ezorder_status_id_seq;
ALTER SEQUENCE ezorder_status_history_s RENAME TO ezorder_status_history_id_seq;
ALTER SEQUENCE ezpackage_s RENAME TO ezpackage_id_seq;
ALTER SEQUENCE ezpaymentobject_s RENAME TO ezpaymentobject_id_seq;
ALTER SEQUENCE ezpdf_export_s RENAME TO ezpdf_export_id_seq;
ALTER SEQUENCE ezpending_actions_s RENAME TO ezpending_actions_id_seq;
ALTER SEQUENCE ezpolicy_s RENAME TO ezpolicy_id_seq;
ALTER SEQUENCE ezpolicy_limitation_s RENAME TO ezpolicy_limitation_id_seq;
ALTER SEQUENCE ezpolicy_limitation_value_s RENAME TO ezpolicy_limitation_value_id_seq;
ALTER SEQUENCE ezpreferences_s RENAME TO ezpreferences_id_seq;
ALTER SEQUENCE ezprest_authorized_clients_s RENAME TO ezprest_authorized_clients_id_seq;
ALTER SEQUENCE ezprest_clients_s RENAME TO ezprest_clients_id_seq;
ALTER SEQUENCE ezproductcategory_s RENAME TO ezproductcategory_id_seq;
ALTER SEQUENCE ezproductcollection_s RENAME TO ezproductcollection_id_seq;
ALTER SEQUENCE ezproductcollection_item_s RENAME TO ezproductcollection_item_id_seq;
ALTER SEQUENCE ezproductcollection_item_opt_s RENAME TO ezproductcollection_item_opt_id_seq;
ALTER SEQUENCE ezrole_s RENAME TO ezrole_id_seq;
ALTER SEQUENCE ezrss_export_s RENAME TO ezrss_export_id_seq;
ALTER SEQUENCE ezrss_export_item_s RENAME TO ezrss_export_item_id_seq;
ALTER SEQUENCE ezrss_import_s RENAME TO ezrss_import_id_seq;
ALTER SEQUENCE ezscheduled_script_s RENAME TO ezscheduled_script_id_seq;
ALTER SEQUENCE ezsearch_object_word_link_s RENAME TO ezsearch_object_word_link_id_seq;
ALTER SEQUENCE ezsearch_search_phrase_s RENAME TO ezsearch_search_phrase_id_seq;
ALTER SEQUENCE ezsearch_word_s RENAME TO ezsearch_word_id_seq;
ALTER SEQUENCE ezsection_s RENAME TO ezsection_id_seq;
ALTER SEQUENCE ezsubtree_notification_rule_s RENAME TO ezsubtree_notification_rule_id_seq;
ALTER SEQUENCE eztrigger_s RENAME TO eztrigger_id_seq;
ALTER SEQUENCE ezurl_s RENAME TO ezurl_id_seq;
ALTER SEQUENCE ezurlalias_s RENAME TO ezurlalias_id_seq;
ALTER SEQUENCE ezurlalias_ml_incr_s RENAME TO ezurlalias_ml_incr_id_seq;
ALTER SEQUENCE ezurlwildcard_s RENAME TO ezurlwildcard_id_seq;
ALTER SEQUENCE ezuser_accountkey_s RENAME TO ezuser_accountkey_id_seq;
ALTER SEQUENCE ezuser_discountrule_s RENAME TO ezuser_discountrule_id_seq;
ALTER SEQUENCE ezuser_role_s RENAME TO ezuser_role_id_seq;
ALTER SEQUENCE ezvatrule_s RENAME TO ezvatrule_id_seq;
ALTER SEQUENCE ezwaituntildatevalue_s RENAME TO ezwaituntildatevalue_id_seq;
ALTER SEQUENCE ezwishlist_s RENAME TO ezwishlist_id_seq;
ALTER SEQUENCE ezworkflow_s RENAME TO ezworkflow_id_seq;
ALTER SEQUENCE ezworkflow_assign_s RENAME TO ezworkflow_assign_id_seq;
ALTER SEQUENCE ezworkflow_event_s RENAME TO ezworkflow_event_id_seq;
ALTER SEQUENCE ezworkflow_group_s RENAME TO ezworkflow_group_id_seq;
ALTER SEQUENCE ezworkflow_process_s RENAME TO ezworkflow_process_id_seq;

ALTER TABLE ezapprove_items ALTER COLUMN id SET DEFAULT nextval('ezapprove_items_id_seq');
ALTER TABLE ezbasket ALTER COLUMN id SET DEFAULT nextval('ezbasket_id_seq');
ALTER TABLE ezcobj_state ALTER COLUMN id SET DEFAULT nextval('ezcobj_state_id_seq');
ALTER TABLE ezcobj_state_group ALTER COLUMN id SET DEFAULT nextval('ezcobj_state_group_id_seq');
ALTER TABLE ezcollab_group ALTER COLUMN id SET DEFAULT nextval('ezcollab_group_id_seq');
ALTER TABLE ezcollab_item ALTER COLUMN id SET DEFAULT nextval('ezcollab_item_id_seq');
ALTER TABLE ezcollab_item_message_link ALTER COLUMN id SET DEFAULT nextval('ezcollab_item_message_link_id_seq');
ALTER TABLE ezcollab_notification_rule ALTER COLUMN id SET DEFAULT nextval('ezcollab_notification_rule_id_seq');
ALTER TABLE ezcollab_profile ALTER COLUMN id SET DEFAULT nextval('ezcollab_profile_id_seq');
ALTER TABLE ezcollab_simple_message ALTER COLUMN id SET DEFAULT nextval('ezcollab_simple_message_id_seq');
ALTER TABLE ezcontentbrowsebookmark ALTER COLUMN id SET DEFAULT nextval('ezcontentbrowsebookmark_id_seq');
ALTER TABLE ezcontentbrowserecent ALTER COLUMN id SET DEFAULT nextval('ezcontentbrowserecent_id_seq');
ALTER TABLE ezcontentclass ALTER COLUMN id SET DEFAULT nextval('ezcontentclass_id_seq');
ALTER TABLE ezcontentclass_attribute ALTER COLUMN id SET DEFAULT nextval('ezcontentclass_attribute_id_seq');
ALTER TABLE ezcontentclassgroup ALTER COLUMN id SET DEFAULT nextval('ezcontentclassgroup_id_seq');
ALTER TABLE ezcontentobject ALTER COLUMN id SET DEFAULT nextval('ezcontentobject_id_seq');
ALTER TABLE ezcontentobject_attribute ALTER COLUMN id SET DEFAULT nextval('ezcontentobject_attribute_id_seq');
ALTER TABLE ezvattype ALTER COLUMN id SET DEFAULT nextval('ezvattype_id_seq');
ALTER TABLE ezcontentobject_link ALTER COLUMN id SET DEFAULT nextval('ezcontentobject_link_id_seq');
ALTER TABLE ezcontentobject_tree ALTER COLUMN node_id SET DEFAULT nextval('ezcontentobject_tree_node_id_seq');
ALTER TABLE ezcontentobject_version ALTER COLUMN id SET DEFAULT nextval('ezcontentobject_version_id_seq');
ALTER TABLE ezcurrencydata ALTER COLUMN id SET DEFAULT nextval('ezcurrencydata_id_seq');
ALTER TABLE ezdiscountrule ALTER COLUMN id SET DEFAULT nextval('ezdiscountrule_id_seq');
ALTER TABLE ezdiscountsubrule ALTER COLUMN id SET DEFAULT nextval('ezdiscountsubrule_id_seq');
ALTER TABLE ezenumvalue ALTER COLUMN id SET DEFAULT nextval('ezenumvalue_id_seq');
ALTER TABLE ezforgot_password ALTER COLUMN id SET DEFAULT nextval('ezforgot_password_id_seq');
ALTER TABLE ezgeneral_digest_user_settings ALTER COLUMN id SET DEFAULT nextval('ezgeneral_digest_user_settings_id_seq');
ALTER TABLE ezimagefile ALTER COLUMN id SET DEFAULT nextval('ezimagefile_id_seq');
ALTER TABLE ezinfocollection ALTER COLUMN id SET DEFAULT nextval('ezinfocollection_id_seq');
ALTER TABLE ezinfocollection_attribute ALTER COLUMN id SET DEFAULT nextval('ezinfocollection_attribute_id_seq');
ALTER TABLE ezisbn_group ALTER COLUMN id SET DEFAULT nextval('ezisbn_group_id_seq');
ALTER TABLE ezisbn_group_range ALTER COLUMN id SET DEFAULT nextval('ezisbn_group_range_id_seq');
ALTER TABLE ezisbn_registrant_range ALTER COLUMN id SET DEFAULT nextval('ezisbn_registrant_range_id_seq');
ALTER TABLE ezkeyword ALTER COLUMN id SET DEFAULT nextval('ezkeyword_id_seq');
ALTER TABLE ezkeyword_attribute_link ALTER COLUMN id SET DEFAULT nextval('ezkeyword_attribute_link_id_seq');
ALTER TABLE ezmessage ALTER COLUMN id SET DEFAULT nextval('ezmessage_id_seq');
ALTER TABLE ezmodule_run ALTER COLUMN id SET DEFAULT nextval('ezmodule_run_id_seq');
ALTER TABLE ezmultipricedata ALTER COLUMN id SET DEFAULT nextval('ezmultipricedata_id_seq');
ALTER TABLE eznode_assignment ALTER COLUMN id SET DEFAULT nextval('eznode_assignment_id_seq');
ALTER TABLE eznotificationcollection ALTER COLUMN id SET DEFAULT nextval('eznotificationcollection_id_seq');
ALTER TABLE eznotificationcollection_item ALTER COLUMN id SET DEFAULT nextval('eznotificationcollection_item_id_seq');
ALTER TABLE eznotificationevent ALTER COLUMN id SET DEFAULT nextval('eznotificationevent_id_seq');
ALTER TABLE ezoperation_memento ALTER COLUMN id SET DEFAULT nextval('ezoperation_memento_id_seq');
ALTER TABLE ezorder ALTER COLUMN id SET DEFAULT nextval('ezorder_id_seq');
ALTER TABLE ezorder_nr_incr ALTER COLUMN id SET DEFAULT nextval('ezorder_nr_incr_id_seq');
ALTER TABLE ezorder_item ALTER COLUMN id SET DEFAULT nextval('ezorder_item_id_seq');
ALTER TABLE ezorder_status ALTER COLUMN id SET DEFAULT nextval('ezorder_status_id_seq');
ALTER TABLE ezorder_status_history ALTER COLUMN id SET DEFAULT nextval('ezorder_status_history_id_seq');
ALTER TABLE ezpackage ALTER COLUMN id SET DEFAULT nextval('ezpackage_id_seq');
ALTER TABLE ezpaymentobject ALTER COLUMN id SET DEFAULT nextval('ezpaymentobject_id_seq');
ALTER TABLE ezpdf_export ALTER COLUMN id SET DEFAULT nextval('ezpdf_export_id_seq');
ALTER TABLE ezpending_actions ALTER COLUMN id SET DEFAULT nextval('ezpending_actions_id_seq');
ALTER TABLE ezpolicy ALTER COLUMN id SET DEFAULT nextval('ezpolicy_id_seq');
ALTER TABLE ezpolicy_limitation ALTER COLUMN id SET DEFAULT nextval('ezpolicy_limitation_id_seq');
ALTER TABLE ezpolicy_limitation_value ALTER COLUMN id SET DEFAULT nextval('ezpolicy_limitation_value_id_seq');
ALTER TABLE ezpreferences ALTER COLUMN id SET DEFAULT nextval('ezpreferences_id_seq');
ALTER TABLE ezprest_authorized_clients ALTER COLUMN id SET DEFAULT nextval('ezprest_authorized_clients_id_seq');
ALTER TABLE ezprest_clients ALTER COLUMN id SET DEFAULT nextval('ezprest_clients_id_seq');
ALTER TABLE ezproductcategory ALTER COLUMN id SET DEFAULT nextval('ezproductcategory_id_seq');
ALTER TABLE ezproductcollection ALTER COLUMN id SET DEFAULT nextval('ezproductcollection_id_seq');
ALTER TABLE ezproductcollection_item ALTER COLUMN id SET DEFAULT nextval('ezproductcollection_item_id_seq');
ALTER TABLE ezproductcollection_item_opt ALTER COLUMN id SET DEFAULT nextval('ezproductcollection_item_opt_id_seq');
ALTER TABLE ezrole ALTER COLUMN id SET DEFAULT nextval('ezrole_id_seq');
ALTER TABLE ezrss_export ALTER COLUMN id SET DEFAULT nextval('ezrss_export_id_seq');
ALTER TABLE ezrss_export_item ALTER COLUMN id SET DEFAULT nextval('ezrss_export_item_id_seq');
ALTER TABLE ezrss_import ALTER COLUMN id SET DEFAULT nextval('ezrss_import_id_seq');
ALTER TABLE ezscheduled_script ALTER COLUMN id SET DEFAULT nextval('ezscheduled_script_id_seq');
ALTER TABLE ezsearch_object_word_link ALTER COLUMN id SET DEFAULT nextval('ezsearch_object_word_link_id_seq');
ALTER TABLE ezsearch_search_phrase ALTER COLUMN id SET DEFAULT nextval('ezsearch_search_phrase_id_seq');
ALTER TABLE ezsearch_word ALTER COLUMN id SET DEFAULT nextval('ezsearch_word_id_seq');
ALTER TABLE ezsection ALTER COLUMN id SET DEFAULT nextval('ezsection_id_seq');
ALTER TABLE ezsubtree_notification_rule ALTER COLUMN id SET DEFAULT nextval('ezsubtree_notification_rule_id_seq');
ALTER TABLE eztrigger ALTER COLUMN id SET DEFAULT nextval('eztrigger_id_seq');
ALTER TABLE ezurl ALTER COLUMN id SET DEFAULT nextval('ezurl_id_seq');
ALTER TABLE ezurlalias ALTER COLUMN id SET DEFAULT nextval('ezurlalias_id_seq');
ALTER TABLE ezurlalias_ml_incr ALTER COLUMN id SET DEFAULT nextval('ezurlalias_ml_incr_id_seq');
ALTER TABLE ezurlwildcard ALTER COLUMN id SET DEFAULT nextval('ezurlwildcard_id_seq');
ALTER TABLE ezuser_accountkey ALTER COLUMN id SET DEFAULT nextval('ezuser_accountkey_id_seq');
ALTER TABLE ezuser_discountrule ALTER COLUMN id SET DEFAULT nextval('ezuser_discountrule_id_seq');
ALTER TABLE ezuser_role ALTER COLUMN id SET DEFAULT nextval('ezuser_role_id_seq');
ALTER TABLE ezvatrule ALTER COLUMN id SET DEFAULT nextval('ezvatrule_id_seq');
ALTER TABLE ezwaituntildatevalue ALTER COLUMN id SET DEFAULT nextval('ezwaituntildatevalue_id_seq');
ALTER TABLE ezwishlist ALTER COLUMN id SET DEFAULT nextval('ezwishlist_id_seq');
ALTER TABLE ezworkflow ALTER COLUMN id SET DEFAULT nextval('ezworkflow_id_seq');
ALTER TABLE ezworkflow_assign ALTER COLUMN id SET DEFAULT nextval('ezworkflow_assign_id_seq');
ALTER TABLE ezworkflow_event ALTER COLUMN id SET DEFAULT nextval('ezworkflow_event_id_seq');
ALTER TABLE ezworkflow_group ALTER COLUMN id SET DEFAULT nextval('ezworkflow_group_id_seq');
ALTER TABLE ezworkflow_process ALTER COLUMN id SET DEFAULT nextval('ezworkflow_process_id_seq');
COMMIT;
/*-- NOTE: the above statements were obtained by executing the following SQLs:
SELECT 'ALTER SEQUENCE ' || regexp_replace(def.adsrc, 'nextval.*''(.+)''.*', E'\\1') || ' RENAME TO ' || tbl.relname || '_' || col.attname || '_seq;'
FROM pg_class tbl
JOIN pg_attribute col ON col.attrelid = tbl.oid
JOIN pg_attrdef def ON def.adnum = col.attnum AND def.adrelid = col.attrelid
WHERE def.adsrc LIKE 'nextval(%';

SELECT 'ALTER TABLE ' || tbl.relname || ' ALTER COLUMN ' || col.attname || ' SET DEFAULT nextval(''' || tbl.relname || '_' || col.attname || '_seq'');'
FROM pg_class tbl
JOIN pg_attribute col ON col.attrelid = tbl.oid
JOIN pg_attrdef def ON def.adnum = col.attnum AND def.adrelid = col.attrelid
WHERE def.adsrc LIKE 'nextval(%';
*/

-- If the queries below fail, it means database is already updated

CREATE INDEX ezcontentobject_tree_contentobject_id_path_string ON ezcontentobject_tree (path_string, contentobject_id);
CREATE INDEX ezcontentobject_section ON ezcontentobject (section_id);


-- 7.2.0 - 7.3.0

--
-- EZP-28881: Add a field to support "date object was trashed"
--

ALTER TABLE ezcontentobject_trash add  trashed integer DEFAULT 0 NOT NULL;


-- 7.4.0 - 7.5.0

--
-- EZP-29990: Add table for multilingual FieldDefinitions
--

DROP TABLE IF EXISTS ezcontentclass_attribute_ml;
CREATE TABLE ezcontentclass_attribute_ml (
    contentclass_attribute_id INT NOT NULL,
    version integer NOT NULL,
    language_id SERIAL NOT NULL,
    name character varying(255) NOT NULL,
    description text DEFAULT NULL,
    data_text text DEFAULT NULL,
    data_json text DEFAULT NULL
);

CREATE INDEX contentclass_attribute_id ON ezcontentclass_attribute_ml USING btree (contentclass_attribute_id, version, language_id);

ALTER TABLE ezcontentclass_attribute_ml
ADD CONSTRAINT ezcontentclass_attribute_ml_lang_fk
  FOREIGN KEY (language_id)
  REFERENCES ezcontent_language (id)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

--
-- EZP-30149: As a Developer I want uniform eznotification DB table definition across all DBMS-es
--

ALTER TABLE eznotification ALTER COLUMN is_pending TYPE BOOLEAN;
ALTER TABLE eznotification ALTER COLUMN is_pending SET DEFAULT true;

--
-- EZP-30139: As an editor I want to hide and reveal a content item
--

ALTER TABLE ezcontentobject ADD is_hidden boolean DEFAULT false NOT NULL;
