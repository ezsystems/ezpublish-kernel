<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentUpdater\Action\RemoveFieldTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentUpdater\Action;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField;
use eZ\Publish\SPI\Persistence\Content;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Content Type Updater.
 */
class RemoveFieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * Content gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * Content gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $contentStorageHandlerMock;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapperMock;

    /**
     * RemoveField action to test
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField
     */
    protected $removeFieldAction;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater::__construct
     *
     * @return void
     */
    public function testCtor()
    {
        $action = $this->getRemoveFieldAction();

        $this->assertAttributeSame(
            $this->getContentGatewayMock(),
            'contentGateway',
            $action
        );
        $this->assertAttributeEquals(
            $this->getFieldDefinitionFixture(),
            'fieldDefinition',
            $action
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField::apply
     *
     * @return void
     */
    public function testApply()
    {
        $action = $this->getRemoveFieldAction();
        $contentInfo = $this->getContentInfoFixture();
        $content = $this->getContentFixture();

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'load' )
            ->with( $contentInfo->id, $contentInfo->currentVersionNo )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content ) ) );

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'deleteField' )
            ->with( $this->equalTo( 3 ) );

        $this->getContentStorageHandlerMock()->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with(
                $this->equalTo( 'ezstring' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->equalTo( array( 3 ) )
            );

        $action->apply( $contentInfo );
    }

    /**
     * Returns a ContentInfo  fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    protected function getContentInfoFixture()
    {
        $contentInfo = new Content\ContentInfo();
        $contentInfo->id = "contentId";
        $contentInfo->currentVersionNo = "versionNo";

        return $contentInfo;
    }

    /**
     * Returns a Content fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture()
    {
        $fieldNoRemove = new Content\Field();
        $fieldNoRemove->id = 2;
        $fieldNoRemove->versionNo = 13;
        $fieldNoRemove->fieldDefinitionId = 23;
        $fieldNoRemove->type = 'ezstring';

        $fieldRemove = new Content\Field();
        $fieldRemove->id = 3;
        $fieldRemove->versionNo = 13;
        $fieldRemove->fieldDefinitionId = 42;
        $fieldRemove->type = 'ezstring';

        $content = new Content();
        $content->versionInfo = new Content\VersionInfo();
        $content->fields = array(
            $fieldNoRemove,
            $fieldRemove
        );
        $content->versionInfo->versionNo = 3;
        return $content;
    }

    /**
     * Returns a Content Gateway mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if ( !isset( $this->contentGatewayMock ) )
        {
            $this->contentGatewayMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Gateway'
            );
        }
        return $this->contentGatewayMock;
    }

    /**
     * Returns a Content StorageHandler mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected function getContentStorageHandlerMock()
    {
        if ( !isset( $this->contentStorageHandlerMock ) )
        {
            $this->contentStorageHandlerMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageHandler',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentStorageHandlerMock;
    }

    /**
     * Returns a Content mapper mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapperMock()
    {
        if ( !isset( $this->contentMapperMock ) )
        {
            $this->contentMapperMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Mapper',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentMapperMock;
    }

    /**
     * Returns a FieldDefinition fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function getFieldDefinitionFixture()
    {
        $fieldDef = new Content\Type\FieldDefinition();
        $fieldDef->id = 42;
        $fieldDef->fieldType = 'ezstring';
        $fieldDef->defaultValue = new Content\FieldValue();
        return $fieldDef;
    }

    /**
     * Returns the RemoveField action to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField
     */
    protected function getRemoveFieldAction()
    {
        if ( !isset( $this->removeFieldAction ) )
        {
            $this->removeFieldAction = new RemoveField(
                $this->getContentGatewayMock(),
                $this->getFieldDefinitionFixture(),
                $this->getContentStorageHandlerMock(),
                $this->getContentMapperMock()
            );
        }
        return $this->removeFieldAction;
    }
}
