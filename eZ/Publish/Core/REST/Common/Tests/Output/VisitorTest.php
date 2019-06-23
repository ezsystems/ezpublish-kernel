<?php

/**
 * File containing the VisitorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\Output;

use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitorDispatcher;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Visitor test.
 */
class VisitorTest extends TestCase
{
    public function testVisitDocument()
    {
        $data = new stdClass();

        $generator = $this->getGeneratorMock();
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

        $visitor = $this->getMockBuilder(Visitor::class)
            ->setMethods(['visitValueObject'])
            ->setConstructorArgs([$generator, $this->getValueObjectDispatcherMock()])
            ->getMock();

        $this->assertEquals(
            new Response('Hello world!', 200, []),
            $visitor->visit($data)
        );
    }

    public function testVisitEmptyDocument()
    {
        $data = new stdClass();

        $generator = $this->getGeneratorMock();
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

        $visitor = $this->getMockBuilder(Visitor::class)
            ->setMethods(['visitValueObject'])
            ->setConstructorArgs([$generator, $this->getValueObjectDispatcherMock()])
            ->getMock();

        $this->assertEquals(
            new Response(null, 200, []),
            $visitor->visit($data)
        );
    }

    public function testVisitValueObject()
    {
        $data = new stdClass();

        /** @var \PHPUnit\Framework\MockObject\MockObject|Common\Output\Generator $generatorMock */
        $generatorMock = $this->getGeneratorMock();

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

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        $this->assertEquals(
            new Response(
                null,
                200,
                [
                    'Content-Type' => 'text/xml',
                ]
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

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Accept-Patch', false);
        $this->assertEquals(
            new Response(
                null,
                200,
                [
                    'Content-Type' => 'text/xml',
                ]
            ),
            $visitor->visit($data)
        );
    }

    public function testSetHeadersNoOverwrite()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Content-Type', 'text/html');
        $this->assertEquals(
            new Response(
                null,
                200,
                [
                    'Content-Type' => 'text/xml',
                ]
            ),
            $visitor->visit($data)
        );
    }

    public function testSetHeaderResetAfterVisit()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');

        $visitor->visit($data);
        $result = $visitor->visit($data);

        $this->assertEquals(
            new Response(
                null,
                200,
                []
            ),
            $result
        );
    }

    public function testSetStatusCode()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

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

        $visitor = $this->getVisitorMock();

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
     * @return Common\Output\ValueObjectVisitorDispatcher|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getValueObjectDispatcherMock()
    {
        return $this->createMock(ValueObjectVisitorDispatcher::class);
    }

    protected function getGeneratorMock()
    {
        return $this->createMock(Generator::class);
    }

    protected function getVisitorMock()
    {
        return $this->getMockBuilder(Visitor::class)
            ->setMethods(['visitValueObject'])
            ->setConstructorArgs(
                [
                    $this->getGeneratorMock(),
                    $this->getValueObjectDispatcherMock(),
                ]
            )
            ->getMock();
    }
}
