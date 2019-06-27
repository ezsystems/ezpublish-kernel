<?php

/**
 * File containing the PageServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Page;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\Page\PageStorage;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\Parts\Item;
use eZ\Publish\Core\FieldType\Page\Parts\Page;
use eZ\Publish\Core\FieldType\Page\Parts\Zone;
use eZ\Publish\Core\FieldType\Page\Value;
use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use PHPUnit\Framework\TestCase;

class PageServiceTest extends TestCase
{
    /**
     * Class to instantiate to get the page service.
     */
    const PAGESERVICE_CLASS = PageService::class;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $storageGateway;

    /** @var \eZ\Publish\Core\FieldType\Page\PageService */
    protected $pageService;

    /** @var array */
    protected $zoneDefinition;

    /** @var array */
    protected $blockDefinition;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    protected function setUp()
    {
        parent::setUp();
        $this->zoneDefinition = $this->getZoneDefinition();
        $this->blockDefinition = $this->getBlockDefinition();

        $this->storageGateway = $this->createMock(PageStorage\Gateway::class);
        $this->contentService = $this->createMock(ContentService::class);
        $pageServiceClass = static::PAGESERVICE_CLASS;
        $this->pageService = new $pageServiceClass(
            $this->contentService,
            $this->zoneDefinition,
            $this->blockDefinition
        );
    }

    /**
     * Returns zone definition to test with.
     *
     * @return array
     */
    protected function getZoneDefinition()
    {
        return [
            'globalZoneLayout' => [
                'zoneTypeName' => 'Global zone layout',
                'zones' => [
                    'main' => ['name' => 'Global zone'],
                ],
                'zoneThumbnail' => 'globalzone_layout.gif',
                'template' => '::globalzonelayout.html.twig',
                'availableForClasses' => ['global_layout'],
            ],
            '2zonesLayout1' => [
                'zoneTypeName' => '2 zones (layout 1)',
                'zones' => [
                    'left' => ['name' => 'Left zone'],
                    'right' => ['name' => 'Right zone'],
                ],
                'zoneThumbnail' => '2zones_layout1.gif',
                'template' => '::2zoneslayout1.html.twig',
                'availableForClasses' => ['frontpage'],
            ],
        ];
    }

    /**
     * Returns block definition to test with.
     *
     * @return array
     */
    protected function getBlockDefinition()
    {
        return [
            'campaign' => [
                'name' => 'Campaign',
                'numberOfValidItems' => 5,
                'numberOfArchivedItems' => 5,
                'manualAddingOfItems' => 'enabled',
                'views' => [
                    'default' => ['name' => 'Default'],
                ],
            ],
            'mainStory' => [
                'name' => 'Main story',
                'numberOfValidItems' => 1,
                'numberOfArchivedItems' => 5,
                'manualAddingOfItems' => 'enabled',
                'views' => [
                    'default' => ['name' => 'Default'],
                    'highlighted' => ['name' => 'Highlighted'],
                ],
            ],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getZoneDefinition
     */
    public function testGetZoneDefinition()
    {
        $this->assertSame($this->zoneDefinition, $this->pageService->getZoneDefinition());
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getZoneDefinitionByLayout
     */
    public function testGetZoneDefinitionByLayout()
    {
        $this->assertSame($this->zoneDefinition['globalZoneLayout'], $this->pageService->getZoneDefinitionByLayout('globalZoneLayout'));
    }

    /**
     * @expectedException \OutOfBoundsException
     *
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getZoneDefinitionByLayout
     */
    public function testGetZoneDefinitionByLayoutInvalidLayout()
    {
        $this->pageService->getZoneDefinitionByLayout('invalid_layout');
    }

    public function getLayoutTemplateProvider()
    {
        return [
            ['globalZoneLayout', '::globalzonelayout.html.twig'],
            ['2zonesLayout1', '::2zoneslayout1.html.twig'],
        ];
    }

    /**
     * @dataProvider getLayoutTemplateProvider
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getLayoutTemplate
     */
    public function testGetLayoutTemplate($zoneLayout, $expectedTemplate)
    {
        $this->assertSame($expectedTemplate, $this->pageService->getLayoutTemplate($zoneLayout));
    }

    /**
     * @expectedException \OutOfBoundsException
     *
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getLayoutTemplate
     */
    public function testGetLayoutTemplateInvalidLayout()
    {
        $this->pageService->getLayoutTemplate('invalid_layout');
    }

    public function hasZoneLayoutProvider()
    {
        return [
            ['globalZoneLayout', true],
            ['2zonesLayout1', true],
            ['2zonesLayout2', false],
            ['invalid_layout', false],
        ];
    }

    /**
     * @dataProvider hasZoneLayoutProvider
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::hasZoneLayout
     *
     * @param string $layout
     * @param bool $expectedResult
     */
    public function testHasZoneLayout($layout, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->pageService->hasZoneLayout($layout));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getAvailableZoneLayouts
     */
    public function testGetAvailableZoneLayouts()
    {
        $this->assertSame(array_keys($this->zoneDefinition), $this->pageService->getAvailableZoneLayouts());
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getBlockDefinition
     */
    public function testGetBlockDefinition()
    {
        $this->assertSame($this->blockDefinition, $this->pageService->getBlockDefinition());
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getBlockDefinitionByIdentifier
     */
    public function testGetBlockDefinitionByIdentifier()
    {
        $this->assertSame($this->blockDefinition['mainStory'], $this->pageService->getBlockDefinitionByIdentifier('mainStory'));
    }

    /**
     * @expectedException \OutOfBoundsException
     *
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getBlockDefinitionByIdentifier
     */
    public function testGetBlockDefinitionByIdentifierInvalidBlock()
    {
        $this->pageService->getBlockDefinitionByIdentifier('invalid_block_identifier');
    }

    public function hasBlockDefinitionProvider()
    {
        return [
            ['campaign', true],
            ['mainStory', true],
            ['invalid_block_identifier', false],
            ['foobar', false],
        ];
    }

    /**
     * @dataProvider hasBlockDefinitionProvider
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::hasBlockDefinition
     *
     * @param string $blockIdentifier
     * @param bool $expectedResult
     */
    public function testHasBlockDefinition($blockIdentifier, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->pageService->hasBlockDefinition($blockIdentifier));
    }

    /**
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    protected function buildBlock()
    {
        return new Block(
            ['id' => md5(mt_rand() . microtime())]
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::setStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::hasStorageGateway
     */
    public function testGetStorageGateway()
    {
        $this->assertFalse($this->pageService->hasStorageGateway());
        $this->pageService->setStorageGateway($this->storageGateway);
        $this->assertTrue($this->pageService->hasStorageGateway());
    }

    /**
     * @expectedException \RuntimeException
     *
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::hasStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getValidBlockItems
     */
    public function testGetValidBlockItemsNoGateway()
    {
        $this->pageService->getValidBlockItems($this->buildBlock());
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::hasStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getValidBlockItems
     */
    public function testGetValidBlockItems()
    {
        $block = $this->buildBlock();
        $items = [
            new Item(),
            new Item(),
        ];

        $this->storageGateway
            ->expects($this->once())
            ->method('getValidBlockItems')
            ->with($block)
            ->will($this->returnValue($items));
        $this->pageService->setStorageGateway($this->storageGateway);
        // Calling assertion twice to test cache (comes along with storage gateway's getValidBlockItems() that should be called only once. See above)
        $this->assertSame($items, $this->pageService->getValidBlockItems($block));
        $this->assertSame($items, $this->pageService->getValidBlockItems($block));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::hasStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getLastValidBlockItem
     */
    public function testGetLastValidBlockItem()
    {
        $block = $this->buildBlock();
        $lastValidItem = new Item();

        $this->storageGateway
            ->expects($this->once())
            ->method('getLastValidBlockItem')
            ->with($block)
            ->will($this->returnValue($lastValidItem));
        $this->pageService->setStorageGateway($this->storageGateway);
        // Calling assertion twice to test cache (comes along with storage gateway's getLastValidBlockItem() that should be called only once. See above)
        $this->assertSame($lastValidItem, $this->pageService->getLastValidBlockItem($block));
        $this->assertSame($lastValidItem, $this->pageService->getLastValidBlockItem($block));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::hasStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getWaitingBlockItems
     */
    public function testGetWaitingBlockItems()
    {
        $block = $this->buildBlock();
        $items = [
            new Item(),
            new Item(),
        ];

        $this->storageGateway
            ->expects($this->once())
            ->method('getWaitingBlockItems')
            ->with($block)
            ->will($this->returnValue($items));
        $this->pageService->setStorageGateway($this->storageGateway);
        // Calling assertion twice to test cache (comes along with storage gateway's getWaitingBlockItems() that should be called only once. See above)
        $this->assertSame($items, $this->pageService->getWaitingBlockItems($block));
        $this->assertSame($items, $this->pageService->getWaitingBlockItems($block));
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::hasStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getStorageGateway
     * @covers \eZ\Publish\Core\FieldType\Page\PageService::getArchivedBlockItems
     */
    public function testGetArchivedBlockItems()
    {
        $block = $this->buildBlock();
        $items = [
            new Item(),
            new Item(),
        ];

        $this->storageGateway
            ->expects($this->once())
            ->method('getArchivedBlockItems')
            ->with($block)
            ->will($this->returnValue($items));
        $this->pageService->setStorageGateway($this->storageGateway);
        // Calling assertion twice to test cache (comes along with storage gateway's getArchivedBlockItems() that should be called only once. See above)
        $this->assertSame($items, $this->pageService->getArchivedBlockItems($block));
        $this->assertSame($items, $this->pageService->getArchivedBlockItems($block));
    }

    public function testLoadBlock()
    {
        $contentId = 12;
        $blockId = 'abc0123';
        $block = new Block(['id' => $blockId]);
        $zone = new Zone(['blocks' => [$block]]);
        $page = new Page(['zones' => [$zone]]);
        $value = new Value($page);
        $field = new Field(['value' => $value]);
        $content = new Content(['internalFields' => [$field]]);

        $this->pageService->setStorageGateway($this->storageGateway);
        $this->storageGateway
            ->expects($this->once())
            ->method('getContentIdByBlockId')
            ->with($blockId)
            ->will($this->returnValue($contentId));

        $this->contentService
            ->expects($this->once())
            ->method('loadContent')
            ->with($contentId)
            ->will($this->returnValue($content));

        // Calling assertion twice to test cache (comes along with storage gateway's
        // getLocationIdByBlockId() that should be called only once. See above)
        $this->assertSame($block, $this->pageService->loadBlock($blockId));
        $this->assertSame($block, $this->pageService->loadBlock($blockId));
    }
}
