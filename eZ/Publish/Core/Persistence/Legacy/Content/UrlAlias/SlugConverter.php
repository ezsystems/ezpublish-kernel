<?php

/**
 * File containing the SlugConverter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

use eZ\Publish\Core\Persistence\TransformationProcessor;

/**
 * URL slug converter.
 */
class SlugConverter
{
    const DEFAULT_CONFIGURATION = [
        'wordSeparatorName' => 'dash',
        'urlAliasNameLimit' => 255,
        'transformation' => 'urlalias',
        'transformationGroups' => [
            'urlalias' => [
                'commands' => [
                    //normalize
                    'space_normalize',
                    'hyphen_normalize',
                    'apostrophe_normalize',
                    'doublequote_normalize',
                    'greek_normalize',
                    'endline_search_normalize',
                    'tab_search_normalize',
                    'specialwords_search_normalize',

                    //transform
                    'apostrophe_to_doublequote',
                    'math_to_ascii',
                    'inverted_to_normal',

                    //decompose
                    'special_decompose',
                    'latin_search_decompose',

                    //transliterate
                    'cyrillic_transliterate_ascii',
                    'greek_transliterate_ascii',
                    'hebrew_transliterate_ascii',
                    'latin1_transliterate_ascii',
                    'latin-exta_transliterate_ascii',

                    //diacritical
                    'cyrillic_diacritical',
                    'greek_diacritical',
                    'latin1_diacritical',
                    'latin-exta_diacritical',
                ],
                'cleanupMethod' => 'url_cleanup',
            ],
            'urlalias_iri' => [
                'commands' => [],
                'cleanupMethod' => 'url_cleanup_iri',
            ],
            'urlalias_compat' => [
                'commands' => [
                    //normalize
                    'space_normalize',
                    'hyphen_normalize',
                    'apostrophe_normalize',
                    'doublequote_normalize',
                    'greek_normalize',
                    'endline_search_normalize',
                    'tab_search_normalize',
                    'specialwords_search_normalize',

                    //transform
                    'apostrophe_to_doublequote',
                    'math_to_ascii',
                    'inverted_to_normal',

                    //decompose
                    'special_decompose',
                    'latin_search_decompose',

                    //transliterate
                    'cyrillic_transliterate_ascii',
                    'greek_transliterate_ascii',
                    'hebrew_transliterate_ascii',
                    'latin1_transliterate_ascii',
                    'latin-exta_transliterate_ascii',

                    //diacritical
                    'cyrillic_diacritical',
                    'greek_diacritical',
                    'latin1_diacritical',
                    'latin-exta_diacritical',

                    //lowercase
                    'ascii_lowercase',
                    'cyrillic_lowercase',
                    'greek_lowercase',
                    'latin1_lowercase',
                    'latin-exta_lowercase',
                    'latin_lowercase',
                ],
                'cleanupMethod' => 'url_cleanup_compat',
            ],
            'urlalias_lowercase' => [
                'commands' => [
                    'ascii_lowercase',
                    'cyrillic_lowercase',
                    'greek_lowercase',
                    'latin1_lowercase',
                    'latin-exta_lowercase',
                    'latin_lowercase',

                    'space_normalize',
                    'hyphen_normalize',
                    'apostrophe_normalize',
                    'doublequote_normalize',
                    'greek_normalize',
                    'endline_search_normalize',
                    'tab_search_normalize',
                    'specialwords_search_normalize',

                    'apostrophe_to_doublequote',
                    'math_to_ascii',
                    'inverted_to_normal',

                    'special_decompose',
                    'latin_search_decompose',

                    'cyrillic_transliterate_ascii',
                    'greek_transliterate_ascii',
                    'hebrew_transliterate_ascii',
                    'latin1_transliterate_ascii',
                    'latin-exta_transliterate_ascii',

                    'cyrillic_diacritical',
                    'greek_diacritical',
                    'latin1_diacritical',
                    'latin-exta_diacritical',
                ],
                'cleanupMethod' => 'url_cleanup',
            ],
        ],
        'reservedNames' => [
            'class',
            'collaboration',
            'content',
            'error',
            'ezinfo',
            'infocollector',
            'layout',
            'notification',
            'oauth',
            'oauthadmin',
            'package',
            'pdf',
            'role',
            'rss',
            'search',
            'section',
            'settings',
            'setup',
            'shop',
            'state',
            'trigger',
            'url',
            'user',
            'visual',
            'workflow',
            'switchlanguage',
        ],
    ];

    /**
     * Transformation processor to normalize URL strings.
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $transformationProcessor;

    /** @var array */
    protected $configuration;

    /**
     * Creates a new URL slug converter.
     *
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor
     * @param array $configuration
     */
    public function __construct(
        TransformationProcessor $transformationProcessor,
        array $configuration = []
    ) {
        $this->transformationProcessor = $transformationProcessor;
        $this->configuration = $configuration + self::DEFAULT_CONFIGURATION;
    }

