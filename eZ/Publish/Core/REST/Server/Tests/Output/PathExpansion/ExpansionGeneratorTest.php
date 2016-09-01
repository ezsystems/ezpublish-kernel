<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\PathExpansion;

use eZ\Publish\Core\REST\Server\Output\PathExpansion\ExpansionGenerator;
use PHPUnit_Framework_TestCase;

class ExpansionGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\REST\Common\Output\Generator
     */
    protected $innerGeneratorMock;

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\BadStateException
     */
    public function testStartDocument()
    {
        $this->buildGenerator()->startDocument('tenant');
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\BadStateException
     */
    public function testEndDocument()
    {
        $this->buildGenerator()->endDocument('tenant');
    }

    public function testIsEmpty()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('isEmpty');

        $this->buildGenerator()->isEmpty();
    }

    public function testStartEndObjectElement()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('startObjectElement')
            ->with('pertwee', 'doctor');

        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('endObjectElement')
            ->with('pertwee');

        $generator = $this->buildGenerator();
        $generator->startObjectElement('jon', 'doctor');
        $generator->startObjectElement('pertwee', 'doctor');
        $generator->endObjectElement('pertwee');
        $generator->endObjectElement('jon');
    }

    public function testStartHashElement()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('startHashElement')
            ->with('baker');

        $this->buildGenerator()->startHashElement('baker');
    }

    public function testEndHashElement()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('endHashElement')
            ->with('baker');

        $this->buildGenerator()->endHashElement('baker');
    }

    public function testStartValueElement()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('startValueElement')
            ->with('baker', 'paul');

        $this->buildGenerator()->startValueElement('baker', 'paul');
    }

    public function testEndValueElement()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('endValueElement')
            ->with('baker');

        $this->buildGenerator()->endValueElement('baker');
    }

    public function testStartList()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('startList')
            ->with('hartnell');

        $this->buildGenerator()->startList('hartnell');
    }

    public function testEndList()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('endList')
            ->with('hartnell');

        $this->buildGenerator()->endList('hartnell');
    }

    public function testStartEndAttribute()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('startObjectElement')
            ->with('eccleston', 'doctor');

        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('startAttribute')
            ->with('jacket', 'leather');

        $generator = $this->buildGenerator();

        $generator->startObjectElement('smith');
        $generator->startObjectElement('eccleston', 'doctor');
        $generator->startAttribute('jacket', 'leather');
    }

    public function testStartEndAttributeAtRoot()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->never())
            ->method('startObjectElement');

        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('startAttribute');

        $this->getInnerGeneratorMock()
            ->expects($this->never())
            ->method('endAttribute');

        $this->buildGenerator()->startObjectElement('eccleston');
        $this->buildGenerator()->startAttribute('href', 'htt://google.cm');
        $this->buildGenerator()->endAttribute('href');
        $this->buildGenerator()->startAttribute('jacket', 'leather');
        $this->buildGenerator()->endAttribute('jacket');
    }

    public function testGetMediaType()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('getMediaType')
            ->with('foo')
            ->willReturn('application/vnd.ez.api.foo');

        self::assertEquals('application/vnd.ez.api.foo', $this->buildGenerator()->getMediaType('foo'));
    }

    public function testGenerateFieldTypeHash()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('generateFieldTypeHash')
            ->with('smith', []);

        $this->buildGenerator()->generateFieldTypeHash('smith', []);
    }

    public function testSerializeBool()
    {
        $this->getInnerGeneratorMock()
            ->expects($this->once())
            ->method('serializeBool')
            ->with(true)
            ->willReturn('true');

        self::assertEquals('true', $this->buildGenerator()->serializeBool(true));
    }

    /**
     * @return ExpansionGenerator
     */
    protected function buildGenerator()
    {
        return new ExpansionGenerator($this->getInnerGeneratorMock());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\REST\Common\Output\Generator
     */
    protected function getInnerGeneratorMock()
    {
        if (!isset($this->innerGeneratorMock)) {
            $this->innerGeneratorMock = $this->getMockBuilder('eZ\Publish\Core\REST\Common\Output\Generator')->getMock();
        }

        return $this->innerGeneratorMock;
    }
}
