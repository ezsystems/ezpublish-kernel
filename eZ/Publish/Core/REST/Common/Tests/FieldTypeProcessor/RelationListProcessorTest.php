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

    public function testPostProcessValueHash()
    {
        $processor = $this->getProcessor();

        $routerMock = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
        $processor->setRouter($routerMock);

        $routerMock
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                ['ezpublish_rest_loadContent', ['contentId' => 42]],
                ['ezpublish_rest_loadContent', ['contentId' => 300]]
            )->willReturnOnConsecutiveCalls(
                '/api/ezp/v2/content/objects/42',
                '/api/ezp/v2/content/objects/300'
            );

        $hash = $processor->postProcessValueHash(['destinationContentIds' => [42, 300]]);
        $this->assertArrayHasKey('destinationContentHrefs', $hash);
        $this->assertEquals('/api/ezp/v2/content/objects/42', $hash['destinationContentHrefs'][0]);
        $this->assertEquals('/api/ezp/v2/content/objects/300', $hash['destinationContentHrefs'][1]);
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationListProcessor
     */
    protected function getProcessor()
    {
        return new RelationListProcessor();
    }
}
