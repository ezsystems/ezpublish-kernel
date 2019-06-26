<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use InvalidArgumentException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Maps a ContentView to a QueryType using the 'query' parameter from the view configuration.
 */
class QueryParameterContentViewQueryTypeMapper implements ContentViewQueryTypeMapper
{
    /** @var QueryTypeRegistry */
    private $queryTypeRegistry;

    public function __construct(QueryTypeRegistry $queryTypeRegistry)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    public function map(ContentView $contentView)
    {
        if (!$contentView instanceof ContentView) {
            throw new InvalidArgumentException('ContentView expected');
        }

        if (!$contentView->hasParameter('query')) {
            throw new InvalidArgumentException('query', "Missing required 'query' view parameter");
        }

        $queryOptions = $contentView->getParameter('query');
        $queryType = $this->queryTypeRegistry->getQueryType($queryOptions['query_type']);

        return $queryType->getQuery($this->extractParametersFromContentView($contentView));
    }

    /**
     * @param ContentView $contentView
     *
     * @return array
     */
    private function extractParametersFromContentView(ContentView $contentView)
    {
        $queryParameters = [];

        $queryOptions = $contentView->getParameter('query');
        if (isset($queryOptions['parameters'])) {
            foreach ($queryOptions['parameters'] as $name => $value) {
                $queryParameters[$name] = $this->extractParameters($contentView, $value);
            }
        }

        return $queryParameters;
    }

    /**
     * @param ContentView $contentView
     * @param array $queryParameterValue
     *
     * @return array|string
     */
    private function extractParameters(ContentView $contentView, $queryParameterValue)
    {
        if (is_array($queryParameterValue)) {
            $queryParameters = [];
            foreach ($queryParameterValue as $name => $value) {
                $queryParameters[$name] = $this->extractParameters($contentView, $value);
            }

            return $queryParameters;
        }

        return $this->evaluateExpression($contentView, $queryParameterValue);
    }

    /**
     * @param ContentView $contentView
     * @param string $queryParameterValue
     *
     * @return mixed
     */
    private function evaluateExpression(ContentView $contentView, $queryParameterValue)
    {
        if (is_string($queryParameterValue) && substr($queryParameterValue, 0, 2) === '@=') {
            $language = new ExpressionLanguage();

            return $language->evaluate(
                substr($queryParameterValue, 2),
                [
                    'view' => $contentView,
                    'location' => $contentView->getLocation(),
                    'content' => $contentView->getContent(),
                ]
            );
        }

        return $queryParameterValue;
    }
}
