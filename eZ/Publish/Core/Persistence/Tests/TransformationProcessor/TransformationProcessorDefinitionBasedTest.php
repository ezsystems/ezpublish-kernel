<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\SearchHandler\TransformationProcessorDefinitionBasedTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Tests\TransformationProcessor;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence;
use eZ\Publish\Core\Persistence\TransformationProcessor\DefinitionBased;

/**
 * Test case for LocationHandlerTest.
 */
class TransformationProcessorDefinitionBasedTest extends TestCase
{
    public function getProcessor()
    {
        return new DefinitionBased(
            new Persistence\TransformationProcessor\DefinitionBased\Parser(),
            new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter()),
            glob(__DIR__ . '/_fixtures/transformations/*.tr')
        );
    }

    public function testSimpleNormalizationLowercase()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'hello world!',
            $processor->transform('Hello World!', ['ascii_lowercase'])
        );
    }

    public function testSimpleNormalizationUppercase()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'HELLO WORLD!',
            $processor->transform('Hello World!', ['ascii_uppercase'])
        );
    }

    public function testApplyAllLowercaseNormalizations()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'hello world!',
            $processor->transformByGroup('Hello World!', 'lowercase')
        );
    }

    /**
     * The main point of this test is, that it shows that all normalizations
     * available can be compiled without errors. The actual expectation is not
     * important.
     */
    public function testAllNormalizations()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'HELLO WORLD.',
            $processor->transform('Hello World!')
        );
    }
}
