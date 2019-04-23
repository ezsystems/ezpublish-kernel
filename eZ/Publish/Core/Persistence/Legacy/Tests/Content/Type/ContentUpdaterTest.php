<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentTypeUpdaterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistryInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\SPI\Persistence\Content\Type;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Content Type Updater.
 */
class ContentUpdaterTest extends TestCase
{
    /**
     * Content gateway mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * FieldValue converter registry mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $converterRegistryMock;

    /**
     * Search handler mock.
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\Handler
     */
    protected $searchHandlerMock;

    /**
     * Content StorageHandler mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $contentStorageHandlerMock;

    /**
     * Content Updater to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater
     */
    protected $contentUpdater;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapperMock;

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater::__construct
     */
    public function testCtor()
    {
        $updater = $this->getContentUpdater();

        $this->assertAttributeSame(
            $this->getContentGatewayMock(),
            'contentGateway',
            $updater
        );
        $this->assertAttributeSame(
            $this->getConverterRegistryMock(),
            'converterRegistry',
            $updater
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater::determineActions
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater::hasFieldDefinition
     */
    public function testDetermineActions()
    {
        $fromType = $this->getFromTypeFixture();
        $toType = $this->getToTypeFixture();

        $converterRegMock = $this->getConverterRegistryMock();
        $converterRegMock->expects($this->once())
            ->method('getConverter')
            ->with('ezstring')
            ->will(
                $this->returnValue(
                    ($converterMock = $this->createMock(Converter::class))
                )
            );

        $updater = $this->getContentUpdater();

        $actions = $updater->determineActions($fromType, $toType);

        $this->assertEquals(
            array(
                new ContentUpdater\Action\RemoveField(
                    $this->getContentGatewayMock(),
                    $fromType->fieldDefinitions[0],
                    $this->getContentStorageHandlerMock(),
                    $this->getContentMapperMock()
                ),
                new ContentUpdater\Action\AddField(
                    $this->getContentGatewayMock(),
                    $toType->fieldDefinitions[2],
                    $converterMock,
                    $this->getContentStorageHandlerMock(),
                    $this->getContentMapperMock()
                ),
            ),
            $actions
        );
    }

    public function testApplyUpdates()
    {
        $updater = $this->getContentUpdater();

        $actionA = $this->getMockForAbstractClass(
            Action::class,
            array(),
            '',
            false
        );
        $actionA->expects($this->at(0))
            ->method('apply')
            ->with(11);
        $actionA->expects($this->at(1))
            ->method('apply')
            ->with(22);
        $actionB = $this->getMockForAbstractClass(
            Action::class,
            array(),
            '',
            false
        );
        $actionB->expects($this->at(0))
            ->method('apply')
            ->with(11);
        $actionB->expects($this->at(1))
            ->method('apply')
            ->with(22);

        $actions = array($actionA, $actionB);

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('getContentIdsByContentTypeId')
            ->with(23)
            ->will(
                $this->returnValue(array(11, 22))
            );

        $updater->applyUpdates(23, $actions);
    }

    /**
     * Returns a fixture for the from Type.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    protected function getFromTypeFixture()
    {
        $type = new Type();

        $fieldA = new Type\FieldDefinition();
        $fieldA->id = 1;
        $fieldA->fieldType = 'ezstring';

        $fieldB = new Type\FieldDefinition();
        $fieldB->id = 2;
        $fieldB->fieldType = 'ezstring';

        $type->fieldDefinitions = array(
            $fieldA, $fieldB,
        );

        return $type;
    }

    /**
     * Returns a fixture for the to Type.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    protected function getToTypeFixture()
    {
        $type = clone $this->getFromTypeFixture();

        unset($type->fieldDefinitions[0]);

        $fieldC = new Type\FieldDefinition();
        $fieldC->id = 3;
        $fieldC->fieldType = 'ezstring';

        $type->fieldDefinitions[] = $fieldC;

        return $type;
    }

    /**
     * Returns a Content Gateway mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if ($this->contentGatewayMock === null) {
            $this->contentGatewayMock = $this->createMock(Gateway::class);
        }

        return $this->contentGatewayMock;
    }

    /**
     * Returns a FieldValue Converter registry mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistryInterface
     */
    protected function getConverterRegistryMock()
    {
        if ($this->converterRegistryMock === null) {
            $this->converterRegistryMock = $this->createMock(ConverterRegistryInterface::class);
        }

        return $this->converterRegistryMock;
    }

    /**
     * Returns a Content StorageHandler mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected function getContentStorageHandlerMock()
    {
        if (!isset($this->contentStorageHandlerMock)) {
            $this->contentStorageHandlerMock = $this->createMock(StorageHandler::class);
        }

        return $this->contentStorageHandlerMock;
    }

    /**
     * Returns a Content mapper mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapperMock()
    {
        if (!isset($this->contentMapperMock)) {
            $this->contentMapperMock = $this->createMock(Mapper::class);
        }

        return $this->contentMapperMock;
    }

    /**
     * Returns the content updater to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater
     */
    protected function getContentUpdater()
    {
        if (!isset($this->contentUpdater)) {
            $this->contentUpdater = new ContentUpdater(
                $this->getContentGatewayMock(),
                $this->getConverterRegistryMock(),
                $this->getContentStorageHandlerMock(),
                $this->getContentMapperMock()
            );
        }

        return $this->contentUpdater;
    }
}
