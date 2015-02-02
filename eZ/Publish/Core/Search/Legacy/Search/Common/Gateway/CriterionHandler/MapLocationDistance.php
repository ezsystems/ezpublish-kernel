<?php
/**
 * File containing the DoctrineDatabase MapLocationDistance criterion handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use RuntimeException;

/**
 * MapLocationDistance criterion handler
 */
class MapLocationDistance extends CriterionHandler
{
    /**
     * Distance in kilometers of one degree longitude at the Equator
     */
    const DEGREE_KM = 111.195;

    /**
     * Radius of the planet in kilometers
     */
    const EARTH_RADIUS = 6371.01;

    /**
     * DB handler to fetch additional field information
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * Construct from handler handler
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     *
     */
    public function __construct( DatabaseHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\MapLocationDistance;
    }

    /**
     * Checks if there are searchable fields for the Criterion
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier.
     *
     * @caching
     * @param string $fieldIdentifier
     *
     * @return void
     */
    protected function checkSearchableFields( $fieldIdentifier )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select( $this->dbHandler->quoteColumn( 'id', 'ezcontentclass_attribute' ) )
            ->from( $this->dbHandler->quoteTable( 'ezcontentclass_attribute' ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'is_searchable', 'ezcontentclass_attribute' ),
                        $query->bindValue( 1, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'data_type_string', 'ezcontentclass_attribute' ),
                        $query->bindValue( "ezgmaplocation" )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'identifier', 'ezcontentclass_attribute' ),
                        $query->bindValue( $fieldIdentifier )
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
        $fieldDefinitionIds = $statement->fetchAll( \PDO::FETCH_COLUMN );

