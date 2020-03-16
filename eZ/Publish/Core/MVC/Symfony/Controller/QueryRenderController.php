<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\Core\MVC\Symfony\View\QueryView;
use eZ\Publish\Core\Pagination\Pagerfanta\AdapterFactory\SearchHitAdapterFactoryInterface;
use eZ\Publish\Core\Query\QueryFactoryInterface;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Controller used internally by ez_query_*_render and ez_query_*_render_* functions.
 *
 * @internal
 */
final class QueryRenderController
{
    /** @var \eZ\Publish\Core\Query\QueryFactoryInterface */
    private $queryFactory;

    /** @var \eZ\Publish\Core\Pagination\Pagerfanta\AdapterFactory\SearchHitAdapterFactoryInterface */
    private $searchHitAdapterFactory;

    public function __construct(
        QueryFactoryInterface $queryFactory,
        SearchHitAdapterFactoryInterface $searchHitAdapterFactory
    ) {
        $this->queryFactory = $queryFactory;
        $this->searchHitAdapterFactory = $searchHitAdapterFactory;
    }

    public function renderQuery(Request $request, array $options): QueryView
    {
        $options = $this->resolveOptions($options);

        $results = new Pagerfanta($this->getAdapter($options));
        if ($options['pagination']['enabled']) {
            $currentPage = $request->get($options['pagination']['page_param'], 1);

            $results->setAllowOutOfRangePages(true);
            $results->setMaxPerPage($options['pagination']['limit']);
            $results->setCurrentPage($currentPage);
        }

        return $this->createQueryView(
            $options['template'],
            $options['query']['assign_results_to'],
            $results
        );
    }

    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();

        $resolver->setDefault('query', static function (OptionsResolver $resolver): void {
            $resolver->setDefaults([
                'parameters' => [],
                'assign_results_to' => 'items',
            ]);

            $resolver->setRequired(['query_type']);
            $resolver->setAllowedTypes('query_type', 'string');
            $resolver->setAllowedTypes('parameters', 'array');
            $resolver->setAllowedTypes('assign_results_to', 'string');
        });

        $resolver->setDefault('pagination', static function (OptionsResolver $resolver): void {
            $resolver->setDefaults([
                'enabled' => true,
                'limit' => 10,
                'page_param' => 'page',
            ]);

            $resolver->setAllowedTypes('enabled', 'boolean');
            $resolver->setAllowedTypes('limit', 'int');
            $resolver->setAllowedTypes('page_param', 'string');
        });

        $resolver->setRequired('template');
        $resolver->setAllowedTypes('template', 'string');
        $resolver->setAllowedTypes('query', 'array');

        return $resolver->resolve($options);
    }

    private function getAdapter(array $options): AdapterInterface
    {
        $query = $this->queryFactory->create(
            $options['query']['query_type'],
            $options['query']['parameters']
        );

        if ($options['pagination']['enabled']) {
            return $this->searchHitAdapterFactory->createAdapter($query);
        }

        return $this->searchHitAdapterFactory->createFixedAdapter($query);
    }

    private function createQueryView(string $template, string $assignResultsTo, iterable $results): QueryView
    {
        $view = new QueryView();
        $view->setTemplateIdentifier($template);
        $view->addParameters([
            $assignResultsTo => $results,
        ]);

        return $view;
    }
}
