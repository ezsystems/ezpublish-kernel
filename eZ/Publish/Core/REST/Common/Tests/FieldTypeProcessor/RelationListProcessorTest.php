<?php

/**
 * File containing the RelationListProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationListProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class RelationListProcessorTest extends TestCase
{
    protected $constants = [
        'SELECTION_BROWSE',
        'SELECTION_DROPDOWN',
    ];

    public function fieldSettingsHashes()
    {
        return array_map(
            function ($constantName) {
                return [
                    ['selectionMethod' => $constantName],
                    ['selectionMethod' => constant("eZ\\Publish\\Core\\FieldType\\RelationList\\Type::{$constantName}")],
                ];
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

    public function testpostProcessFieldSettingsHashLocation()
    {
        $processor = $this->getProcessor();

        $serviceLocationMock = $this->createMock(LocationService::class);
        $processor->setLocationService($serviceLocationMock);

        $serviceLocationMock
            ->method('loadLocation')
            ->with('42')
            ->willReturn(new Location(['path' => ['1', '25', '42']]));

        $routerMock = $this->createMock(RouterInterface::class);
        $processor->setRouter($routerMock);

        $routerMock
            ->method('generate')
            ->with('ezpublish_rest_loadLocation', ['locationPath' => '1/25/42'])
            ->willReturn('/api/ezp/v2/content/locations/1/25/42');

        $hash = $processor->postProcessFieldSettingsHash(['selectionDefaultLocation' => 42]);

        $this->assertEquals([
            'selectionDefaultLocation' => 42,
            'selectionDefaultLocationHref' => '/api/ezp/v2/content/locations/1/25/42',
        ], $hash);

        //empty cases
        $hash = $processor->postProcessFieldSettingsHash(['selectionDefaultLocation' => '']);
        $this->assertEquals(['selectionDefaultLocation' => ''], $hash);
        $hash = $processor->postProcessFieldSettingsHash(['selectionDefaultLocation' => null]);
        $this->assertEquals(['selectionDefaultLocation' => null], $hash);
    }

    public function testPostProcessValueHash()
    {
        $processor = $this->getProcessor();

        $routerMock = $this->createMock(RouterInterface::class);
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
