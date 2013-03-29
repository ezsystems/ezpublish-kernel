<?php
/**
 * File containing the MediaProcessorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Server\Tests\BaseTest;
use eZ\Publish\Core\REST\Common\FieldTypeProcessor\MediaProcessor;

class MediaProcessorTest extends BaseTest
{
    protected $constants = array(
        "TYPE_FLASH",
        "TYPE_QUICKTIME",
        "TYPE_REALPLAYER",
        "TYPE_SILVERLIGHT",
        "TYPE_WINDOWSMEDIA",
        "TYPE_HTML5_VIDEO",
        "TYPE_HTML5_AUDIO"
    );

    public function fieldSettingsHashes()
    {
        return array_map(
            function( $constantName )
            {
                return array(
                    array( "mediaType" => $constantName ),
                    array( "mediaType" => constant( "eZ\\Publish\\Core\\FieldType\\Media\\Type::{$constantName}" ) )
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
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\DateAndTimeProcessor
     */
    protected function getProcessor()
    {
        return new MediaProcessor;
    }
}
