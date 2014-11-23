<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the FullText criterion
 */
class FullText extends CriterionVisitor
{
    /**
     * Field map
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap
     */
    protected $fieldMap;

    /**
     * Create from content type handler and field registry
     *
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap $fieldMap
     */
    public function __construct( FieldMap $fieldMap )
    {
        $this->fieldMap = $fieldMap;
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
        return $criterion instanceof Criterion\FullText;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     * @param bool $isChildQuery
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null, $isChildQuery = false )
    {
        $childJoinStr = $this->getChildJoinString( $isChildQuery );
        $fuzzinessStr = $criterion->fuzziness < 1 ? sprintf( "~%.1f", $criterion->fuzziness ) : "";
        $queries = array(
            "{$childJoinStr}text:" . $criterion->value . $fuzzinessStr,
        );

        foreach ( $criterion->boost as $field => $boost )
        {
            $fields = $this->fieldMap->getFieldTypes( $criterion );

            if ( !isset( $fields[$field] ) )
            {
                continue;
            }

            foreach ( $fields[$field] as $fieldNames )
            {
                foreach ( $fieldNames as $fieldName )
                {
                    $queries[] = $childJoinStr . "{!type=edismax qf={$fieldName} v='{$criterion->value}^{$boost}{$fuzzinessStr}'}";
                }
            }
        }

        $docType = $isChildQuery ? 'location' : 'content';
        return '(' . implode( ') OR (', $queries ) . ")";
    }
}

