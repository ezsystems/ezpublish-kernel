<?php

declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\MVC\Symfony\View\QueryView;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use eZ\Publish\Core\Pagination\Pagerfanta\LocationSearchHitAdapter;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Controller used internally by ez_query_X_render_* functions.
 *
 * @internal
 */
final class QueryRenderController
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    public function __construct(SearchService $searchService, QueryTypeRegistry $queryTypeRegistry)
    {
        $this->searchService = $searchService;
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    public function renderContentQueryAction(Request $request, array $options): QueryView
    {
        return $this->renderQuery($request, $options, 'findContent');
    }

    public function renderContentInfoQueryAction(Request $request, array $options): QueryView
    {
        return $this->renderQuery($request, $options, 'findContentInfo');
    }

    public function renderLocationQueryAction(Request $request, array $options): QueryView
    {
        return $this->renderQuery($request, $options, 'findLocation');
    }

    private function renderQuery(Request $request, array $options, string $method): QueryView
    {
        $options = $this->resolveOptions($options);

        $queryType = $this->queryTypeRegistry->getQueryType(
            $options['query']['query_type']
        );

        $query = $queryType->getQuery($options['query']['parameters']);

        if ($options['pagination']['enabled']) {
            $currentPage = $request->get($options['pagination']['page_param'], 1);

            if ($query instanceof LocationQuery) {
                $results = new Pagerfanta(new LocationSearchHitAdapter($query, $this->searchService));
            } else {
                $results = new Pagerfanta(new ContentSearchHitAdapter($query, $this->searchService));
            }

            $results->setAllowOutOfRangePages(true);
            $results->setMaxPerPage($options['pagination']['limit']);
            $results->setCurrentPage($currentPage);
        } else {
            /** @var \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResults */
            $searchResults = $this->searchService->{$method}($query);

            $results = new Pagerfanta(new FixedAdapter($searchResults->totalCount, $searchResults));
        }

        $view = new QueryView();
        $view->setTemplateIdentifier($options['template']);
        $view->addParameters([
            $options['query']['assign_results_to'] => $results,
        ]);

        return $view;
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
}
