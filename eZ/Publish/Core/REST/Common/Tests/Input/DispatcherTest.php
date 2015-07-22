<?php

/**
 * File containing the DispatcherTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Input;

use eZ\Publish\Core\REST\Common;
use PHPUnit_Framework_TestCase;

/**
 * Dispatcher test class.
 */
class DispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testParseMissingContentType()
    {
        $message = new Common\Message();

        $parsingDispatcher = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher');
        $dispatcher = new Common\Input\Dispatcher($parsingDispatcher);

        $dispatcher->parse($message);
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testParseInvalidContentType()
    {
        $message = new Common\Message(
            array(
                'Content-Type' => 'text/html',
            )
        );

        $parsingDispatcher = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher');
        $dispatcher = new Common\Input\Dispatcher($parsingDispatcher);

        $dispatcher->parse($message);
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testParseMissingFormatHandler()
    {
        $message = new Common\Message(
            array(
                'Content-Type' => 'text/html+unknown',
            )
        );

        $parsingDispatcher = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher');
        $dispatcher = new Common\Input\Dispatcher($parsingDispatcher);

        $dispatcher->parse($message);
    }

    public function testParse()
    {
        $message = new Common\Message(
            array(
                'Content-Type' => 'text/html+format',
            ),
            'Hello world!'
        );

        $parsingDispatcher = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher');
        $parsingDispatcher
            ->expects($this->at(0))
            ->method('parse')
            ->with(array(42), 'text/html')
            ->will($this->returnValue(23));

        $handler = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Input\\Handler');
        $handler
            ->expects($this->at(0))
            ->method('convert')
            ->with('Hello world!')
            ->will($this->returnValue(array(array(42))));

        $dispatcher = new Common\Input\Dispatcher($parsingDispatcher, array('format' => $handler));

        $this->assertSame(
            23,
            $dispatcher->parse($message)
        );
    }

    /**
     * @todo This is a test for a feature that needs refactoring. There must be
     * a sensible way to submit the called URL to the parser.
     */
    public function testParseSpecialUrlHeader()
    {
        $message = new Common\Message(
            array(
                'Content-Type' => 'text/html+format',
                'Url' => '/foo/bar',
            ),
            'Hello world!'
        );

        $parsingDispatcher = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher');
        $parsingDispatcher
            ->expects($this->at(0))
            ->method('parse')
            ->with(array('someKey' => 'someValue', '__url' => '/foo/bar'), 'text/html')
            ->will($this->returnValue(23));

        $handler = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Input\\Handler');
        $handler
            ->expects($this->at(0))
            ->method('convert')
            ->with('Hello world!')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'someKey' => 'someValue',
                        ),
                    )
                )
            );

        $dispatcher = new Common\Input\Dispatcher($parsingDispatcher, array('format' => $handler));

        $this->assertSame(
            23,
            $dispatcher->parse($message)
        );
    }
}
