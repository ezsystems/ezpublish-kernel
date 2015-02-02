<?php
/**
 * File containing the ContentTypeIdentifierIn criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;

/**
 * Visits the ContentTypeIdentifier criterion
 */
class ContentTypeIdentifierIn extends CriterionVisitor
{
    /**
     * ContentType handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Create from content type handler
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     */
    public function __construct( ContentTypeHandler $contentTypeHandler )
    {
        $this->contentTypeHandler = $contentTypeHandler;
    }

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
            $criterion instanceof Criterion\ContentTypeIdentifier &&
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
     * @param array $fieldFilters
     *
     * @return mixed
     */
    public function visitFilter( Criterion $criterion, Dispatcher $dispatcher, array $fieldFilters )
    {
        if ( count( $criterion->value ) > 1 )
        {
            $idList = array();
            foreach ( $criterion->value as $identifier )
            {
                $idList[] = $this->contentTypeHandler->loadByIdentifier( $identifier )->id;
            }

            $filter = array(
                "terms" => array(
                    "type_id" => $idList,
                ),
            );
        }
        else
        {
            $filter = array(
                "term" => array(
                    "type_id" => $this->contentTypeHandler->loadByIdentifier( $criterion->value[0] )->id,
                ),
            );
        }

        return $filter;
    }
}
