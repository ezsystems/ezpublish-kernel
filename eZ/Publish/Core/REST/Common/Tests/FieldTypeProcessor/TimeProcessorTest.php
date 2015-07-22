<?php

/**
 * File containing the TimeProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\TimeProcessor;
use PHPUnit_Framework_TestCase;

class TimeProcessorTest extends PHPUnit_Framework_TestCase
{
    protected $constants = array(
        'DEFAULT_EMPTY',
        'DEFAULT_CURRENT_TIME',
    );

    public function fieldSettingsHashes()
    {
        return array_map(
            function ($constantName) {
                return array(
                    array('defaultType' => $constantName),
                    array('defaultType' => constant("eZ\\Publish\\Core\\FieldType\\Time\\Type::{$constantName}")),
                );
            },
            $this->constants
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\TimeProcessor::preProcessFieldSettingsHash
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
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\TimeProcessor::postProcessFieldSettingsHash
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
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\TimeProcessor
     */
    protected function getProcessor()
    {
        return new TimeProcessor();
    }
}
