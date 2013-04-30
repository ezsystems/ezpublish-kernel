<?php
/**
 * File containing the XmlTextProcessorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\XmlTextProcessor;
use PHPUnit_Framework_TestCase;

class XmlTextProcessorTest extends PHPUnit_Framework_TestCase
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
                    array( "tagPreset" => constant( "eZ\\Publish\\Core\\FieldType\\XmlText\\Type::{$constantName}" ) )
                );
            },
            $this->constants
        );
    }

    /**
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
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\XmlTextProcessor
     */
    protected function getProcessor()
    {
        return new XmlTextProcessor;
    }
}
