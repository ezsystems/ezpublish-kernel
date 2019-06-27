<?php

/**
 * File containing the GeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\Output;

use PHPUnit\Framework\TestCase;

/**
 * Output generator test class.
 */
abstract class GeneratorTest extends TestCase
{
    /** @var \eZ\Publish\Core\REST\Common\Output\Generator */
    protected $generator;

    /**
     * @return \eZ\Publish\Core\REST\Common\Output\Generator
     */
    abstract protected function getGenerator();

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidDocumentStart()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startDocument('test');
    }

    public function testValidDocumentStartAfterReset()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->reset();
        $generator->startDocument('test');

        $this->assertNotNull($generator->endDocument('test'));
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidDocumentNameEnd()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->endDocument('invalid');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidOuterElementStart()
    {
        $generator = $this->getGenerator();

        $generator->startObjectElement('element');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidElementEnd()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');
        $generator->endObjectElement('invalid');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidDocumentEnd()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');
        $generator->endDocument('test');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidAttributeOuterStart()
    {
        $generator = $this->getGenerator();

        $generator->startAttribute('attribute', 'value');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidAttributeDocumentStart()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startAttribute('attribute', 'value');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidAttributeListStart()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');
        $generator->startList('list');
        $generator->startAttribute('attribute', 'value');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidValueElementOuterStart()
    {
        $generator = $this->getGenerator();

        $generator->startValueElement('element', 'value');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidValueElementDocumentStart()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startValueElement('element', 'value');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidListOuterStart()
    {
        $generator = $this->getGenerator();

        $generator->startList('list');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidListDocumentStart()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startList('list');
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidListListStart()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');
        $generator->startList('list');
        $generator->startList('attribute', 'value');
    }

    public function testEmptyDocument()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $this->assertTrue($generator->isEmpty());
    }

    public function testNonEmptyDocument()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');

        $this->assertFalse($generator->isEmpty());
    }
}
