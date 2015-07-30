<?php

/**
 * File containing the VisitorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Common\Tests\Output;

use eZ\Publish\Core\REST\Common;
use stdClass;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Visitor test.
 */
class VisitorTest extends PHPUnit_Framework_TestCase
{
    public function testVisitDocument()
    {
        $data = new stdClass();

        $generator = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        $generator
            ->expects($this->at(1))
            ->method('startDocument')
            ->with($data);

        $generator
            ->expects($this->at(2))
            ->method('isEmpty')
            ->will($this->returnValue(false));

        $generator
            ->expects($this->at(3))
            ->method('endDocument')
            ->with($data)
            ->will($this->returnValue('Hello world!'));

        $visitor = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
            array('visitValueObject'),
            array($generator, $this->getValueObjectDispatcherMock())
        );

        $this->assertEquals(
            new Response('Hello world!', 200, array()),
            $visitor->visit($data)
        );
    }

    public function testVisitEmptyDocument()
    {
        $data = new stdClass();

        $generator = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        $generator
            ->expects($this->at(1))
            ->method('startDocument')
            ->with($data);

        $generator
            ->expects($this->at(2))
            ->method('isEmpty')
            ->will($this->returnValue(true));

        $generator
            ->expects($this->never())
            ->method('endDocument');

        $visitor = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
            array('visitValueObject'),
            array($generator, $this->getValueObjectDispatcherMock())
        );

        $this->assertEquals(
            new Response(null, 200, array()),
            $visitor->visit($data)
        );
    }

    public function testVisitValueObject()
    {
        $data = new stdClass();

        /** @var \PHPUnit_Framework_MockObject_MockObject|Common\Output\Generator $generatorMock */
        $generatorMock = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');

        $valueObjectDispatcherMock = $this->getValueObjectDispatcherMock();
        $valueObjectDispatcherMock
            ->expects($this->once())
            ->method('visit')
            ->with($data);

        $visitor = new Common\Output\Visitor($generatorMock, $valueObjectDispatcherMock);
        $visitor->visit($data);
    }

    public function testSetHeaders()
    {
        $data = new stdClass();

        $generator = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        $visitor = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
            array('visitValueObject'),
            array($generator, $this->getValueObjectDispatcherMock())
        );

        $visitor->setHeader('Content-Type', 'text/xml');
        $this->assertEquals(
            new Response(
                null,
                200,
                array(
                    'Content-Type' => 'text/xml',
                )
            ),
            $visitor->visit($data)
        );
    }

    /**
     * @todo This is a test for a feature that needs refactoring.
     *
     * @see \eZ\Publish\Core\REST\Common\Output\Visitor::visit
     */
    public function testSetFilteredHeaders()
    {
        $data = new stdClass();

        $generator = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        $visitor = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
            array('visitValueObject'),
            array($generator, $this->getValueObjectDispatcherMock())
        );

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Accept-Patch', false);
        $this->assertEquals(
            new Response(
                null,
                200,
                array(
                    'Content-Type' => 'text/xml',
                )
            ),
            $visitor->visit($data)
        );
    }

    public function testSetHeadersNoOverwrite()
    {
        $data = new stdClass();

        $generator = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        $visitor = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
            array('visitValueObject'),
            array($generator, $this->getValueObjectDispatcherMock())
        );

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Content-Type', 'text/html');
        $this->assertEquals(
            new Response(
                null,
                200,
                array(
                    'Content-Type' => 'text/xml',
                )
            ),
            $visitor->visit($data)
        );
    }

    public function testSetHeaderResetAfterVisit()
    {
        $data = new stdClass();

        $generator = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        $visitor = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
            array('visitValueObject'),
            array($generator, $this->getValueObjectDispatcherMock())
        );

        $visitor->setHeader('Content-Type', 'text/xml');

        $visitor->visit($data);
        $result = $visitor->visit($data);

        $this->assertEquals(
            new Response(
                null,
                200,
                array()
            ),
            $result
        );
    }

    public function testSetStatusCode()
    {
        $data = new stdClass();

        $generator = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        $visitor = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
            array('visitValueObject'),
            array($generator, $this->getValueObjectDispatcherMock())
        );

        $visitor->setStatus(201);
        $this->assertEquals(
            new Response(
                null,
                201
            ),
            $visitor->visit($data)
        );
    }

    public function testSetStatusCodeNoOverride()
    {
        $data = new stdClass();

        $generator = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        $visitor = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
            array('visitValueObject'),
            array($generator, $this->getValueObjectDispatcherMock())
        );

        $visitor->setStatus(201);
        $visitor->setStatus(404);

        $this->assertEquals(
            new Response(
                null,
                201
            ),
            $visitor->visit($data)
        );
    }

    /**
     * @return Common\Output\ValueObjectVisitorDispatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getValueObjectDispatcherMock()
    {
        return $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Output\\ValueObjectVisitorDispatcher'
        );
    }
}
