<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\CriterionVisitor;

use eZ\Publish\Core\Search\Solr\Content\CriterionVisitor;
use eZ\Publish\Core\Search\Solr\Content\FieldMap;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the FullText criterion
 */
class FullText extends CriterionVisitor
{
    /**
     * Field map
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\FieldMap
     */
    protected $fieldMap;

    /**
     * Create from content type handler and field registry
     *
     * @param \eZ\Publish\Core\Search\Solr\Content\FieldMap $fieldMap
     *
     * @return void
     */
    public function __construct( FieldMap $fieldMap )
    {
        $this->fieldMap = $fieldMap;
    }

    /**
     * Get field type information
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $fieldDefinitionIdentifier
     *
     * @return array
     */
    protected function getFieldNames( Criterion $criterion, $fieldDefinitionIdentifier )
    {
        return $this->fieldMap->getFieldNames( $criterion, $fieldDefinitionIdentifier );
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
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        $queries = array(
            "text:" . $criterion->value,
        );

        foreach ( $criterion->boost as $field => $boost )
        {
            $fieldNames = $this->getFieldNames( $criterion, $field );

            foreach ( $fieldNames as $name )
            {
                $queries[] = $name . ":" . $criterion->value . "^" . $boost;
            }
        }

        return "(" . implode(
            ') OR (',
            array_map(
                function ($search) use ($criterion) {
                    return $search . (
                        $criterion->fuzziness < 1 ?
                            sprintf( "~%.1f", $criterion->fuzziness ) :
                            ""
                        );
                },
                $queries
            )
        ) . ")";
    }
}