    /**
     * Converts given $text into a URL slug consisting of URL valid characters.
     * For non-Unicode setups this means character in the range a-z, numbers and _, for Unicode
     * setups it means all characters except space, &, ;, /, :, =, ?, [, ], (, ), -.
     *
     * Invalid characters are converted to -.
     *
     * Example with a non-Unicode setup
     *
     * 'My car' => 'My-car'
     * 'What is this?' => 'What-is-this'
     * 'This & that' => 'This-that'
     * 'myfile.tpl' => 'Myfile-tpl',
     * 'øæå' => 'oeaeaa'
     *
     * @param string $text
     * @param string $defaultText
     * @param string|null $transformation
     *
     * @return string
     */
    public function convert($text, $defaultText = '_1', $transformation = null)
    {
        if (!isset($transformation)) {
            $transformation = $this->configuration['transformation'];
        }

        if (strlen($text) === 0) {
            $text = $defaultText;
        }

        if (isset($this->configuration['transformationGroups'][$transformation]['commands'])
            && !empty($this->configuration['transformationGroups'][$transformation]['commands'])
        ) {
            $text = $this->transformationProcessor->transform(
                $text,
                $this->configuration['transformationGroups'][$transformation]['commands']
            );
        }

        return $this->cleanupText(
            $text,
            $this->configuration['transformationGroups'][$transformation]['cleanupMethod']
        );
    }

    /**
     * Returns unique counter number that is appended to the path element in order to make it unique
     * against system reserved names and other entries on the same level.
     *
     * Comparison is done only if given $isRootLevel equals to true (it does by default), meaning that
     * entry is at first level of URL.
     * In a case when reserved name is matched method will return 2.
     * When given $isRootLevel equals to false or when there is no match with reserved names this will
     * return 1, which is default value not appended to name.
     *
     * Note: this is used only when publishing URL aliases, when creating global and custom aliases user
     * is allowed to create first level entries that collide with reserved names. Also, in actual creation
     * of the alias name will be further checked against existing elements under the same parent, using
     * unique counter value determined here as starting unique counter value.
     *
     * @param string $text
     * @param bool $isRootLevel
     *
     * @return int
     */
    public function getUniqueCounterValue($text, $isRootLevel = true)
    {
        if ($isRootLevel) {
            foreach ($this->configuration['reservedNames'] as $reservedName) {
                // Case insensitive comparison
                if (strcasecmp($text, $reservedName) === 0) {
                    return 2;
                }
            }
        }

        return 1;
    }

    /**
     * Cleans up $text using given $method.
     *
     * @param string $text
     * @param string $method
     *
     * @return string
     */
    protected function cleanupText($text, $method)
    {
        switch ($method) {
            case 'url_cleanup':
                $sep = $this->getWordSeparator();
                $sepQ = preg_quote($sep);
                $text = preg_replace(
                    [
                        '#[^a-zA-Z0-9_!.-]+#',
                        '#^[.]+|[!.]+$#', // Remove dots at beginning/end
                        "#\.\.+#", // Remove double dots
                        "#[{$sepQ}]+#", // Turn multiple separators into one
                        "#^[{$sepQ}]+|[{$sepQ}]+$#", // Strip separator from beginning/end
                    ],
                    [
                        $sep,
                        $sep,
                        $sep,
                        $sep,
                        '',
                    ],
                    $text
                );
                break;
            case 'url_cleanup_iri':
                // With IRI support we keep all characters except some reserved ones,
                // they are space, ampersand, semi-colon, forward slash, colon, equal sign, question mark,
                //          square brackets, parenthesis, plus.
                //
                // Note: Space is turned into a dash to make it easier for people to
                //       paste urls from the system and have the whole url recognized
                //       instead of being broken off
                $sep = $this->getWordSeparator();
                $sepQ = preg_quote($sep);
                $prepost = ' .' . $sepQ;
                if ($sep != '-') {
                    $prepost .= '-';
                }
                $text = preg_replace(
                    [
                        "#[ \\\\%\#&;/:=?\[\]()+]+#",
                        '#^[.]+|[!.]+$#', // Remove dots at beginning/end
                        "#\.\.+#", // Remove double dots
                        "#[{$sepQ}]+#", // Turn multiple separators into one
                        "#^[{$prepost}]+|[{$prepost}]+$#",
                    ],
                    [
                        $sep,
                        $sep,
                        $sep,
                        $sep,
                        '',
                    ],
                    $text
                );
                break;
            case 'url_cleanup_compat':
                // Old style of url alias with lowercase only and underscores for separators
                $text = strtolower($text);
                $text = preg_replace(
                    [
                        '#[^a-z0-9]+#',
                        '#^_+|_+$#',
                    ],
                    [
                        '_',
                        '',
                    ],
                    $text
                );
                break;
            default:
                // Nothing
        }

        return $text;
    }

    /**
     * Returns word separator value.
     *
     * @return string
     */
    protected function getWordSeparator()
    {
        switch ($this->configuration['wordSeparatorName']) {
            case 'dash':
                return '-';
            case 'underscore':
                return '_';
            case 'space':
                return ' ';
        }

        return '-';
    }
}
