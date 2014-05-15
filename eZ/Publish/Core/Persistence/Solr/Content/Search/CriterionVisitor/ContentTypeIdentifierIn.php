<?php
/**
 * File containing the ContentTypeIdentifier criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;

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
     * Create from content type handler and field registry
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     */
    public function __construct( Handler $contentTypeHandler )
    {
        $this->contentTypeHandler = $contentTypeHandler;
    }

    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return
            $criterion instanceof Criterion\ContentTypeIdentifier
            && (
                ( $criterion->operator ?: Operator::IN ) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        $contentTypeHandler = $this->contentTypeHandler;

        return '(' .
            implode(
                ' OR ',
                array_map(
                    function ( $value ) use ( $contentTypeHandler )
                    {
                        return 'type_id:"' . $contentTypeHandler->loadByIdentifier( $value )->id . '"';
                    },
                    $criterion->value
                )
            ) .
            ')';
    }
}
