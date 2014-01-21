<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\MapLocationDistance class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;

/**
 * The MapLocationDistance Criterion class.
 *
 * Provides content filtering based on distance from geographical location.
 */
class MapLocationDistance extends Criterion implements CriterionInterface, CustomFieldInterface
{
    /**
     * Custom field definitions to query instead of default field
     *
     * @var array
     */
    protected $customFields = array();

    /**
     * @todo needs to be defined, could be a string identifying one of the predefined easing methods
     *
     * @var array
     */
    protected $boost;

    /**
     * @param string $target
     * @param string $operator
     * @param float $distance
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct( $target, $operator, $distance, $latitude, $longitude )
    {
        $distanceStart = new MapLocationValue( $latitude, $longitude );
        parent::__construct( $target, $operator, $distance, $distanceStart );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications( Operator::IN, Specifications::FORMAT_ARRAY ),
            new Specifications( Operator::EQ, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::GT, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::GTE, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::LT, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::LTE, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::BETWEEN, Specifications::FORMAT_ARRAY, null, 2 ),
        );
    }

    /**
     * Set a custom field to query
     *
     * Set a custom field to query for a defined field in a defined type.
     *
     * @param string $type
     * @param string $field
     * @param string $customField
     * @return void
     */
    public function setCustomField( $type, $field, $customField )
    {
        $this->customFields[$type][$field] = $customField;
    }

    /**
     * Return custom field
     *
     * If no custom field is set, return null
     *
     * @param string $type
     * @param string $field
     * @return mixed
     */
    public function getCustomField( $type, $field )
    {
        if ( !isset( $this->customFields[$type] ) ||
             !isset( $this->customFields[$type][$field] ) )
        {
            return null;
        }

        return $this->customFields[$type][$field];
    }
}
