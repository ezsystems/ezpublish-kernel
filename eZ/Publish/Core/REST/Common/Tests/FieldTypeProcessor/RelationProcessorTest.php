<?php

/**
 * File containing the RelationProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class RelationProcessorTest extends TestCase
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
                    ['selectionMethod' => constant("eZ\\Publish\\Core\\FieldType\\Relation\\Type::{$constantName}")],
                ];
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

        $hash = $processor->postProcessFieldSettingsHash(['selectionRoot' => 42]);

        $this->assertEquals([
            'selectionRoot' => 42,
            'selectionRootHref' => '/api/ezp/v2/content/locations/1/25/42',
        ], $hash);

        //empty cases
        $hash = $processor->postProcessFieldSettingsHash(['selectionRoot' => '']);
        $this->assertEquals(['selectionRoot' => ''], $hash);
        $hash = $processor->postProcessFieldSettingsHash(['selectionRoot' => null]);
        $this->assertEquals(['selectionRoot' => null], $hash);
    }

    public function testPostProcessFieldValueHash()
    {
        $processor = $this->getProcessor();

        $routerMock = $this->createMock(RouterInterface::class);
        $processor->setRouter($routerMock);

        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with('ezpublish_rest_loadContent', ['contentId' => 42])
            ->will($this->returnValue('/api/ezp/v2/content/objects/42'));

        $hash = $processor->postProcessValueHash(['destinationContentId' => 42]);
        $this->assertArrayHasKey('destinationContentHref', $hash);
        $this->assertEquals('/api/ezp/v2/content/objects/42', $hash['destinationContentHref']);
    }

    public function testPostProcessFieldValueHashNullValue()
    {
        $processor = $this->getProcessor();

        $routerMock = $this->createMock(RouterInterface::class);
        $processor->setRouter($routerMock);

        $routerMock
            ->expects($this->never())
            ->method('generate');

        $hash = $processor->postProcessValueHash(['destinationContentId' => null]);
        $this->assertArrayNotHasKey('destinationContentHref', $hash);
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\RelationProcessor
     */
    protected function getProcessor()
    {
        return new RelationProcessor();
    }
}
