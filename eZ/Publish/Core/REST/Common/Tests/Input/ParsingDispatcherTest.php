<?php

/**
 * File containing the ParsingDispatcherTest class.
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
 * ParsingDispatcher test class.
 */
class ParsingDispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testParseMissingContentType()
    {
        $dispatcher = new Common\Input\ParsingDispatcher();

        $dispatcher->parse(array(), 'text/unknown');
    }

    public function testParse()
    {
        $parser = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Input\\Parser');
        $dispatcher = new Common\Input\ParsingDispatcher(['text/html' => $parser]);

        $parser
            ->expects($this->at(0))
            ->method('parse')
            ->with(array(42), $dispatcher)
            ->will($this->returnValue(23));

        $this->assertSame(
            23,
            $dispatcher->parse(array(42), 'text/html')
        );
    }

    public function testParseStripFormat()
    {
        $parser = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Input\\Parser');
        $dispatcher = new Common\Input\ParsingDispatcher(['text/html' => $parser]);

        $parser
            ->expects($this->at(0))
            ->method('parse')
            ->with(array(42), $dispatcher)
            ->will($this->returnValue(23));

        $this->assertSame(
            23,
            $dispatcher->parse(array(42), 'text/html+json')
        );
    }
}
