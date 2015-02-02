<?php
/**
 * File containing the abstract FieldFilterBase criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;

/**
 * Abstract criterion visitor implementation, providing field filters condition for criteria
 * that require it (Field, CustomField, FullText, MapLocationDistance).
 */
abstract class FieldFilterBase extends CriterionVisitor
{
    /**
     * Returns filter condition for the given $fieldFilters.
     *
     * Filter is to be used by both query and filter visiting, as it should not impact scoring.
     * Null will be returned if nothing is found to be filtered.
     *
     * @param array $fieldFilters
     *
     * @return array|null
     */
    protected function getFieldFilter( array $fieldFilters )
    {
        $filter = null;

        // Only 'languages' and 'useAlwaysAvailable' are available,
        // latter making sense only when former is set.
        if ( !empty( $fieldFilters["languages"] ) )
        {
            // For 'terms' filter caching is enabled by default
            $filter = array(
                "terms" => array(
                    "fields_doc.meta_language_code_s" => $fieldFilters["languages"],
                ),
            );

            if ( isset( $fieldFilters["useAlwaysAvailable"] ) && $fieldFilters["useAlwaysAvailable"] === true )
            {
                $filter = array(
                    "or" => array(
                        // Enabling caching requires filters to be wrapped in 'filters' element
                        "filters" => array(
                            $filter,
                            array(
                                "term" => array(
                                    "meta_is_always_available_b" => true,
                                ),
                            ),
                        ),
                        // For 'or' filter caching is disabled by default
                        // We enable it as it should be heavily reused
                        "_cache" => true,
                    ),
                );
            }
        }

        return $filter;
    }

    /**
     * TODO: should really work something like this, but also
     * needs update to the UrlAliasService etc
     *
     * @param array $fieldFilters
     *
     * @return array|null
     */
    protected function getTodoFieldFilter( array $fieldFilters )
    {
        $translationFilter = null;

        if ( !empty( $fieldFilters["languages"] ) )
        {
            $translationFilter = array(
                "terms" => array(
                    "fields_doc.meta_language_code_s" => $fieldFilters["languages"],
                ),
            );

            if ( isset( $fieldFilters["defaultTranslationToMainLanguage"] ) )
            {
                switch ( $fieldFilters["defaultTranslationToMainLanguage"] )
                {
                    case true:
                        $translationFilter = array(
                            "or" => array(
                                $translationFilter,
                                "term" => array(
                                    "meta_is_main_translation_b" => true,
                                ),
                            ),
                        );
                        break;

                    case "use_always_available":
                        $translationFilter = array(
                            "or" => array(
                                $translationFilter,
                                "and" => array(
                                    array(
                                        "term" => array(
                                            "always_available_b" => true,
                                        ),
                                    ),
                                    array(
                                        "term" => array(
                                            "meta_is_main_translation_b" => true,
                                        ),
                                    ),
                                ),
                            ),
                        );
                        break;

                    case false:
                        // Nothing to do
                        break;

                    default:
                        throw new \RuntimeException(
                            "Invalid value for 'defaultTranslationToMainLanguage' field filter: expected one of: " .
                            "true, 'use_always_available', false, got: " .
                            var_export( $fieldFilters["defaultTranslationToMainLanguage"], true )
                        );
                }
            }
        }

        return $translationFilter;
    }
}
