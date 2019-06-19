<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\SearchHandler/TransformationProcessorDefinitionBasedParserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Tests\TransformationProcessor;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence;

/**
 * Test case for LocationHandlerTest.
 */
class TransformationProcessorDefinitionBasedParserTest extends TestCase
{
    public static function getTestFiles()
    {
        return array_map(
            function ($file) {
                return [realpath($file)];
            },
            glob(__DIR__ . '/_fixtures/transformations/*.tr')
        );
    }

    /**
     * @dataProvider getTestFiles
     */
    public function testParse($file)
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser();

        $fixture = include $file . '.result';
        $this->assertEquals(
            $fixture,
            $parser->parse($file)
        );
    }
}
