<?php

/**
 * File containing the SessionInputTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Server\Input\Parser\ViewInput;
use eZ\Publish\Core\REST\Server\Values\RestViewInput;

class ViewInputTest extends BaseTest
{
    /**
     * Tests the ViewInput parser.
     */
    public function testParse()
    {
        $inputArray = [
            'identifier' => 'Query identifier',
            'Query' => [],
        ];

        $parser = $this->getParser();
        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects($this->once())
            ->method('parse')
            ->with($inputArray['Query'], 'application/vnd.ez.api.internal.ContentQuery')
            ->will($this->returnValue(new Query()));

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedViewInput = new RestViewInput();
        $expectedViewInput->identifier = 'Query identifier';
        $expectedViewInput->query = new Query();

        $this->assertEquals($expectedViewInput, $result, 'RestViewInput not created correctly.');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testThrowsExceptionOnMissingIdentifier()
    {
        $inputArray = ['Query' => []];
        $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testThrowsExceptionOnMissingQuery()
    {
        $inputArray = ['identifier' => 'foo'];
        $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the session input parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ViewInput
     */
    protected function internalGetParser()
    {
        return new ViewInput();
    }
}
