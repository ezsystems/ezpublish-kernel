<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
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
     * @param string $fieldIdentifier
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier.
     */
    protected function getFieldDefinitionIds($fieldIdentifier)
    {
        $fieldDefinitionIdList = [];
        $fieldMap = $this->contentTypeHandler->getSearchableFieldMap();

        foreach ($fieldMap as $fieldIdentifierMap) {
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
                "No searchable Fields found for the provided Criterion target '{$fieldIdentifier}'."
            );
        }

        return $fieldDefinitionIdList;
    }

    protected function kilometersToDegrees($kilometers)
    {
        return $kilometers / self::DEGREE_KM;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $fieldDefinitionIds = $this->getFieldDefinitionIds($criterion->target);
        $subSelect = $this->connection->createQueryBuilder();

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location */
        $location = $criterion->valueData;

        // note: avoid using literal names for parameters to account for multiple visits of the same Criterion
        $latitudePlaceholder = $queryBuilder->createNamedParameter($location->latitude);
        $longitudePlaceholder = $queryBuilder->createNamedParameter($location->longitude);
        $correctionPlaceholder = $queryBuilder->createNamedParameter(
            cos(deg2rad($location->latitude)) ** 2
        );

        // build: (latitude1 - latitude2)^2 + (longitude2 - longitude2)^2 * longitude_correction)
        $latitudeSubstrExpr = "(map.latitude - {$latitudePlaceholder})";
        $longitudeSubstrExpr = "(map.longitude - {$longitudePlaceholder})";
        $latitudeExpr = "{$latitudeSubstrExpr} * {$latitudeSubstrExpr}";
        $longitudeExpr = "{$longitudeSubstrExpr} * {$longitudeSubstrExpr} * {$correctionPlaceholder}";
        $distanceExpression = "{$latitudeExpr} + {$longitudeExpr}";

        $expr = $subSelect->expr();
        switch ($criterion->operator) {
            case Criterion\Operator::IN:
            case Criterion\Operator::EQ:
            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                $distanceInDegrees = $this->kilometersToDegrees($criterion->value) ** 2;
                $distanceFilter = $expr->$operatorFunction(
                    $distanceExpression,
                    $this->dbPlatform->getRoundExpression(
                        $queryBuilder->createNamedParameter($distanceInDegrees),
                        10
                    )
                );
                break;

            case Criterion\Operator::BETWEEN:
                $distanceInDegrees1 = $this->kilometersToDegrees($criterion->value[0]) ** 2;
                $distanceInDegrees2 = $this->kilometersToDegrees($criterion->value[1]) ** 2;
                $distanceFilter = $this->dbPlatform->getBetweenExpression(
                    $distanceExpression,
                    $this->dbPlatform->getRoundExpression(
                        $queryBuilder->createNamedParameter($distanceInDegrees1),
                        10
                    ),
                    $this->dbPlatform->getRoundExpression(
                        $queryBuilder->createNamedParameter($distanceInDegrees2),
                        10
                    ),
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
            default:
                // Skip other operators
                break;
        }
        if (isset($distanceUpper)) {
            $boundingConstraints = $this->getBoundingConstraints(
                $queryBuilder,
                $location,
                $distanceUpper
            );
        }

        $subSelect
            ->select('contentobject_id')
            ->from('ezcontentobject_attribute', 'f_def')
            ->innerJoin(
                'f_def',
                'ezgmaplocation',
                'map',
                $expr->andX(
                    'map.contentobject_version = f_def.version',
                    'map.contentobject_attribute_id = f_def.id',
                    ...$boundingConstraints
                )
            )
            // note: joining by correlation on outer table
            ->where('f_def.version = c.current_version')
            ->andWhere(
                $expr->in(
                    'f_def.contentclassattribute_id',
                    $queryBuilder->createNamedParameter($fieldDefinitionIds, Connection::PARAM_INT_ARRAY)
                )
            )
            ->andWhere($distanceFilter)
            ->andWhere($this->getFieldCondition($queryBuilder, $languageSettings))
        ;

        return $queryBuilder->expr()->in(
            'c.id',
            $subSelect->getSQL()
        );
    }

    /**
     * Credit for the formula goes to http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates.
     */
    protected function getBoundingConstraints(
        QueryBuilder $query,
        MapLocationValue $location,
        float $distance
    ): array {
        $boundingCoordinates = $this->getBoundingCoordinates($location, $distance);

        $expr = $query->expr();

        return [
            $expr->gte(
                'map.latitude',
                $query->createNamedParameter($boundingCoordinates['lowLatitude'])
            ),
            $expr->gte(
                'map.longitude',
                $query->createNamedParameter($boundingCoordinates['lowLongitude'])
            ),
            $expr->lte(
                'map.latitude',
                $query->createNamedParameter($boundingCoordinates['highLatitude'])
            ),
            $expr->lte(
                'map.longitude',
                $query->createNamedParameter($boundingCoordinates['highLongitude'])
            ),
        ];
    }

    /**
     * Calculates and returns bounding box coordinates.
     *
     * Credits: http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location
     * @param float $distance
     *
     * @return array
     *
     * @todo it should also be possible to calculate inner bounding box, which could be applied for the
     * operators GT, GTE and lower distance of the BETWEEN operator.
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
