<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentUpdater\Action\RemoveFieldTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentUpdater\Action;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField;
use eZ\Publish\SPI\Persistence\Content;

/**
 * Test case for Content Type Updater.
 */
class RemoveFieldTest extends \PHPUnit_Framework_TestCase
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
        $content = $this->getContentFixture();

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'deleteField' )
            ->with(
                $this->equalTo( 3 ),
                $this->equalTo( 13 )
            );

        $this->getContentStorageHandlerMock()->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with(
                $this->equalTo( 'ezstring' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->equalTo( array( 3 ) )
            );

        $action->apply( $content );
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
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
                $this->getContentStorageHandlerMock()
            );
        }
        return $this->removeFieldAction;
    }
}
