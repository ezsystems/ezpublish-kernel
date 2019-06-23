<?php

/**
 * File containing the abstract FieldFilterBase criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;

/**
 * Abstract criterion visitor implementation, providing field filters condition for criteria
 * that require it (Field, CustomField, FullText, MapLocationDistance).
 */
abstract class FieldFilterBase extends CriterionVisitor
{
    /**
     * Returns filter condition for the given $languageFilter.
     *
     * Filter is to be used by both query and filter visiting, as it should not impact scoring.
     * Null will be returned if nothing is found to be filtered.
     *
     * @param array $languageFilter
     *
     * @return array|null
     */
    protected function getFieldFilter(array $languageFilter)
    {
        $filter = null;

        // Only 'languages' and 'useAlwaysAvailable' are available,
        // latter making sense only when former is set.
        if (!empty($languageFilter['languages'])) {
            // For 'terms' filter caching is enabled by default
            $filter = [
                'terms' => [
                    'fields_doc.meta_language_code_s' => $languageFilter['languages'],
                ],
            ];

            if (!isset($languageFilter['useAlwaysAvailable']) || $languageFilter['useAlwaysAvailable'] === true) {
                $filter = [
                    'or' => [
                        // Enabling caching requires filters to be wrapped in 'filters' element
                        'filters' => [
                            $filter,
                            [
                                'term' => [
                                    'meta_is_always_available_b' => true,
                                ],
                            ],
                        ],
                        // For 'or' filter caching is disabled by default
                        // We enable it as it should be heavily reused
                        '_cache' => true,
                    ],
                ];
            }
        }

        return $filter;
    }

    /**
     * TODO: should really work something like this, but also
     * needs update to the UrlAliasService etc.
     *
     * @param array $languageFilter
     *
     * @return array|null
     */
    protected function getTodoFieldFilter(array $languageFilter)
    {
        $translationFilter = null;

        if (!empty($languageFilter['languages'])) {
            $translationFilter = [
                'terms' => [
                    'fields_doc.meta_language_code_s' => $languageFilter['languages'],
                ],
            ];

            if (isset($languageFilter['defaultTranslationToMainLanguage'])) {
                switch ($languageFilter['defaultTranslationToMainLanguage']) {
                    case true:
                        $translationFilter = [
                            'or' => [
                                $translationFilter,
                                'term' => [
                                    'meta_is_main_translation_b' => true,
                                ],
                            ],
                        ];
                        break;

                    case 'use_always_available':
                        $translationFilter = [
                            'or' => [
                                $translationFilter,
                                'and' => [
                                    [
                                        'term' => [
                                            'always_available_b' => true,
                                        ],
                                    ],
                                    [
                                        'term' => [
                                            'meta_is_main_translation_b' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ];
                        break;

                    case false:
                        // Nothing to do
                        break;

                    default:
                        throw new \RuntimeException(
                            "Invalid value for 'defaultTranslationToMainLanguage' field filter: expected one of: " .
                            "true, 'use_always_available', false, got: " .
                            var_export($languageFilter['defaultTranslationToMainLanguage'], true)
                        );
                }
            }
        }

        return $translationFilter;
    }
}
