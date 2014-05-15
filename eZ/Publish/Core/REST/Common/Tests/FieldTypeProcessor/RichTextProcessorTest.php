<?php
/**
 * File containing the RichTextProcessorTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\RichTextProcessor;
use PHPUnit_Framework_TestCase;

class RichTextProcessorTest extends PHPUnit_Framework_TestCase
{
    protected $constants = array(
        "TAG_PRESET_DEFAULT",
        "TAG_PRESET_SIMPLE_FORMATTING"
    );

    public function fieldSettingsHashes()
    {
        return array_map(
            function ( $constantName )
            {
                return array(
                    array( "tagPreset" => $constantName ),
                    array( "tagPreset" => constant( "eZ\\Publish\\Core\\FieldType\\RichText\\Type::{$constantName}" ) )
                );
            },
            $this->constants
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RichTextProcessor::preProcessFieldSettingsHash
     * @dataProvider fieldSettingsHashes
     */
    public function testPreProcessFieldSettingsHash( $inputSettings, $outputSettings )
    {
        $processor = $this->getProcessor();

        $this->assertEquals(
            $outputSettings,
            $processor->preProcessFieldSettingsHash( $inputSettings )
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RichTextProcessor::postProcessFieldSettingsHash
     * @dataProvider fieldSettingsHashes
     */
    public function testPostProcessFieldSettingsHash( $outputSettings, $inputSettings )
    {
        $processor = $this->getProcessor();

        $this->assertEquals(
            $outputSettings,
            $processor->postProcessFieldSettingsHash( $inputSettings )
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RichTextProcessor
     */
    protected function getProcessor()
    {
        return new RichTextProcessor;
    }
}
