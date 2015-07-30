<?php

/**
 * File containing the RelationListProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationListProcessor;
use PHPUnit_Framework_TestCase;

class RelationListProcessorTest extends PHPUnit_Framework_TestCase
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
                    array('selectionMethod' => constant("eZ\\Publish\\Core\\FieldType\\RelationList\\Type::{$constantName}")),
                );
            },
            $this->constants
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationListProcessor::preProcessFieldSettingsHash
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
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationListProcessor::postProcessFieldSettingsHash
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
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationListProcessor
     */
    protected function getProcessor()
    {
        return new RelationListProcessor();
    }
}
