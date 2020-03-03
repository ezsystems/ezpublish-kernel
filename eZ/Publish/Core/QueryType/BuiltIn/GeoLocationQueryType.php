<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuiltIn;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\MapLocationDistance;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GeoLocationQueryType extends AbstractQueryType
{
    public static function getName(): string
    {
        return 'eZ:GeoLocation';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('field');
        $resolver->setAllowedTypes('field', ['string', Field::class]);
        $resolver->setNormalizer('field', static function (Options $options, $value) {
            if ($value instanceof Field) {
                $value = $value->fieldDefIdentifier;
            }

            return $value;
        });

        $resolver->setRequired('distance');
        $resolver->setAllowedTypes('distance', ['float', 'int', 'array']);

        $resolver->setRequired('latitude');
        $resolver->setAllowedTypes('latitude', ['float']);

        $resolver->setRequired('longitude');
        $resolver->setAllowedTypes('longitude', ['float']);

        $resolver->setDefault('operator', Operator::LTE);
        $resolver->setAllowedTypes('operator', ['string']);
        $resolver->setAllowedValues('operator', [
            Operator::IN,
            Operator::EQ,
            Operator::GT,
            Operator::GTE,
            Operator::LT,
            Operator::LTE,
            Operator::BETWEEN,
        ]);
    }

    protected function getQueryFilter(array $parameters): Criterion
    {
        return new MapLocationDistance(
            $parameters['field'],
            $parameters['operator'],
            $parameters['distance'],
            $parameters['latitude'],
            $parameters['longitude']
        );
    }
}
