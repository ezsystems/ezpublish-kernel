<?php
/**
 * This file is part of the ezplatform package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use InvalidArgumentException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ContentViewQueryTypeMapper implements QueryTypeMapper
{
    /**
     * @var QueryTypeRegistry
     */
    private $queryTypeRegistry;

    public function __construct(QueryTypeRegistry $queryTypeRegistry)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    public function map($contentView)
    {
        if (!$contentView instanceof ContentView) {
            throw new InvalidArgumentException('ContentView expected');
        }

        if (!$contentView->hasParameter('query')) {
            throw new InvalidArgumentException('query', "Missing required 'query' view parameter");
        }

        $queryType = $this->queryTypeRegistry->getQueryType(
            $contentView->getParameter('query')
        );

        $queryParameters = $contentView->hasParameter('queryParameters') ?
            $this->extractParameters($contentView, $queryType) :
            [];

        return $queryType->getQuery($queryParameters);
    }

    /**
     * @param ContentView $contentView
     * @param QueryType $queryType
     *
     * @return array
     */
    public function extractParameters(ContentView $contentView, QueryType $queryType)
    {
        $queryParameters = $contentView->getParameter('queryParameters');
        $supportedQueryParameters = array_flip($queryType->getSupportedParameters());
        foreach ($queryParameters as $queryParameterName => $queryParameterValue) {
            if (!isset($supportedQueryParameters[$queryParameterName])) {
                throw new InvalidArgumentException("parameter $queryParameterName", 'unsupported query parameter');
            }
            $queryParameters[$queryParameterName] = $this->evaluateExpression($contentView, $queryParameterValue);
        }

        return $queryParameters;
    }

    /**
     * @param ContentView $contentView
     * @param string $queryParameterValue
     *
     * @return mixed
     */
    public function evaluateExpression(ContentView $contentView, $queryParameterValue)
    {
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
}
