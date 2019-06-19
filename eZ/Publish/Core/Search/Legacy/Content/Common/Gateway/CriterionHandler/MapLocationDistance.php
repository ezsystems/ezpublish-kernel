<?php

/**
 * File containing the DoctrineDatabase MapLocationDistance criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use RuntimeException;

/**
 * MapLocationDistance criterion handler.
 */
class MapLocationDistance extends FieldBase
{
    /**
     * Distance in kilometers of one degree longitude at the Equator.
     */
    const DEGREE_KM = 111.195;

    /**
     * Radius of the planet in kilometers.
     */
    const EARTH_RADIUS = 6371.01;

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\MapLocationDistance;
    }

    /**
     * Returns a list of IDs of searchable FieldDefinitions for the given criterion target.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier.
     *
     * @param string $fieldIdentifier
     *
     * @return array
     */
    protected function getFieldDefinitionIds($fieldIdentifier)
    {
        $fieldDefinitionIdList = [];
        $fieldMap = $this->contentTypeHandler->getSearchableFieldMap();

        foreach ($fieldMap as $contentTypeIdentifier => $fieldIdentifierMap) {
            // First check if field exists in the current ContentType, there is nothing to do if it doesn't
            if (
                !(
                    isset($fieldIdentifierMap[$fieldIdentifier])
                    && $fieldIdentifierMap[$fieldIdentifier]['field_type_identifier'] === 'ezgmaplocation'
                )
            ) {
                continue;
            }

            $fieldDefinitionIdList[] = $fieldIdentifierMap[$fieldIdentifier]['field_definition_id'];
        }

        if (empty($fieldDefinitionIdList)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$fieldIdentifier}'."
            );
        }

        return $fieldDefinitionIdList;
    }

    protected function kilometersToDegrees($kilometers)
    {
        return $kilometers / self::DEGREE_KM;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     * @throws \RuntimeException If given criterion operator is not handled
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $fieldDefinitionIds = $this->getFieldDefinitionIds($criterion->target);
        $subSelect = $query->subSelect();

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location */
        $location = $criterion->valueData;

        /*
         * Note: this formula is precise only for short distances.
         * @todo if ABS function was available in Zeta Database component it should be possible to account for
         * distances across the date line. Revisit when Doctrine DBAL is introduced.
         */
        $longitudeCorrectionByLatitude = cos(deg2rad($location->latitude)) ** 2;
        $distanceExpression = $subSelect->expr->add(
            $subSelect->expr->mul(
                $subSelect->expr->sub(
                    $this->dbHandler->quoteColumn('latitude', 'ezgmaplocation'),
                    $subSelect->bindValue($location->latitude)
                ),
                $subSelect->expr->sub(
                    $this->dbHandler->quoteColumn('latitude', 'ezgmaplocation'),
                    $subSelect->bindValue($location->latitude)
                )
            ),
            $subSelect->expr->mul(
                $subSelect->expr->sub(
                    $this->dbHandler->quoteColumn('longitude', 'ezgmaplocation'),
                    $subSelect->bindValue($location->longitude)
                ),
                $subSelect->expr->sub(
                    $this->dbHandler->quoteColumn('longitude', 'ezgmaplocation'),
                    $subSelect->bindValue($location->longitude)
                ),
                $subSelect->bindValue($longitudeCorrectionByLatitude)
            )
        );

        switch ($criterion->operator) {
            case Criterion\Operator::IN:
            case Criterion\Operator::EQ:
            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                $distanceInDegrees = $this->kilometersToDegrees($criterion->value) ** 2;
                $distanceFilter = $subSelect->expr->$operatorFunction(
                    $distanceExpression,
                    $subSelect->expr->round(
                        $subSelect->bindValue($distanceInDegrees),
                        10
                    )
                );
                break;

            case Criterion\Operator::BETWEEN:
                $distanceInDegrees1 = $this->kilometersToDegrees($criterion->value[0]) ** 2;
                $distanceInDegrees2 = $this->kilometersToDegrees($criterion->value[1]) ** 2;
                $distanceFilter = $subSelect->expr->between(
                    $distanceExpression,
                    $subSelect->expr->round(
                        $subSelect->bindValue($distanceInDegrees1),
                        10
                    ),
                    $subSelect->expr->round(
                        $subSelect->bindValue($distanceInDegrees2),
                        10
                    )
                );
                break;

            default:
                throw new RuntimeException('Unknown operator.');
        }

        // Calculate bounding box if possible
        // @todo consider covering operators EQ and IN as well
        $boundingConstraints = [];
        switch ($criterion->operator) {
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
        if (isset($distanceUpper)) {
            $boundingConstraints = $this->getBoundingConstraints($subSelect, $location, $distanceUpper);
        }

        $subSelect
            ->select($this->dbHandler->quoteColumn('contentobject_id'))
            ->from($this->dbHandler->quoteTable('ezcontentobject_attribute'))
            ->innerJoin(
                $this->dbHandler->quoteTable('ezgmaplocation'),
                $subSelect->expr->lAnd(
                    [
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('contentobject_version', 'ezgmaplocation'),
                            $this->dbHandler->quoteColumn('version', 'ezcontentobject_attribute')
                        ),
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('contentobject_attribute_id', 'ezgmaplocation'),
                            $this->dbHandler->quoteColumn('id', 'ezcontentobject_attribute')
                        ),
                    ],
                    $boundingConstraints
                )
            )
            ->where(
                $subSelect->expr->lAnd(
                    $subSelect->expr->eq(
                        $this->dbHandler->quoteColumn('version', 'ezcontentobject_attribute'),
                        $this->dbHandler->quoteColumn('current_version', 'ezcontentobject')
                    ),
                    $subSelect->expr->in(
                        $this->dbHandler->quoteColumn('contentclassattribute_id', 'ezcontentobject_attribute'),
                        $fieldDefinitionIds
                    ),
                    $distanceFilter,
                    $this->getFieldCondition($subSelect, $languageSettings)
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }

    /**
     * Credit for the formula goes to http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location
     * @param float $distance
     *
     * @return array
     */
    protected function getBoundingConstraints(SelectQuery $query, MapLocationValue $location, $distance)
    {
        $boundingCoordinates = $this->getBoundingCoordinates($location, $distance);

        return [
            $query->expr->gte(
                $this->dbHandler->quoteColumn('latitude', 'ezgmaplocation'),
                $query->bindValue($boundingCoordinates['lowLatitude'])
            ),
            $query->expr->gte(
                $this->dbHandler->quoteColumn('longitude', 'ezgmaplocation'),
                $query->bindValue($boundingCoordinates['lowLongitude'])
            ),
            $query->expr->lte(
                $this->dbHandler->quoteColumn('latitude', 'ezgmaplocation'),
                $query->bindValue($boundingCoordinates['highLatitude'])
            ),
            $query->expr->lte(
                $this->dbHandler->quoteColumn('longitude', 'ezgmaplocation'),
                $query->bindValue($boundingCoordinates['highLongitude'])
            ),
        ];
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
     * @param float $distance
     *
     * @return array
     */
    protected function getBoundingCoordinates(MapLocationValue $location, $distance)
    {
        $radiansLatitude = deg2rad($location->latitude);
        $radiansLongitude = deg2rad($location->longitude);
        $angularDistance = $distance / self::EARTH_RADIUS;
        $deltaLongitude = asin(sin($angularDistance) / cos($radiansLatitude));

        $lowLatitudeRadians = $radiansLatitude - $angularDistance;
        $highLatitudeRadians = $radiansLatitude + $angularDistance;

        // Check that bounding box does not include poles.
        if ($lowLatitudeRadians > -M_PI_2 && $highLatitudeRadians < M_PI_2) {
            $boundingCoordinates = [
                'lowLatitude' => rad2deg($lowLatitudeRadians),
                'lowLongitude' => rad2deg($radiansLongitude - $deltaLongitude),
                'highLatitude' => rad2deg($highLatitudeRadians),
                'highLongitude' => rad2deg($radiansLongitude + $deltaLongitude),
            ];
        } else {
            // Handle the pole(s) being inside a bounding box, in this case we MUST cover
            // full circle of Earth's longitude and one or both poles.
            // Note that calculation for distances over the polar regions with flat Earth formula
            // will be VERY imprecise.
            $boundingCoordinates = [
                'lowLatitude' => rad2deg(max($lowLatitudeRadians, -M_PI_2)),
                'lowLongitude' => rad2deg(-M_PI),
                'highLatitude' => rad2deg(min($highLatitudeRadians, M_PI_2)),
                'highLongitude' => rad2deg(M_PI),
            ];
        }

        return $boundingCoordinates;
    }
}
