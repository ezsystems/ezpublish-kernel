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
            'action' => 'module:ezinfo/isalive',
            'action_type' => 'module',
            'alias_redirects' => '1',
            'id' => '2',
            'is_alias' => '1',
            'is_original' => '1',
            'lang_mask' => '5',
            'link' => '2',
            'parent' => '0',
            'text' => 'is-alive',
            'text_md5' => 'd003895fa282a14c8ec3eddf23ca4ca2',
        ),
        2 => array (
            'action' => 'nop:',
            'action_type' => 'nop',
            'alias_redirects' => '1',
            'id' => '3',
            'is_alias' => '0',
            'is_original' => '0',
            'lang_mask' => '1',
            'link' => '3',
            'parent' => '2',
            'text' => 'then',
            'text_md5' => '0e5243d9965540f62aac19a985f3f33e',
        ),
        3 => array (
            'action' => 'module:content/search',
            'action_type' => 'module',
            'alias_redirects' => '0',
            'id' => '4',
            'is_alias' => '1',
            'is_original' => '1',
            'lang_mask' => '4',
            'link' => '4',
            'parent' => '3',
            'text' => 'search',
            'text_md5' => '06a943c59f33a34bb5924aaf72cd2995',
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
