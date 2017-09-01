<?php

/**
 * File containing the SessionInputTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Server\Input\Parser\ContentQuery as QueryParser;

class QueryParserTest extends BaseTest
{
    public function testParseEmptyQuery()
    {
        $inputArray = array(
            'Filter' => [],
            'Criteria' => [],
            'Query' => [],
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();

        $this->assertEquals($expectedQuery, $result);
    }

    public function testDispatchOneFilter()
    {
        $inputArray = array(
            'Filter' => ['ContentTypeIdentifierCriterion' => 'article'],
            'Criteria' => [],
            'Query' => [],
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects($this->once())
            ->method('parse')
            ->with(['ContentTypeIdentifierCriterion' => 'article'])
            ->will($this->returnValue(new Query\Criterion\Matcher\ContentTypeIdentifier('article')));

        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();
        $expectedQuery->filter = new Query\Criterion\Matcher\ContentTypeIdentifier('article');

        $this->assertEquals($expectedQuery, $result);
    }

    public function testDispatchMoreThanOneFilter()
    {
        $inputArray = array(
            'Filter' => ['ContentTypeIdentifierCriterion' => 'article', 'ParentLocationIdCriterion' => 762],
            'Criteria' => [],
            'Query' => [],
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects($this->at(0))
            ->method('parse')
            ->with(['ContentTypeIdentifierCriterion' => 'article'])
            ->will($this->returnValue(new Query\Criterion\Matcher\ContentTypeIdentifier('article')));
        $parsingDispatcher
            ->expects($this->at(1))
            ->method('parse')
            ->with(['ParentLocationIdCriterion' => 762])
            ->will($this->returnValue(new Query\Criterion\Matcher\ParentLocationId(762)));

        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();
        $expectedQuery->filter = new Query\Criterion\LogicalOperator\LogicalAnd([
            new Query\Criterion\Matcher\ContentTypeIdentifier('article'),
            new Query\Criterion\Matcher\ParentLocationId(762),
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    public function testDispatchOneQueryItem()
    {
        $inputArray = array(
            'Query' => ['ContentTypeIdentifierCriterion' => 'article'],
            'Criteria' => [],
            'Filter' => [],
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects($this->once())
            ->method('parse')
            ->with(['ContentTypeIdentifierCriterion' => 'article'])
            ->will($this->returnValue(new Query\Criterion\Matcher\ContentTypeIdentifier('article')));

        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();
        $expectedQuery->query = new Query\Criterion\Matcher\ContentTypeIdentifier('article');

        $this->assertEquals($expectedQuery, $result);
    }

    public function testDispatchMoreThanOneQueryItem()
    {
        $inputArray = array(
            'Query' => ['ContentTypeIdentifierCriterion' => 'article', 'ParentLocationIdCriterion' => 762],
            'Criteria' => [],
            'Filter' => [],
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects($this->at(0))
            ->method('parse')
            ->with(['ContentTypeIdentifierCriterion' => 'article'])
            ->will($this->returnValue(new Query\Criterion\Matcher\ContentTypeIdentifier('article')));
        $parsingDispatcher
            ->expects($this->at(1))
            ->method('parse')
            ->with(['ParentLocationIdCriterion' => 762])
            ->will($this->returnValue(new Query\Criterion\Matcher\ParentLocationId(762)));

        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();
        $expectedQuery->query = new Query\Criterion\LogicalOperator\LogicalAnd([
            new Query\Criterion\Matcher\ContentTypeIdentifier('article'),
            new Query\Criterion\Matcher\ParentLocationId(762),
        ]);

        $this->assertEquals($expectedQuery, $result);
    }

    /**
     * Returns the session input parser.
     */
    protected function internalGetParser()
    {
        return new QueryParser();
    }
}
