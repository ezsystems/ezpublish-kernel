<?php

return array(
    'ezurlalias_ml' => array(
        0 => array(
            'action' => 'eznode:2',
            'action_type' => 'eznode',
            'alias_redirects' => '1',
            'id' => '1',
            'is_alias' => '0',
            'is_original' => '1',
            'lang_mask' => '3',
            'link' => '1',
            'parent' => '0',
            'text' => '',
            'text_md5' => 'd41d8cd98f00b204e9800998ecf8427e',
        ),
        1 => array(
            'action' => 'eznode:314',
            'action_type' => 'eznode',
            'alias_redirects' => '1',
            'id' => '2',
            'is_alias' => '0',
            'is_original' => '1',
            'lang_mask' => '2',
            'link' => '2',
            'parent' => '0',
            'text' => 'jedan',
            'text_md5' => '6896260129051a949051c3847c34466f',
        ),
        2 => array(
            'action' => 'eznode:315',
            'action_type' => 'eznode',
            'alias_redirects' => '1',
            'id' => '3',
            'is_alias' => '0',
            'is_original' => '1',
            'lang_mask' => '2',
            'link' => '3',
            'parent' => '0',
            'text' => 'dva',
            'text_md5' => 'c67ed9a09ab136fae610b6a087d82e21',
        ),
        3 => array(
            'action' => 'eznode:316',
            'action_type' => 'eznode',
            'alias_redirects' => '1',
            'id' => '4',
            'is_alias' => '0',
            'is_original' => '1',
            'lang_mask' => '2',
            'link' => '4',
            'parent' => '2',
            'text' => 'swap-this',
            'text_md5' => '21940df6bebbfc9501b3b512640dffe5',
        ),
        4 => array(
            'action' => 'eznode:317',
            'action_type' => 'eznode',
            'alias_redirects' => '1',
            'id' => '5',
            'is_alias' => '0',
            'is_original' => '1',
            'lang_mask' => '2',
            'link' => '5',
            'parent' => '3',
            'text' => 'swap-that',
            'text_md5' => 'b8d555a5436774b6d3a035a4437ea37c',
        ),
        5 => array(
            'action' => 'nop:',
            'action_type' => 'nop',
            'alias_redirects' => '1',
            'id' => '6',
            'is_alias' => '0',
            'is_original' => '0',
            'lang_mask' => '1',
            'link' => '6',
            'parent' => '2',
            'text' => 'swap-that',
            'text_md5' => 'b8d555a5436774b6d3a035a4437ea37c',
        ),
        6 => array(
            'action' => 'module:content/search',
            'action_type' => 'module',
            'alias_redirects' => '1',
            'id' => '7',
            'is_alias' => '1',
            'is_original' => '1',
            'lang_mask' => '2',
            'link' => '7',
            'parent' => '6',
            'text' => 'search',
            'text_md5' => '06a943c59f33a34bb5924aaf72cd2995',
        ),
    ),
    'ezcontent_language' => array(
        0 => array(
            'disabled' => 0,
            'id' => 2,
            'locale' => 'cro-HR',
            'name' => 'Croatian (Hrvatski)'
        ),
    ),
    'ezurlalias_ml_incr' => array(
        0 => array(
            'id' => '1',
        ),
        1 => array(
            'id' => '2',
        ),
        2 => array(
            'id' => '3',
        ),
        3 => array(
            'id' => '4',
        ),
        4 => array(
            'id' => '5',
        ),
        5 => array(
            'id' => '6',
        ),
        6 => array(
            'id' => '7',
        ),
    ),
    'ezcontentobject_tree' => array(
        0 => array(
            'node_id' => 314,
            'main_node_id' => 314,
            'parent_node_id' => 2,
            'path_string' => '',
            'path_identification_string' => '',
            'remote_id' => '',
            'contentobject_id' => 1,
        ),
        1 => array(
            'node_id' => 315,
            'main_node_id' => 315,
            'parent_node_id' => 2,
            'path_string' => '',
            'path_identification_string' => '',
            'remote_id' => '',
            'contentobject_id' => 2,
        ),
        2 => array(
            'node_id' => 316,
            'main_node_id' => 316,
            'parent_node_id' => 314,
            'path_string' => '',
            'path_identification_string' => '',
            'remote_id' => '',
            'contentobject_id' => 3,
        ),
        3 => array(
            'node_id' => 317,
            'main_node_id' => 317,
            'parent_node_id' => 315,
            'path_string' => '',
            'path_identification_string' => '',
            'remote_id' => '',
            'contentobject_id' => 4,
        ),
    ),
    'ezcontentobject' => array(
        0 => array(
            'id' => 3,
            'initial_language_id' => 2,
            'current_version' => 1,
        ),
        1 => array(
            'id' => 4,
            'initial_language_id' => 2,
            'current_version' => 1,
        ),
    ),
    'ezcontentobject_name' => [
        0 => [
            'contentobject_id' => 3,
            'content_version' => 1,
            'name' => 'swap that',
            'content_translation' => 'cro-HR',
        ],
        1 => [
            'contentobject_id' => 4,
            'content_version' => 1,
            'name' => 'swap this',
            'content_translation' => 'cro-HR',
        ],
    ],
);
