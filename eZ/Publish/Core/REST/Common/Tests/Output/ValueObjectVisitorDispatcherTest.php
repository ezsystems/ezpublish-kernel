<?php

/**
 * File containing the ValueObjectVisitorDispatcherTest class.
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

/**
 * Visitor test.
 */
class ValueObjectVisitorDispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Common\Output\Visitor
     */
    private $outputVisitorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Common\Output\Generator
     */
    private $outputGeneratorMock;

    public function testVisitValueObject()
    {
        $data = new stdClass();

        $visitor = $this->getValueObjectVisitorMock();
        $visitor
            ->expects($this->at(0))
            ->method('visit')
            ->with($this->getOutputVisitorMock(), $this->getOutputGeneratorMock(), $data);

        $valueObjectDispatcher = $this->getValueObjectDispatcher();
        $valueObjectDispatcher->addVisitor('stdClass', $visitor);

        $valueObjectDispatcher->visit($data);
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\InvalidTypeException
     */
    public function testVisitValueObjectInvalidType()
    {
        $this->getValueObjectDispatcher()->visit(42);
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\NoVisitorFoundException
     */
    public function testVisitValueObjectNoMatch()
    {
        $dispatcher = $this->getValueObjectDispatcher();

        $dispatcher->visit(new stdClass());
    }

    public function testVisitValueObjectParentMatch()
    {
        $data = new ValueObject();

        $valueObjectVisitor = $this->getValueObjectVisitorMock();
        $valueObjectVisitor
            ->expects($this->at(0))
            ->method('visit')
            ->with($this->getOutputVisitorMock(), $this->getOutputGeneratorMock(), $data);

        $dispatcher = $this->getValueObjectDispatcher();
        $dispatcher->addVisitor('stdClass', $valueObjectVisitor);

        $dispatcher->visit($data);
    }

    public function testVisitValueObjectSecondRuleParentMatch()
    {
        $data = new ValueObject();

        $generator = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        $valueObjectVisitor1 = $this->getValueObjectVisitorMock();
        $valueObjectVisitor2 = $this->getValueObjectVisitorMock();

        $dispatcher = $this->getValueObjectDispatcher();
        $dispatcher->addVisitor('WontMatch', $valueObjectVisitor1);
        $dispatcher->addVisitor('stdClass', $valueObjectVisitor2);

        $valueObjectVisitor1
            ->expects($this->never())
            ->method('visit');

        $valueObjectVisitor2
            ->expects($this->at(0))
            ->method('visit')
            ->with($this->getOutputVisitorMock(), $this->getOutputGeneratorMock(), $data);

        $dispatcher->visit($data);
    }

    /**
     * @return Common\Output\ValueObjectVisitorDispatcher
     */
    private function getValueObjectDispatcher()
    {
        $dispatcher = new Common\Output\ValueObjectVisitorDispatcher();
        $dispatcher->setOutputGenerator($this->getOutputGeneratorMock());
        $dispatcher->setOutputVisitor($this->getOutputVisitorMock());

        return $dispatcher;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor
     */
    private function getValueObjectVisitorMock()
    {
        return $this->getMockForAbstractClass('\\eZ\\Publish\\Core\\REST\\Common\\Output\\ValueObjectVisitor');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Common\Output\Visitor
     */
    private function getOutputVisitorMock()
    {
        if (!isset($this->outputVisitorMock)) {
            $this->outputVisitorMock = $this->getMockBuilder('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor')
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->outputVisitorMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Common\Output\Generator
     */
    private function getOutputGeneratorMock()
    {
        if (!isset($this->outputGeneratorMock)) {
            $this->outputGeneratorMock = $this->getMock('\\eZ\\Publish\\Core\\REST\\Common\\Output\\Generator');
        }

        return $this->outputGeneratorMock;
    }
}
