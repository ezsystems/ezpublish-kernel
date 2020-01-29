<?php

declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Depth;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchNone;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SubtreeQueryType extends AbstractLocationQueryType
{
    public static function getName(): string
    {
        return 'eZ:Subtree';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'depth' => -1,
        ]);
        $resolver->setAllowedTypes('depth', 'int');
    }

    protected function getQueryFilter(array $parameters): Criterion
    {
        $location = $this->resolveLocation($parameters);

        if ($location === null) {
            return new MatchNone();
        }

        if ($parameters['depth'] > -1) {
            $depth = $location->depth + (int)$parameters['depth'];

            return new LogicalAnd([
                new Subtree($location->pathString),
                new Depth(Operator::LTE, $depth),
            ]);
        }

        return new Subtree($location->pathString);
    }
}
