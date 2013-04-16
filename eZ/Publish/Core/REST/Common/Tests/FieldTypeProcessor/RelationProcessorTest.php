<?php
/**
 * File containing the RelationProcessorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationProcessor;
use PHPUnit_Framework_TestCase;

class RelationProcessorTest extends PHPUnit_Framework_TestCase
{
    protected $constants = array(
        "SELECTION_BROWSE",
        "SELECTION_DROPDOWN"
    );

    public function fieldSettingsHashes()
    {
        return array_map(
            function( $constantName )
            {
                return array(
                    array( "selectionMethod" => $constantName ),
                    array( "selectionMethod" => constant( "eZ\\Publish\\Core\\FieldType\\Relation\\Type::{$constantName}" ) )
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
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationProcessor
     */
    protected function getProcessor()
    {
        return new RelationProcessor;
    }
}
