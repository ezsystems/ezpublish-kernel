<?php

/**
 * File containing a ParserToolsTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Input;

use eZ\Publish\Core\REST\Common\Input\ParserTools;
use PHPUnit_Framework_TestCase;

class ParserToolsTest extends PHPUnit_Framework_TestCase
{
    public function testIsEmbeddedObjectReturnsTrue()
    {
        $parserTools = $this->getParserTools();

        $this->assertTrue(
            $parserTools->isEmbeddedObject(
                array(
                    '_href' => '/foo/bar',
                    '_media-type' => 'application/some-type',
                    'id' => 23,
                )
            )
        );
    }

    public function testIsEmbeddedObjectReturnsFalse()
    {
        $parserTools = $this->getParserTools();

        $this->assertFalse(
            $parserTools->isEmbeddedObject(
                array(
                    '_href' => '/foo/bar',
                    '_media-type' => 'application/some-type',
                )
            )
        );
    }

    public function testParseObjectElementEmbedded()
    {
        $parserTools = $this->getParserTools();

        $dispatcherMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher',
            array(),
            array(),
            '',
            false
        );
        $dispatcherMock->expects($this->once())
            ->method('parse')
            ->with(
                $this->isType('array'),
                $this->equalTo('application/my-type')
            );

        $parsingInput = array(
            '_href' => '/foo/bar',
            '_media-type' => 'application/my-type',
            'someContent' => array(),
        );

        $this->assertEquals(
            '/foo/bar',
            $parserTools->parseObjectElement($parsingInput, $dispatcherMock)
        );
    }

    public function testParseObjectElementNotEmbedded()
    {
        $parserTools = $this->getParserTools();

        $dispatcherMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher',
            array(),
            array(),
            '',
            false
        );
        $dispatcherMock->expects($this->never())
            ->method('parse');

        $parsingInput = array(
            '_href' => '/foo/bar',
            '_media-type' => 'application/my-type',
            '#someTextContent' => 'foo',
        );

        $this->assertEquals(
            '/foo/bar',
            $parserTools->parseObjectElement($parsingInput, $dispatcherMock)
        );
    }

    protected function getParserTools()
    {
        return new ParserTools();
    }
}
