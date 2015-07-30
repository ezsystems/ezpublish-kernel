<?php

/**
 * File containing the RelationProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationProcessor;
use PHPUnit_Framework_TestCase;

class RelationProcessorTest extends PHPUnit_Framework_TestCase
{
    protected $constants = array(
        'SELECTION_BROWSE',
        'SELECTION_DROPDOWN',
    );

    public function fieldSettingsHashes()
    {
        return array_map(
            function ($constantName) {
                return array(
                    array('selectionMethod' => $constantName),
                    array('selectionMethod' => constant("eZ\\Publish\\Core\\FieldType\\Relation\\Type::{$constantName}")),
                );
            },
            $this->constants
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationProcessor::preProcessFieldSettingsHash
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
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationProcessor::postProcessFieldSettingsHash
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
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationProcessor
     */
    protected function getProcessor()
    {
        return new RelationProcessor();
    }
}