        if ( empty( $fieldDefinitionIds ) )
        {
            throw new InvalidArgumentException(
                "\$criterion->target",
                "No searchable fields found for the given criterion target '{$fieldIdentifier}'."
            );
        }
    }

    protected function kilometersToDegrees( $kilometers )
    {
        return $kilometers / self::DEGREE_KM;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     * @throws \RuntimeException If given criterion operator is not handled
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle( CriteriaConverter $converter, SelectQuery $query, Criterion $criterion )
    {
        $this->checkSearchableFields( $criterion->target );
        $subSelect = $query->subSelect();

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location */
        $location = $criterion->valueData;

        /**
         * Note: this formula is precise only for short distances.
         * @todo if ABS function was available in Zeta Database component it should be possible to account for
         * distances across the date line. Revisit when Doctrine DBAL is introduced.
         */
        $longitudeCorrectionByLatitude = pow( cos( deg2rad( $location->latitude ) ), 2 );
        $distanceExpression = $subSelect->expr->add(
            $subSelect->expr->mul(
                $subSelect->expr->sub(
                    $this->dbHandler->quoteColumn( "latitude", "ezgmaplocation" ),
                    $subSelect->bindValue( $location->latitude )
                ),
                $subSelect->expr->sub(
                    $this->dbHandler->quoteColumn( "latitude", "ezgmaplocation" ),
                    $subSelect->bindValue( $location->latitude )
                )
            ),
            $subSelect->expr->mul(
                $subSelect->expr->sub(
                    $this->dbHandler->quoteColumn( "longitude", "ezgmaplocation" ),
                    $subSelect->bindValue( $location->longitude )
                ),
                $subSelect->expr->sub(
                    $this->dbHandler->quoteColumn( "longitude", "ezgmaplocation" ),
                    $subSelect->bindValue( $location->longitude )
                ),
                $subSelect->bindValue( $longitudeCorrectionByLatitude )
            )
        );

        switch ( $criterion->operator )
        {
            case Criterion\Operator::IN:
            case Criterion\Operator::EQ:
            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                $distanceInDegrees = pow( $this->kilometersToDegrees( $criterion->value ), 2 );
                $distanceFilter = $subSelect->expr->$operatorFunction(
                    $distanceExpression,
                    $subSelect->expr->round(
                        $subSelect->bindValue( $distanceInDegrees ),
                        10
                    )
                );
                break;

            case Criterion\Operator::BETWEEN:
                $distanceInDegrees1 = pow( $this->kilometersToDegrees( $criterion->value[0] ), 2 );
                $distanceInDegrees2 = pow( $this->kilometersToDegrees( $criterion->value[1] ), 2 );
                $distanceFilter = $subSelect->expr->between(
                    $distanceExpression,
                    $subSelect->expr->round(
                        $subSelect->bindValue( $distanceInDegrees1 ),
                        10
                    ),
                    $subSelect->expr->round(
                        $subSelect->bindValue( $distanceInDegrees2 ),
                        10
                    )
                );
                break;

            default:
                throw new RuntimeException( 'Unknown operator.' );
        }

        // Calculate bounding box if possible
        // @todo consider covering operators EQ and IN as well
        $boundingConstraints = array();
        switch ( $criterion->operator )
        {
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $distanceUpper = $criterion->value;
                break;
            case Criterion\Operator::BETWEEN:
                $distanceUpper = $criterion->value[0] > $criterion->value[1] ?
                    $criterion->value[0] :
                    $criterion->value[1];
                break;
        }
        if ( isset( $distanceUpper ) )
        {
            $boundingConstraints = $this->getBoundingConstraints( $subSelect, $location, $distanceUpper );
        }

        $subSelect
            ->select( $this->dbHandler->quoteColumn( 'contentobject_id' ) )
            ->from( $this->dbHandler->quoteTable( 'ezcontentobject_attribute' ) )
            ->innerJoin(
                $this->dbHandler->quoteTable( "ezgmaplocation" ),
                $subSelect->expr->lAnd(
                    array(
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn( "contentobject_version", "ezgmaplocation" ),
                            $this->dbHandler->quoteColumn( "version", "ezcontentobject_attribute" )
                        ),
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn( "contentobject_attribute_id", "ezgmaplocation" ),
                            $this->dbHandler->quoteColumn( "id", "ezcontentobject_attribute" )
                        )
                    ),
                    $boundingConstraints
                )
            )
            ->where(
                $subSelect->expr->lAnd(
                    $subSelect->expr->eq(
                        $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_attribute' ),
                        $this->dbHandler->quoteColumn( 'current_version', 'ezcontentobject' )
                    ),
                    $distanceFilter
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }

    /**
     * Credit for the formula goes to http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location
     * @param double $distance
     *
     * @return array
     */
    protected function getBoundingConstraints( SelectQuery $query, MapLocationValue $location, $distance )
    {
        $boundingCoordinates = $this->getBoundingCoordinates( $location, $distance );
        return array(
            $query->expr->gte(
                $this->dbHandler->quoteColumn( "latitude", "ezgmaplocation" ),
                $query->bindValue( $boundingCoordinates["lowLatitude"] )
            ),
            $query->expr->gte(
                $this->dbHandler->quoteColumn( "longitude", "ezgmaplocation" ),
                $query->bindValue( $boundingCoordinates["lowLongitude"] )
            ),
            $query->expr->lte(
                $this->dbHandler->quoteColumn( "latitude", "ezgmaplocation" ),
                $query->bindValue( $boundingCoordinates["highLatitude"] )
            ),
            $query->expr->lte(
                $this->dbHandler->quoteColumn( "longitude", "ezgmaplocation" ),
                $query->bindValue( $boundingCoordinates["highLongitude"] )
            )
        );
    }

    /**
     * Calculates and returns bounding box coordinates.
     *
     * Credits: http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates
     *
     * @todo it should also be possible to calculate inner bounding box, which could be applied for the
     * operators GT, GTE and lower distance of the BETWEEN operator.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location
     * @param double $distance
     *
     * @return array
     */
    protected function getBoundingCoordinates( MapLocationValue $location, $distance )
    {
        $radiansLatitude = deg2rad( $location->latitude );
        $radiansLongitude = deg2rad( $location->longitude );
        $angularDistance = $distance / self::EARTH_RADIUS;
        $deltaLongitude = asin( sin( $angularDistance ) / cos( $radiansLatitude ) );

        $lowLatitudeRadians = $radiansLatitude - $angularDistance;
        $highLatitudeRadians = $radiansLatitude + $angularDistance;

        // Check that bounding box does not include poles.
        if ( $lowLatitudeRadians > -M_PI_2 && $highLatitudeRadians < M_PI_2 )
        {
            $boundingCoordinates = array(
                "lowLatitude" => rad2deg( $lowLatitudeRadians ),
                "lowLongitude" => rad2deg( $radiansLongitude - $deltaLongitude ),
                "highLatitude" => rad2deg( $highLatitudeRadians ),
                "highLongitude" => rad2deg( $radiansLongitude + $deltaLongitude )
            );
        }
        else
        {
            // Handle the pole(s) being inside a bounding box, in this case we MUST cover
            // full circle of Earth's longitude and one or both poles.
            // Note that calculation for distances over the polar regions with flat Earth formula
            // will be VERY imprecise.
            $boundingCoordinates = array(
                "lowLatitude" => rad2deg( max( $lowLatitudeRadians, -M_PI_2 ) ),
                "lowLongitude" => rad2deg( -M_PI ),
                "highLatitude" => rad2deg( min( $highLatitudeRadians, M_PI_2 ) ),
                "highLongitude" => rad2deg( M_PI )
            );
        }

        return $boundingCoordinates;
    }

}
