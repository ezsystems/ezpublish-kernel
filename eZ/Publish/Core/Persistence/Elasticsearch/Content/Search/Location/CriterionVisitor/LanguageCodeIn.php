<?php
/**
 * File containing the LanguageCodeIn criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location\CriterionVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the LanguageCode criterion
 */
class LanguageCodeIn extends CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return
            $criterion instanceof Criterion\LanguageCode &&
            (
                ( $criterion->operator ?: Operator::IN ) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Elasticsearch filter representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher $dispatcher
     *
     * @return string
     */
    public function visitFilter( Criterion $criterion, Dispatcher $dispatcher = null )
    {
        if ( count( $criterion->value ) > 1 )
        {
            $filter = array(
                "terms" => array(
                    "content_language_code_ms" => $criterion->value,
                ),
            );
        }
        else
        {
            $filter = array(
                "term" => array(
                    "content_language_code_ms" => $criterion->value[0],
                ),
            );
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode $criterion */
        if ( $criterion->matchAlwaysAvailable )
        {
            $filter = array(
                "or" => array(
                    $filter,
                    array(
                        "term" => array(
                            "content_always_available_b" => true,
                        ),
                    ),
                ),
            );
        }

        return $filter;
    }

    /**
     * Map field value to a proper Elasticsearch query representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher $dispatcher
     *
     * @return string
     */
    public function visitQuery( Criterion $criterion, Dispatcher $dispatcher = null )
    {
        return $this->visitFilter( $criterion, $dispatcher );
    }
}

