<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\QueryType\OptionsResolverBasedQueryType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractQueryType extends OptionsResolverBasedQueryType
{
    public const DEFAULT_LIMIT = 25;

    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClausesFactoryInterface */
    private $sortClausesFactory;

    public function __construct(
        Repository $repository,
        ConfigResolverInterface $configResolver,
        SortClausesFactoryInterface $sortSpecParserFactory
    ) {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
        $this->sortClausesFactory = $sortSpecParserFactory;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filter' => static function (OptionsResolver $resolver): void {
                $resolver->setDefaults([
                    'content_type' => [],
                    'visible_only' => true,
                    'siteaccess_aware' => true,
                ]);

                $resolver->setAllowedTypes('content_type', 'array');
                $resolver->setAllowedTypes('visible_only', 'bool');
                $resolver->setAllowedTypes('siteaccess_aware', 'bool');
            },
            'offset' => 0,
            'limit' => self::DEFAULT_LIMIT,
            'sort' => [],
        ]);

        $resolver->setNormalizer('sort', function (Options $options, $value) {
            if (is_string($value)) {
                $value = $this->sortClausesFactory->createFromSpecification($value);
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            return $value;
        });

        $resolver->setAllowedTypes('sort', ['string', 'array', SortClause::class]);
        $resolver->setAllowedTypes('offset', 'int');
        $resolver->setAllowedTypes('limit', 'int');
    }

    abstract protected function getQueryFilter(array $parameters): Criterion;

    protected function doGetQuery(array $parameters): Query
    {
        $query = new Query();
        $query->filter = $this->buildFilters($parameters);

        if ($parameters['sort'] !== null) {
            $query->sortClauses = $parameters['sort'];
        }

        $query->limit = $parameters['limit'];
        $query->offset = $parameters['offset'];

        return $query;
    }

    private function buildFilters(array $parameters): Criterion
    {
        $criteria = [
            $this->getQueryFilter($parameters),
        ];

        if ($parameters['filter']['visible_only']) {
            $criteria[] = new Visibility(Visibility::VISIBLE);
        }

        if (!empty($parameters['filter']['content_type'])) {
            $criteria[] = new ContentTypeIdentifier($parameters['filter']['content_type']);
        }

        if ($parameters['filter']['siteaccess_aware']) {
            // Limit results to current SiteAccess tree root
            $criteria[] = new Subtree($this->getRootLocationPathString());
        }

        return new LogicalAnd($criteria);
    }

    private function getRootLocationPathString(): string
    {
        $rootLocation = $this->repository->getLocationService()->loadLocation(
            $this->configResolver->getParameter('content.tree_root.location_id')
        );

        return $rootLocation->pathString;
    }
}
