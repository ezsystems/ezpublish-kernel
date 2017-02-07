DROP TABLE IF EXISTS ezbinaryfile;
CREATE TABLE ezbinaryfile (
    contentobject_attribute_id integer DEFAULT 0 NOT NULL,
    download_count integer DEFAULT 0 NOT NULL,
    filename character varying(255) DEFAULT ''::character varying NOT NULL,
    mime_type character varying(255) DEFAULT ''::character varying NOT NULL,
    original_filename character varying(255) DEFAULT ''::character varying NOT NULL,
    "version" integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezmedia;
CREATE TABLE ezmedia (
    contentobject_attribute_id integer DEFAULT 0 NOT NULL,
    download_count integer DEFAULT 0 NOT NULL,
    filename character varying(255) DEFAULT ''::character varying NOT NULL,
    mime_type character varying(255) DEFAULT ''::character varying NOT NULL,
    original_filename character varying(255) DEFAULT ''::character varying NOT NULL,
    "version" integer DEFAULT 0 NOT NULL,
    controls character varying(50) DEFAULT ''::character varying NOT NULL,
    has_controller integer DEFAULT 0 NOT NULL,
    height integer DEFAULT 0 NOT NULL,
    is_autoplay integer DEFAULT 0 NOT NULL,
    is_loop integer DEFAULT 0 NOT NULL,
    pluginspage character varying(255) DEFAULT ''::character varying NOT NULL,
    quality character varying(50) DEFAULT ''::character varying NOT NULL,
    width integer DEFAULT NULL
);

DROP TABLE IF EXISTS ezimagefile;
CREATE TABLE ezimagefile (
    contentobject_attribute_id integer DEFAULT 0 NOT NULL,
    filepath text NOT NULL,
    id SERIAL
);

DROP TABLE IF EXISTS ezgmaplocation;
CREATE TABLE ezgmaplocation (
  contentobject_attribute_id integer DEFAULT '0' NOT NULL,
  contentobject_version integer DEFAULT '0' NOT NULL,
  latitude double precision DEFAULT '0' NOT NULL,
  longitude double precision DEFAULT '0' NOT NULL,
  address character varying(150) DEFAULT NULL
);

DROP TABLE IF EXISTS ezcobj_state;
CREATE TABLE ezcobj_state (
    default_language_id bigint NOT NULL DEFAULT 0,
    group_id integer NOT NULL DEFAULT 0,
    id SERIAL,
    identifier character varying(45) NOT NULL DEFAULT ''::character varying,
    language_mask bigint NOT NULL DEFAULT 0,
    priority integer NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS ezcobj_state_group;
CREATE TABLE ezcobj_state_group (
    default_language_id bigint NOT NULL DEFAULT 0,
    id SERIAL,
    identifier character varying(45) NOT NULL DEFAULT ''::character varying,
    language_mask bigint NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS ezcobj_state_group_language;
CREATE TABLE ezcobj_state_group_language (
    contentobject_state_group_id integer NOT NULL DEFAULT 0,
    description text NOT NULL,
    language_id bigint NOT NULL DEFAULT 0,
    real_language_id bigint NOT NULL DEFAULT 0,
    name character varying(45) NOT NULL DEFAULT ''::character varying
);

DROP TABLE IF EXISTS ezcobj_state_language;
CREATE TABLE ezcobj_state_language (
    contentobject_state_id integer NOT NULL DEFAULT 0,
    description text NOT NULL,
    language_id bigint NOT NULL DEFAULT 0,
    name character varying(45) NOT NULL DEFAULT ''::character varying
);

DROP TABLE IF EXISTS ezcobj_state_link;
CREATE TABLE ezcobj_state_link (
  contentobject_id integer NOT NULL DEFAULT 0,
  contentobject_state_id integer NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS ezcontent_language;
CREATE TABLE ezcontent_language (
    disabled integer DEFAULT 0 NOT NULL,
    id bigint DEFAULT 0 NOT NULL,
    locale character varying(20) DEFAULT ''::character varying NOT NULL,
    name character varying(255) DEFAULT ''::character varying NOT NULL
);

DROP TABLE IF EXISTS ezcontentclass;
CREATE TABLE ezcontentclass (
    always_available integer DEFAULT 0 NOT NULL,
    contentobject_name character varying(255),
    created integer DEFAULT 0 NOT NULL,
    creator_id integer DEFAULT 0 NOT NULL,
    id SERIAL,
    identifier character varying(50) DEFAULT ''::character varying NOT NULL,
    initial_language_id bigint DEFAULT 0 NOT NULL,
    is_container integer DEFAULT 0 NOT NULL,
    language_mask bigint DEFAULT 0 NOT NULL,
    modified integer DEFAULT 0 NOT NULL,
    modifier_id integer DEFAULT 0 NOT NULL,
    remote_id character varying(100) DEFAULT ''::character varying NOT NULL,
    serialized_description_list text,
    serialized_name_list text,
    sort_field integer DEFAULT 1 NOT NULL,
    sort_order integer DEFAULT 1 NOT NULL,
    url_alias_name character varying(255),
    "version" integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezcontentclass_attribute;
CREATE TABLE ezcontentclass_attribute (
    can_translate integer DEFAULT 1,
    category character varying(25) DEFAULT ''::character varying NOT NULL,
    contentclass_id integer DEFAULT 0 NOT NULL,
    data_float1 double precision,
    data_float2 double precision,
    data_float3 double precision,
    data_float4 double precision,
    data_int1 integer,
    data_int2 integer,
    data_int3 integer,
    data_int4 integer,
    data_text1 character varying(50),
    data_text2 character varying(50),
    data_text3 character varying(50),
    data_text4 character varying(255),
    data_text5 text,
    data_type_string character varying(50) DEFAULT ''::character varying NOT NULL,
    id SERIAL,
    identifier character varying(50) DEFAULT ''::character varying NOT NULL,
    is_information_collector integer DEFAULT 0 NOT NULL,
    is_required integer DEFAULT 0 NOT NULL,
    is_searchable integer DEFAULT 0 NOT NULL,
    placement integer DEFAULT 0 NOT NULL,
    serialized_data_text text,
    serialized_description_list text,
    serialized_name_list text NOT NULL,
    "version" integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezcontentclass_classgroup;
CREATE TABLE ezcontentclass_classgroup (
    contentclass_id integer DEFAULT 0 NOT NULL,
    contentclass_version integer DEFAULT 0 NOT NULL,
    group_id integer DEFAULT 0 NOT NULL,
    group_name character varying(255)
);

DROP TABLE IF EXISTS ezcontentclass_name;
CREATE TABLE ezcontentclass_name (
    contentclass_id integer DEFAULT 0 NOT NULL,
    contentclass_version integer DEFAULT 0 NOT NULL,
    language_id bigint DEFAULT 0 NOT NULL,
    language_locale character varying(20) DEFAULT ''::character varying NOT NULL,
    name character varying(255) DEFAULT ''::character varying NOT NULL
);

DROP TABLE IF EXISTS ezcontentclassgroup;
CREATE TABLE ezcontentclassgroup (
    created integer DEFAULT 0 NOT NULL,
    creator_id integer DEFAULT 0 NOT NULL,
    id SERIAL,
    modified integer DEFAULT 0 NOT NULL,
    modifier_id integer DEFAULT 0 NOT NULL,
    name character varying(255)
);

DROP TABLE IF EXISTS ezcontentobject;
CREATE TABLE ezcontentobject (
    contentclass_id integer DEFAULT 0 NOT NULL,
    current_version integer,
    id SERIAL,
    initial_language_id bigint DEFAULT 0 NOT NULL,
    language_mask bigint DEFAULT 0 NOT NULL,
    modified integer DEFAULT 0 NOT NULL,
    name character varying(255),
    owner_id integer DEFAULT 0 NOT NULL,
    published integer DEFAULT 0 NOT NULL,
    remote_id character varying(100),
    section_id integer DEFAULT 0 NOT NULL,
    status integer DEFAULT 0
);

DROP TABLE IF EXISTS ezcontentobject_attribute;
CREATE TABLE ezcontentobject_attribute (
    attribute_original_id integer DEFAULT 0,
    contentclassattribute_id integer DEFAULT 0 NOT NULL,
    contentobject_id integer DEFAULT 0 NOT NULL,
    data_float double precision,
    data_int integer,
    data_text text,
    data_type_string character varying(50) DEFAULT ''::character varying,
    id SERIAL,
    language_code character varying(20) DEFAULT ''::character varying NOT NULL,
    language_id bigint DEFAULT 0 NOT NULL,
    sort_key_int integer DEFAULT 0 NOT NULL,
    sort_key_string character varying(255) DEFAULT ''::character varying NOT NULL,
    "version" integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezcontentobject_link;
CREATE TABLE ezcontentobject_link (
    contentclassattribute_id integer DEFAULT 0 NOT NULL,
    from_contentobject_id integer DEFAULT 0 NOT NULL,
    from_contentobject_version integer DEFAULT 0 NOT NULL,
    id SERIAL,
    relation_type integer DEFAULT 1 NOT NULL,
    to_contentobject_id integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezcontentobject_name;
CREATE TABLE ezcontentobject_name (
    content_translation character varying(20) DEFAULT ''::character varying NOT NULL,
    content_version integer DEFAULT 0 NOT NULL,
    contentobject_id integer DEFAULT 0 NOT NULL,
    language_id bigint DEFAULT 0 NOT NULL,
    name character varying(255),
    real_translation character varying(20)
);

DROP TABLE IF EXISTS ezcontentobject_trash;
CREATE TABLE ezcontentobject_trash (
    contentobject_id integer,
    contentobject_version integer,
    depth integer DEFAULT 0 NOT NULL,
    is_hidden integer DEFAULT 0 NOT NULL,
    is_invisible integer DEFAULT 0 NOT NULL,
    main_node_id integer,
    modified_subnode integer DEFAULT 0,
    node_id integer DEFAULT 0 NOT NULL,
    parent_node_id integer DEFAULT 0 NOT NULL,
    path_identification_string text,
    path_string character varying(255) DEFAULT ''::character varying NOT NULL,
    priority integer DEFAULT 0 NOT NULL,
    remote_id character varying(100) DEFAULT ''::character varying NOT NULL,
    sort_field integer DEFAULT 1,
    sort_order integer DEFAULT 1
);

DROP TABLE IF EXISTS ezcontentobject_tree;
CREATE TABLE ezcontentobject_tree (
    contentobject_id integer,
    contentobject_is_published integer,
    contentobject_version integer,
    depth integer DEFAULT 0 NOT NULL,
    is_hidden integer DEFAULT 0 NOT NULL,
    is_invisible integer DEFAULT 0 NOT NULL,
    main_node_id integer,
    modified_subnode integer DEFAULT 0,
    node_id SERIAL,
    parent_node_id integer DEFAULT 0 NOT NULL,
    path_identification_string text,
    path_string character varying(255) DEFAULT ''::character varying NOT NULL,
    priority integer DEFAULT 0 NOT NULL,
    remote_id character varying(100) DEFAULT ''::character varying NOT NULL,
    sort_field integer DEFAULT 1,
    sort_order integer DEFAULT 1
);

DROP TABLE IF EXISTS ezcontentobject_version;
CREATE TABLE ezcontentobject_version (
    contentobject_id integer,
    created integer DEFAULT 0 NOT NULL,
    creator_id integer DEFAULT 0 NOT NULL,
    id SERIAL,
    initial_language_id bigint DEFAULT 0 NOT NULL,
    language_mask bigint DEFAULT 0 NOT NULL,
    modified integer DEFAULT 0 NOT NULL,
    status integer DEFAULT 0 NOT NULL,
    user_id integer DEFAULT 0 NOT NULL,
    "version" integer DEFAULT 0 NOT NULL,
    workflow_event_pos integer DEFAULT 0
);

DROP TABLE IF EXISTS eznode_assignment;
CREATE TABLE eznode_assignment (
    contentobject_id integer,
    contentobject_version integer,
    from_node_id integer DEFAULT 0,
    id SERIAL,
    is_main integer DEFAULT 0 NOT NULL,
    op_code integer DEFAULT 0 NOT NULL,
    parent_node integer,
    parent_remote_id character varying(100) DEFAULT ''::character varying NOT NULL,
    remote_id character varying(100) DEFAULT '0'::character varying NOT NULL,
    sort_field integer DEFAULT 1,
    sort_order integer DEFAULT 1,
    priority integer DEFAULT 0 NOT NULL,
    is_hidden integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezpolicy;
CREATE TABLE ezpolicy (
    function_name character varying(255),
    id SERIAL,
    module_name character varying(255),
    original_id integer DEFAULT 0 NOT NULL,
    role_id integer
);

DROP TABLE IF EXISTS ezpolicy_limitation;
CREATE TABLE ezpolicy_limitation (
    id SERIAL,
    identifier character varying(255) DEFAULT ''::character varying NOT NULL,
    policy_id integer
);

DROP TABLE IF EXISTS ezpolicy_limitation_value;
CREATE TABLE ezpolicy_limitation_value (
    id SERIAL,
    limitation_id integer,
    value character varying(255)
);

DROP TABLE IF EXISTS ezrole;
CREATE TABLE ezrole (
    id SERIAL,
    is_new integer DEFAULT 0 NOT NULL,
    name character varying(255) DEFAULT ''::character varying NOT NULL,
    value character(1),
    "version" integer DEFAULT 0
);

DROP TABLE IF EXISTS ezsearch_object_word_link;
CREATE TABLE ezsearch_object_word_link (
    contentclass_attribute_id integer DEFAULT 0 NOT NULL,
    contentclass_id integer DEFAULT 0 NOT NULL,
    contentobject_id integer DEFAULT 0 NOT NULL,
    frequency double precision DEFAULT 0::double precision NOT NULL,
    id SERIAL,
    identifier character varying(255) DEFAULT ''::character varying NOT NULL,
    integer_value integer DEFAULT 0 NOT NULL,
    next_word_id integer DEFAULT 0 NOT NULL,
    placement integer DEFAULT 0 NOT NULL,
    prev_word_id integer DEFAULT 0 NOT NULL,
    published integer DEFAULT 0 NOT NULL,
    section_id integer DEFAULT 0 NOT NULL,
    word_id integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezsearch_word;
CREATE TABLE ezsearch_word (
    id SERIAL,
    object_count integer DEFAULT 0 NOT NULL,
    word character varying(150)
);

DROP TABLE IF EXISTS ezsection;
CREATE TABLE ezsection (
    id SERIAL,
    identifier character varying(255),
    locale character varying(255),
    name character varying(255),
    navigation_part_identifier character varying(100) DEFAULT 'ezcontentnavigationpart'::character varying
);

DROP TABLE IF EXISTS ezurl;
CREATE TABLE ezurl (
    created integer DEFAULT 0 NOT NULL,
    id SERIAL,
    is_valid integer DEFAULT 1 NOT NULL,
    last_checked integer DEFAULT 0 NOT NULL,
    modified integer DEFAULT 0 NOT NULL,
    original_url_md5 character varying(32) DEFAULT ''::character varying NOT NULL,
    url text
);

DROP TABLE IF EXISTS ezurl_object_link;
CREATE TABLE ezurl_object_link (
    contentobject_attribute_id integer DEFAULT 0 NOT NULL,
    contentobject_attribute_version integer DEFAULT 0 NOT NULL,
    url_id integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezurlalias;
CREATE TABLE ezurlalias (
    destination_url text NOT NULL,
    forward_to_id integer DEFAULT 0 NOT NULL,
    id SERIAL,
    is_imported integer DEFAULT 0 NOT NULL,
    is_internal integer DEFAULT 1 NOT NULL,
    is_wildcard integer DEFAULT 0 NOT NULL,
    source_md5 character varying(32),
    source_url text NOT NULL
);

DROP TABLE IF EXISTS ezurlalias_ml;
CREATE TABLE ezurlalias_ml (
    "action" text NOT NULL,
    action_type character varying(32) DEFAULT ''::character varying NOT NULL,
    alias_redirects integer DEFAULT 1 NOT NULL,
    id integer DEFAULT 0 NOT NULL,
    is_alias integer DEFAULT 0 NOT NULL,
    is_original integer DEFAULT 0 NOT NULL,
    lang_mask bigint DEFAULT 0 NOT NULL,
    link integer DEFAULT 0 NOT NULL,
    parent integer DEFAULT 0 NOT NULL,
    text text NOT NULL,
    text_md5 character varying(32) DEFAULT ''::character varying NOT NULL
);

DROP TABLE IF EXISTS ezurlalias_ml_incr;
CREATE TABLE ezurlalias_ml_incr (
    id SERIAL
);

DROP TABLE IF EXISTS ezurlwildcard;
CREATE TABLE ezurlwildcard (
    destination_url text NOT NULL,
    id SERIAL,
    source_url text NOT NULL,
    "type" integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezuser;
CREATE TABLE ezuser (
    contentobject_id integer DEFAULT 0 NOT NULL,
    email character varying(150) DEFAULT ''::character varying NOT NULL,
    login character varying(150) DEFAULT ''::character varying NOT NULL,
    password_hash character varying(50),
    password_hash_type integer DEFAULT 1 NOT NULL
);

DROP TABLE IF EXISTS ezuser_role;
CREATE TABLE ezuser_role (
    contentobject_id integer,
    id SERIAL,
    limit_identifier character varying(255) DEFAULT ''::character varying,
    limit_value character varying(255) DEFAULT ''::character varying,
    role_id integer
);

DROP TABLE IF EXISTS ezuser_setting;
CREATE TABLE ezuser_setting (
    is_enabled integer DEFAULT 0 NOT NULL,
    max_login integer,
    user_id integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezuser_accountkey;
CREATE TABLE ezuser_accountkey (
    hash_key character varying(32) DEFAULT '' NOT NULL,
    id SERIAL,
    time integer DEFAULT 0 NOT NULL,
    user_id integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezuservisit;
CREATE TABLE ezuservisit (
    current_visit_timestamp integer DEFAULT 0 NOT NULL,
    failed_login_attempts integer DEFAULT 0 NOT NULL,
    last_visit_timestamp integer DEFAULT 0 NOT NULL,
    login_count integer DEFAULT 0 NOT NULL,
    user_id integer DEFAULT 0 NOT NULL
);

DROP TABLE IF EXISTS ezkeyword;
CREATE TABLE ezkeyword (
  class_id integer DEFAULT 0 NOT NULL,
  id SERIAL,
  keyword character varying(255) DEFAULT NULL
);

DROP TABLE IF EXISTS ezkeyword_attribute_link;
CREATE TABLE ezkeyword_attribute_link (
  id SERIAL,
  keyword_id integer DEFAULT 0 NOT NULL,
  objectattribute_id integer DEFAULT 0 NOT NULL
);

CREATE INDEX ezimagefile_coid ON ezimagefile USING btree (contentobject_attribute_id);

CREATE INDEX ezimagefile_file ON ezimagefile USING btree (filepath);

CREATE INDEX ezgmaplocation_file ON ezgmaplocation USING btree (latitude,longitude);

CREATE UNIQUE INDEX ezcobj_state_identifier ON ezcobj_state USING btree (group_id, identifier);

CREATE INDEX ezcobj_state_lmask ON ezcobj_state USING btree (language_mask);

CREATE INDEX ezcobj_state_priority ON ezcobj_state USING btree (priority);

CREATE UNIQUE INDEX ezcobj_state_group_identifier ON ezcobj_state_group USING btree (identifier);

CREATE INDEX ezcobj_state_group_lmask ON ezcobj_state_group USING btree (language_mask);

CREATE INDEX ezcontent_language_name ON ezcontent_language USING btree (name);

CREATE INDEX ezcontentclass_version ON ezcontentclass USING btree ("version");

CREATE INDEX ezcontentclass_identifier ON ezcontentclass USING btree (identifier, "version");

CREATE INDEX ezcontentclass_attr_ccid ON ezcontentclass_attribute USING btree (contentclass_id);

CREATE INDEX ezcontentobject_classid ON ezcontentobject USING btree (contentclass_id);

CREATE INDEX ezcontentobject_currentversion ON ezcontentobject USING btree (current_version);

CREATE INDEX ezcontentobject_lmask ON ezcontentobject USING btree (language_mask);

CREATE INDEX ezcontentobject_owner ON ezcontentobject USING btree (owner_id);

CREATE INDEX ezcontentobject_pub ON ezcontentobject USING btree (published);

CREATE UNIQUE INDEX ezcontentobject_remote_id ON ezcontentobject USING btree (remote_id);

CREATE INDEX ezcontentobject_status ON ezcontentobject USING btree (status);

CREATE INDEX ezcontentobject_attribute_co_id_ver_lang_code ON ezcontentobject_attribute USING btree (contentobject_id, "version", language_code);

CREATE INDEX ezcontentobject_attribute_language_code ON ezcontentobject_attribute USING btree (language_code);

CREATE INDEX ezcontentobject_classattr_id ON ezcontentobject_attribute USING btree (contentclassattribute_id); 

CREATE INDEX sort_key_int ON ezcontentobject_attribute USING btree (sort_key_int);

CREATE INDEX sort_key_string ON ezcontentobject_attribute USING btree (sort_key_string);

CREATE INDEX ezco_link_from ON ezcontentobject_link USING btree (from_contentobject_id, from_contentobject_version, contentclassattribute_id);

CREATE INDEX ezco_link_to_co_id ON ezcontentobject_link USING btree (to_contentobject_id);

CREATE INDEX ezcontentobject_name_cov_id ON ezcontentobject_name USING btree (content_version);

CREATE INDEX ezcontentobject_name_lang_id ON ezcontentobject_name USING btree (language_id);

CREATE INDEX ezcontentobject_name_name ON ezcontentobject_name USING btree (name);

CREATE INDEX ezcobj_trash_co_id ON ezcontentobject_trash USING btree (contentobject_id);

CREATE INDEX ezcobj_trash_depth ON ezcontentobject_trash USING btree (depth);

CREATE INDEX ezcobj_trash_modified_subnode ON ezcontentobject_trash USING btree (modified_subnode);

CREATE INDEX ezcobj_trash_p_node_id ON ezcontentobject_trash USING btree (parent_node_id);

CREATE INDEX ezcobj_trash_path ON ezcontentobject_trash USING btree (path_string);

CREATE INDEX ezcobj_trash_path_ident ON ezcontentobject_trash USING btree (path_identification_string);

CREATE INDEX ezcontentobject_tree_remote_id ON ezcontentobject_tree USING btree (remote_id);

CREATE INDEX ezcontentobject_tree_co_id ON ezcontentobject_tree USING btree (contentobject_id);

CREATE INDEX ezcontentobject_tree_depth ON ezcontentobject_tree USING btree (depth);

CREATE INDEX ezcontentobject_tree_p_node_id ON ezcontentobject_tree USING btree (parent_node_id);

CREATE INDEX ezcontentobject_tree_path ON ezcontentobject_tree USING btree (path_string);

CREATE INDEX ezcontentobject_tree_path_ident ON ezcontentobject_tree USING btree (path_identification_string);

CREATE INDEX modified_subnode ON ezcontentobject_tree USING btree (modified_subnode);

CREATE INDEX ezcobj_version_creator_id ON ezcontentobject_version USING btree (creator_id);

CREATE INDEX ezcobj_version_status ON ezcontentobject_version USING btree (status);

CREATE INDEX idx_object_version_objver ON ezcontentobject_version USING btree (contentobject_id, "version");

CREATE INDEX ezcontentobject_version_object_status ON ezcontentobject_version USING btree (contentobject_id, status);

CREATE INDEX eznode_assignment_co_version ON eznode_assignment USING btree (contentobject_version);

CREATE INDEX eznode_assignment_coid_cov ON eznode_assignment USING btree (contentobject_id, contentobject_version);

CREATE INDEX eznode_assignment_is_main ON eznode_assignment USING btree (is_main);

CREATE INDEX eznode_assignment_parent_node ON eznode_assignment USING btree (parent_node);

CREATE INDEX ezpolicy_original_id ON ezpolicy USING btree (original_id);

CREATE INDEX ezpolicy_role_id ON ezpolicy USING btree (role_id);

CREATE INDEX policy_id ON ezpolicy_limitation USING btree (policy_id);

CREATE INDEX ezpolicy_limitation_value_val ON ezpolicy_limitation_value USING btree (value);

CREATE INDEX ezpolicy_limitation_value_limitation_id ON ezpolicy_limitation_value USING btree (limitation_id);

CREATE INDEX ezsearch_object_word_link_frequency ON ezsearch_object_word_link USING btree (frequency);

CREATE INDEX ezsearch_object_word_link_identifier ON ezsearch_object_word_link USING btree (identifier);

CREATE INDEX ezsearch_object_word_link_integer_value ON ezsearch_object_word_link USING btree (integer_value);

CREATE INDEX ezsearch_object_word_link_object ON ezsearch_object_word_link USING btree (contentobject_id);

CREATE INDEX ezsearch_object_word_link_word ON ezsearch_object_word_link USING btree (word_id);

CREATE INDEX ezsearch_word_obj_count ON ezsearch_word USING btree (object_count);

CREATE INDEX ezsearch_word_word_i ON ezsearch_word USING btree (word);

CREATE INDEX ezurl_url ON ezurl USING btree (url);

CREATE INDEX ezurl_ol_coa_id ON ezurl_object_link USING btree (contentobject_attribute_id);

CREATE INDEX ezurl_ol_coa_version ON ezurl_object_link USING btree (contentobject_attribute_version);

CREATE INDEX ezurl_ol_url_id ON ezurl_object_link USING btree (url_id);

CREATE INDEX ezurlalias_desturl ON ezurlalias USING btree (destination_url);

CREATE INDEX ezurlalias_forward_to_id ON ezurlalias USING btree (forward_to_id);

CREATE INDEX ezurlalias_imp_wcard_fwd ON ezurlalias USING btree (is_imported, is_wildcard, forward_to_id);

CREATE INDEX ezurlalias_source_md5 ON ezurlalias USING btree (source_md5);

CREATE INDEX ezurlalias_source_url ON ezurlalias USING btree (source_url);

CREATE INDEX ezurlalias_wcard_fwd ON ezurlalias USING btree (is_wildcard, forward_to_id);

CREATE INDEX ezurlalias_ml_act_org ON ezurlalias_ml USING btree ("action", is_original);

CREATE INDEX ezurlalias_ml_actt_org_al ON ezurlalias_ml USING btree (action_type, is_original, is_alias);

CREATE INDEX ezurlalias_ml_id ON ezurlalias_ml USING btree (id);

CREATE INDEX ezurlalias_ml_par_act_id_lnk ON ezurlalias_ml USING btree ("action", id, link, parent);

CREATE INDEX ezurlalias_ml_par_lnk_txt ON ezurlalias_ml USING btree (parent, text, link);

CREATE INDEX ezurlalias_ml_text ON ezurlalias_ml USING btree (text, id, link);

CREATE INDEX ezurlalias_ml_text_lang ON ezurlalias_ml USING btree (text, lang_mask, parent);

CREATE UNIQUE INDEX ezuser_login ON ezuser USING btree ((lower(login)));

CREATE INDEX hash_key ON ezuser_accountkey USING btree (hash_key);

CREATE INDEX ezuser_role_contentobject_id ON ezuser_role USING btree (contentobject_id);

CREATE INDEX ezuser_role_role_id ON ezuser_role USING btree (role_id);

CREATE INDEX ezuservisit_co_visit_count ON ezuservisit USING btree (current_visit_timestamp, login_count);

CREATE INDEX ezkeyword_keyword ON ezkeyword USING btree (keyword);

CREATE INDEX ezkeyword_attr_link_kid_oaid ON ezkeyword_attribute_link USING btree (keyword_id,objectattribute_id);

CREATE INDEX ezkeyword_attr_link_oaid ON ezkeyword_attribute_link USING btree (objectattribute_id);

CREATE INDEX ezuser_accountkey_hash_key ON ezuser_accountkey USING btree (hash_key);

ALTER TABLE ONLY ezcobj_state
    ADD CONSTRAINT ezcobj_state_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezimagefile
    ADD CONSTRAINT ezimagefile_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezgmaplocation
    ADD CONSTRAINT ezgmaplocation_pkey PRIMARY KEY (contentobject_attribute_id,contentobject_version);

ALTER TABLE ONLY ezbinaryfile
    ADD CONSTRAINT ezbinaryfile_pkey PRIMARY KEY (contentobject_attribute_id, "version");

ALTER TABLE ONLY ezmedia
    ADD CONSTRAINT ezmedia_pkey PRIMARY KEY (contentobject_attribute_id, "version");

ALTER TABLE ONLY ezcobj_state_group
    ADD CONSTRAINT ezcobj_state_group_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezcobj_state_group_language
    ADD CONSTRAINT ezcobj_state_group_language_pkey PRIMARY KEY (contentobject_state_group_id, real_language_id);

ALTER TABLE ONLY ezcobj_state_language
    ADD CONSTRAINT ezcobj_state_language_pkey PRIMARY KEY (contentobject_state_id, language_id);

ALTER TABLE ONLY ezcobj_state_link
    ADD CONSTRAINT ezcobj_state_link_pkey PRIMARY KEY (contentobject_id,contentobject_state_id);

ALTER TABLE ONLY ezcontent_language
    ADD CONSTRAINT ezcontent_language_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezcontentclass
    ADD CONSTRAINT ezcontentclass_pkey PRIMARY KEY (id, "version");

ALTER TABLE ONLY ezcontentclass_attribute
    ADD CONSTRAINT ezcontentclass_attribute_pkey PRIMARY KEY (id, "version");

ALTER TABLE ONLY ezcontentclass_classgroup
    ADD CONSTRAINT ezcontentclass_classgroup_pkey PRIMARY KEY (contentclass_id, contentclass_version, group_id);

ALTER TABLE ONLY ezcontentclass_name
    ADD CONSTRAINT ezcontentclass_name_pkey PRIMARY KEY (contentclass_id, contentclass_version, language_id);

ALTER TABLE ONLY ezcontentclassgroup
    ADD CONSTRAINT ezcontentclassgroup_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezcontentobject
    ADD CONSTRAINT ezcontentobject_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezcontentobject_attribute
    ADD CONSTRAINT ezcontentobject_attribute_pkey PRIMARY KEY (id, "version");

ALTER TABLE ONLY ezcontentobject_link
    ADD CONSTRAINT ezcontentobject_link_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezcontentobject_name
    ADD CONSTRAINT ezcontentobject_name_pkey PRIMARY KEY (contentobject_id, content_version, content_translation);

ALTER TABLE ONLY ezcontentobject_trash
    ADD CONSTRAINT ezcontentobject_trash_pkey PRIMARY KEY (node_id);

ALTER TABLE ONLY ezcontentobject_tree
    ADD CONSTRAINT ezcontentobject_tree_pkey PRIMARY KEY (node_id);

ALTER TABLE ONLY ezcontentobject_version
    ADD CONSTRAINT ezcontentobject_version_pkey PRIMARY KEY (id);

ALTER TABLE ONLY eznode_assignment
    ADD CONSTRAINT eznode_assignment_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezpolicy
    ADD CONSTRAINT ezpolicy_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezpolicy_limitation
    ADD CONSTRAINT ezpolicy_limitation_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezpolicy_limitation_value
    ADD CONSTRAINT ezpolicy_limitation_value_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezrole
    ADD CONSTRAINT ezrole_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezsearch_object_word_link
    ADD CONSTRAINT ezsearch_object_word_link_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezsearch_word
    ADD CONSTRAINT ezsearch_word_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezsection
    ADD CONSTRAINT ezsection_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezurl
    ADD CONSTRAINT ezurl_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezurlalias
    ADD CONSTRAINT ezurlalias_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezurlalias_ml
    ADD CONSTRAINT ezurlalias_ml_pkey PRIMARY KEY (parent, text_md5);

ALTER TABLE ONLY ezurlalias_ml_incr
    ADD CONSTRAINT ezurlalias_ml_incr_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezurlwildcard
    ADD CONSTRAINT ezurlwildcard_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezuser
    ADD CONSTRAINT ezuser_pkey PRIMARY KEY (contentobject_id);

ALTER TABLE ONLY ezuser_accountkey
    ADD CONSTRAINT ezuser_accountkey_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezuser_role
    ADD CONSTRAINT ezuser_role_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezuser_setting
    ADD CONSTRAINT ezuser_setting_pkey PRIMARY KEY (user_id);

ALTER TABLE ONLY ezuservisit
    ADD CONSTRAINT ezuservisit_pkey PRIMARY KEY (user_id);

ALTER TABLE ONLY ezkeyword
    ADD CONSTRAINT ezkeyword_pkey PRIMARY KEY (id);

ALTER TABLE ONLY ezkeyword_attribute_link
    ADD CONSTRAINT ezkeyword_attribute_link_pkey PRIMARY KEY (id);
