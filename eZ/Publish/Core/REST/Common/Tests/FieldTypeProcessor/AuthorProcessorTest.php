<?php

/**
 * File containing the DateProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\AuthorProcessor;
use PHPUnit\Framework\TestCase;

class AuthorProcessorTest extends TestCase
{
    protected $constants = [
        'DEFAULT_VALUE_EMPTY',
        'DEFAULT_CURRENT_USER',
    ];

    public function fieldSettingsHashes()
    {
        return array_map(
            function ($constantName) {
                return [
                    ['defaultAuthor' => $constantName],
                    ['defaultAuthor' => constant("eZ\\Publish\\Core\\FieldType\\Author\\Type::{$constantName}")],
                ];
            },
            $this->constants
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\AuthorProcessor::preProcessFieldSettingsHash
     * @dataProvider fieldSettingsHashes
     */
    public function testPreProcessFieldSettingsHash($inputSettings, $outputSettings)
    {
        $processor = $this->getProcessor();

        $this->assertEquals(
            $outputSettings,
            $processor->preProcessFieldSettingsHash($inputSettings)
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\AuthorProcessor::postProcessFieldSettingsHash
     * @dataProvider fieldSettingsHashes
     */
    public function testPostProcessFieldSettingsHash($outputSettings, $inputSettings)
    {
        $processor = $this->getProcessor();

        $this->assertEquals(
            $outputSettings,
            $processor->postProcessFieldSettingsHash($inputSettings)
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\AuthorProcessor
     */
    protected function getProcessor()
    {
        return new AuthorProcessor();
    }
}
