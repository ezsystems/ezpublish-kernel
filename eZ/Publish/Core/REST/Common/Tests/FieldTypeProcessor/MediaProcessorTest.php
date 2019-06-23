<?php

/**
 * File containing the MediaProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\MediaProcessor;

class MediaProcessorTest extends BinaryInputProcessorTest
{
    protected $constants = [
        'TYPE_FLASH',
        'TYPE_QUICKTIME',
        'TYPE_REALPLAYER',
        'TYPE_SILVERLIGHT',
        'TYPE_WINDOWSMEDIA',
        'TYPE_HTML5_VIDEO',
        'TYPE_HTML5_AUDIO',
    ];

    public function fieldSettingsHashes()
    {
        return array_map(
            function ($constantName) {
                return [
                    ['mediaType' => $constantName],
                    ['mediaType' => constant("eZ\\Publish\\Core\\FieldType\\Media\\Type::{$constantName}")],
                ];
            },
            $this->constants
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\MediaProcessor::preProcessFieldSettingsHash
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
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\MediaProcessor::postProcessFieldSettingsHash
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
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\MediaProcessor
     */
    protected function getProcessor()
    {
        return new MediaProcessor($this->getTempDir());
    }
}
