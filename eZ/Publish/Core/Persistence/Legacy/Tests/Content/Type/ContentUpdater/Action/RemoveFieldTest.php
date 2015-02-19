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
    public function testApplySingleVersionSingleTranslation()
    {
        $contentId = 42;
        $versionNumbers = array( 1 );
        $action = $this->getRemoveFieldAction();
        $content = $this->getContentFixture( 1, array( "cro-HR" ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 1 ) )
            ->method( 'load' )
            ->with( $contentId, 1 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content ) ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'deleteField' )
            ->with( $this->equalTo( "3-cro-HR" ) );

        $this->getContentStorageHandlerMock()->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with(
                $this->equalTo( 'ezstring' ),
                $content->versionInfo,
                $this->equalTo( array( "3-cro-HR" ) )
            );

        $action->apply( $contentId );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField::apply
     */
    public function testApplyMultipleVersionsSingleTranslation()
    {
        $contentId = 42;
        $versionNumbers = array( 1, 2 );
        $action = $this->getRemoveFieldAction();
        $content1 = $this->getContentFixture( 1, array( "cro-HR" ) );
        $content2 = $this->getContentFixture( 2, array( "cro-HR" ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 1 ) )
            ->method( 'load' )
            ->with( $contentId, 1 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->at( 0 ) )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content1 ) ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 2 ) )
            ->method( 'load' )
            ->with( $contentId, 2 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->at( 1 ) )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content2 ) ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'deleteField' )
            ->with( $this->equalTo( "3-cro-HR" ) );

        $this->getContentStorageHandlerMock()
            ->expects( $this->at( 0 ) )
            ->method( 'deleteFieldData' )
            ->with(
                $this->equalTo( 'ezstring' ),
                $content1->versionInfo,
                $this->equalTo( array( "3-cro-HR" ) )
            );

        $this->getContentStorageHandlerMock()
            ->expects( $this->at( 1 ) )
            ->method( 'deleteFieldData' )
            ->with(
                $this->equalTo( 'ezstring' ),
                $content2->versionInfo,
                $this->equalTo( array( "3-cro-HR" ) )
            );

        $action->apply( $contentId );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action\RemoveField::apply
     */
    public function testApplyMultipleVersionsMultipleTranslations()
    {
        $contentId = 42;
        $versionNumbers = array( 1, 2 );
        $action = $this->getRemoveFieldAction();
        $content1 = $this->getContentFixture( 1, array( "cro-HR", "hun-HU" ) );
        $content2 = $this->getContentFixture( 2, array( "cro-HR", "hun-HU" ) );

        $this->getContentGatewayMock()
            ->expects( $this->once() )
            ->method( 'listVersionNumbers' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $versionNumbers ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 1 ) )
            ->method( 'load' )
            ->with( $contentId, 1 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->at( 0 ) )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content1 ) ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 2 ) )
            ->method( 'load' )
            ->with( $contentId, 2 )
            ->will( $this->returnValue( array() ) );

        $this->getContentMapperMock()
            ->expects( $this->at( 1 ) )
            ->method( 'extractContentFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( $content2 ) ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 3 ) )
            ->method( 'deleteField' )
            ->with( $this->equalTo( "3-cro-HR" ) );

        $this->getContentGatewayMock()
            ->expects( $this->at( 4 ) )
            ->method( 'deleteField' )
            ->with( $this->equalTo( "3-hun-HU" ) );

        $this->getContentStorageHandlerMock()
            ->expects( $this->at( 0 ) )
            ->method( 'deleteFieldData' )
            ->with(
                $this->equalTo( 'ezstring' ),
                $content1->versionInfo,
                $this->equalTo( array( "3-cro-HR", "3-hun-HU" ) )
            );

        $this->getContentStorageHandlerMock()
            ->expects( $this->at( 1 ) )
            ->method( 'deleteFieldData' )
            ->with(
                $this->equalTo( 'ezstring' ),
                $content2->versionInfo,
                $this->equalTo( array( "3-cro-HR", "3-hun-HU" ) )
            );

        $action->apply( $contentId );
    }

    /**
     * Returns a Content fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture( $versionNo, $languageCodes )
    {
        $fields = array();

        foreach ( $languageCodes as $index => $languageCode )
        {
            $fieldNoRemove = new Content\Field();
            $fieldNoRemove->id = "2-{$languageCode}";
            $fieldNoRemove->versionNo = $versionNo;
            $fieldNoRemove->fieldDefinitionId = 23;
            $fieldNoRemove->type = 'ezstring';
            $fieldNoRemove->languageCode = $languageCode;

            $fields[] = $fieldNoRemove;

            $fieldRemove = new Content\Field();
            $fieldRemove->id = "3-{$languageCode}";
            $fieldRemove->versionNo = $versionNo;
            $fieldRemove->fieldDefinitionId = 42;
            $fieldRemove->type = 'ezstring';
            $fieldRemove->languageCode = $languageCode;

            $fields[] = $fieldRemove;
        }

        $content = new Content();
        $content->versionInfo = new Content\VersionInfo();
        $content->fields = $fields;
        $content->versionInfo->versionNo = $versionNo;

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
