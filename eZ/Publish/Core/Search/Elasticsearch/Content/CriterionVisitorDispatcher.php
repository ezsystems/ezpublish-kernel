<?php

/**
 * File containing the CriterionVisitorDispatcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use RuntimeException;

/**
 * Dispatches Criterion objects to a visitor depending on the query context.
 *
 * @deprecated
 */
class CriterionVisitorDispatcher
{
    /**
     * Query visiting context.
     */
    const CONTEXT_QUERY = 'query';

    /**
     * Filter visiting context.
     */
    const CONTEXT_FILTER = 'filter';

    /**
     * Map of CONTEXT_* constants to a method handling the visiting for a context.
     *
     * @var array
     */
    protected $contextMethodMap = [
        self::CONTEXT_QUERY => 'visitQuery',
        self::CONTEXT_FILTER => 'visitFilter',
    ];

    /**
     * Array of available visitors.
     *
     * @var \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor[]
     */
    protected $visitors = [];

    /**
     * Construct from optional visitor array.
     *
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor[] $visitors
     */
    public function __construct(array $visitors = [])
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    /**
     * Adds visitor.
     *
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor $visitor
     */
    public function addVisitor(CriterionVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Map field value to a proper Elasticsearch representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $context
     * @param array $languageFilter
     *
     * @return string
     */
    public function dispatch(Criterion $criterion, $context, array $languageFilter = [])
    {
        if (!isset($this->contextMethodMap[$context])) {
            throw new RuntimeException(
                "Given context '{$context}' is not recognized"
            );
        }

        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($criterion)) {
                return $visitor->{ $this->contextMethodMap[$context] }($criterion, $this, $languageFilter);
            }
        }

        throw new NotImplementedException(
            'No visitor available for: ' . get_class($criterion) . ' with operator ' . $criterion->operator
        );
    }
}
