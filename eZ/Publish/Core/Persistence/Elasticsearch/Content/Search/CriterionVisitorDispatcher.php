<?php
/**
 * File containing the Aggregate criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use RuntimeException;

/**
 * Dispatches Criterion objects to a visitor depending on the query context
 */
class CriterionVisitorDispatcher
{
    /**
     * @todo
     */
    const CONTEXT_QUERY = "query";

    /**
     * @todo
     */
    const CONTEXT_FILTER = "filter";

    /**
     * @todo
     * @var array
     */
    protected $contextMethodMap = array(
        self::CONTEXT_QUERY => "visitQuery",
        self::CONTEXT_FILTER => "visitFilter",
    );

    /**
     * Array of available visitors
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor[]
     */
    protected $visitors = array();

    /**
     * Construct from optional visitor array
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor[] $visitors
     */
    public function __construct( array $visitors = array() )
    {
        foreach ( $visitors as $visitor )
        {
            $this->addVisitor( $visitor );
        }
    }

    /**
     * Adds visitor
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor $visitor
     */
    public function addVisitor( CriterionVisitor $visitor )
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $context
     *
     * @return string
     */
    public function dispatch( Criterion $criterion, $context )
    {
        if ( !isset( $this->contextMethodMap[$context] ) )
        {
            throw new RuntimeException(
                "Given context '{$context}' is not recognized"
            );
        }

        foreach ( $this->visitors as $visitor )
        {
            if ( $visitor->canVisit( $criterion ) )
            {
                return $visitor->{ $this->contextMethodMap[$context] }( $criterion, $this );
            }
        }

        throw new NotImplementedException(
            "No visitor available for: " . get_class( $criterion ) . ' with operator ' . $criterion->operator
        );
    }
}
