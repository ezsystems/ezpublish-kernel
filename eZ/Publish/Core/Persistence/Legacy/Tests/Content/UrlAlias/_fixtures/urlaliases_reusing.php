<?php

return array (
    'ezurlalias_ml' => array (
        0 => array (
            'action' => 'eznode:2',
            'action_type' => 'eznode',
            'alias_redirects' => '1',
            'id' => '1',
            'is_alias' => '0',
            'is_original' => '1',
            'lang_mask' => '4',
            'link' => '1',
            'parent' => '0',
            'text' => '',
            'text_md5' => 'd41d8cd98f00b204e9800998ecf8427e',
        ),
        1 => array (
            'action' => 'nop:',
            'action_type' => 'nop',
            'alias_redirects' => '1',
            'id' => '2',
            'is_alias' => '0',
            'is_original' => '0',
            'lang_mask' => '1',
            'link' => '2',
            'parent' => '0',
            'text' => 'nop-element',
            'text_md5' => 'de55c2fff721217cc4cb67b58dc35f85',
        ),
        2 => array (
            'action' => 'module:content/search',
            'action_type' => 'module',
            'alias_redirects' => '0',
            'id' => '3',
            'is_alias' => '1',
            'is_original' => '1',
            'lang_mask' => '4',
            'link' => '3',
            'parent' => '2',
            'text' => 'search',
            'text_md5' => '06a943c59f33a34bb5924aaf72cd2995',
        ),
        3 => array (
            'action' => 'eznode:314',
            'action_type' => 'eznode',
            'alias_redirects' => '1',
            'id' => '4',
            'is_alias' => '1',
            'is_original' => '1',
            'lang_mask' => '4',
            'link' => '4',
            'parent' => '0',
            'text' => 'hello',
            'text_md5' => '5d41402abc4b2a76b9719d911017c592',
        ),
    ),
    'ezcontentobject_tree' => array(
        0 => array(
            'node_id' => 1,
            'parent_node_id' => 1,
            'path_string' => '',
            'remote_id' => '',
        ),
        1 => array(
            'node_id' => 2,
            'parent_node_id' => 1,
            'path_string' => '',
            'remote_id' => '',
        ),
        2 => array(
            'node_id' => 314,
            'parent_node_id' => 2,
            'path_string' => '',
            'remote_id' => '',
        ),
    ),
    'ezcontent_language' => array (
        0 => array(
            'disabled' => 0,
            'id' => 2,
            'locale' => 'cro-HR',
            'name' => 'Croatian (Hrvatski)'
        ),
        1 => array(
            'disabled' => 0,
            'id' => 4,
            'locale' => 'eng-GB',
            'name' => 'English (United Kingdom)'
        ),
    ),
    'ezurlalias_ml_incr' => array (
        0 => array (
            'id' => '1',
        ),
        1 => array (
            'id' => '2',
        ),
        2 => array (
            'id' => '3',
        ),
        3 => array (
            'id' => '4',
        ),
    ),
);
