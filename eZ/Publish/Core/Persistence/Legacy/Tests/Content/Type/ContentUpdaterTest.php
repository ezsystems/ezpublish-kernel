<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentTypeUpdaterTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId as CriterionContentTypeId;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Test case for Content Type Updater.
 */
class ContentUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Content gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * FieldValue converter registry mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $converterRegistryMock;

    /**
     * Search handler mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler
     */
    protected $searchHandlerMock;

    /**
     * Content StorageHandler mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $contentStorageHandlerMock;

    /**
     * Content Updater to test
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater
     */
    protected $contentUpdater;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater::__construct
     *
     * @return void
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
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater::determineActions
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater::hasFieldDefinition
     */
    public function testDetermineActions()
    {
        $fromType = $this->getFromTypeFixture();
        $toType = $this->getToTypeFixture();

        $converterRegMock = $this->getConverterRegistryMock();
        $converterRegMock->expects( $this->once() )
            ->method( 'getConverter' )
            ->with( 'ezstring' )
            ->will(
                $this->returnValue(
                    ( $converterMock = $this->getMock(
                        '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
                    ) )
                )
            );

        $updater = $this->getContentUpdater();

        $actions = $updater->determineActions(
            $fromType, $toType
        );

        $this->assertEquals(
            array(
                new ContentUpdater\Action\RemoveField(
                    $this->getContentGatewayMock(),
                    $fromType->fieldDefinitions[0],
                    $this->getContentStorageHandlerMock()
                ),
                new ContentUpdater\Action\AddField(
                    $this->getContentGatewayMock(),
                    $toType->fieldDefinitions[2],
                    $converterMock,
                    $this->getContentStorageHandlerMock()
                )
            ),
            $actions
        );
    }

    public function testApplyUpdates()
    {
        $updater = $this->getContentUpdater();

        $actionA = $this->getMockForAbstractClass(
            '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\ContentUpdater\\Action',
            array(),
            '',
            false
        );
        $actionA->expects( $this->exactly( 2 ) )
            ->method( 'apply' )
            ->with(
                $this->isInstanceOf(
                    '\\eZ\\Publish\\SPI\\Persistence\\Content'
                )
            );
        $actionB = $this->getMockForAbstractClass(
            '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\ContentUpdater\\Action',
            array(),
            '',
            false
        );
        $actionB->expects( $this->exactly( 2 ) )
            ->method( 'apply' )
            ->with(
                $this->isInstanceOf(
                    '\\eZ\\Publish\\SPI\\Persistence\\Content'
                )
            );

        $actions = array( $actionA, $actionB );

        $content = new Content();

        $result = new SearchResult();

        $hit    = new SearchHit();
        $hit->valueObject = $content;
        $result->searchHits[] = $hit;

        $hit    = new SearchHit();
        $hit->valueObject = clone $content;
        $result->searchHits[] = $hit;

        $this->getSearchHandlerMock()
            ->expects( $this->once() )
            ->method( 'findContent' )
            ->with(
                $this->equalTo(
                    new Query(
                        array(
                            'criterion' => new CriterionContentTypeId( 23 )
                        )
                    )
                )
            )->will(
                $this->returnValue( $result )
            );

        $updater->applyUpdates( 23, $actions );
    }

    /**
     * Returns a fixture for the from Type
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
            $fieldA, $fieldB
        );

        return $type;
    }

    /**
     * Returns a fixture for the to Type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    protected function getToTypeFixture()
    {
        $type = clone $this->getFromTypeFixture();

        unset( $type->fieldDefinitions[0] );

        $fieldC = new Type\FieldDefinition();
        $fieldC->id = 3;
        $fieldC->fieldType = 'ezstring';

        $type->fieldDefinitions[] = $fieldC;

        return $type;
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
     * Returns a FieldValue Converter registry mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected function getConverterRegistryMock()
    {
        if ( !isset( $this->converterRegistryMock ) )
        {
            $this->converterRegistryMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\ConverterRegistry',
                array(),
                array( array() )
            );
        }
        return $this->converterRegistryMock;
    }

    /**
     * Returns a Search Handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler
     */
    protected function getSearchHandlerMock()
    {
        if ( !isset( $this->searchHandlerMock ) )
        {
            $this->searchHandlerMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Search\\Handler',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->searchHandlerMock;
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
     * Returns the content updater to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater
     */
    protected function getContentUpdater()
    {
        if ( !isset( $this->contentUpdater ) )
        {
            $this->contentUpdater = new ContentUpdater(
                $this->getSearchHandlerMock(),
                $this->getContentGatewayMock(),
                $this->getConverterRegistryMock(),
                $this->getContentStorageHandlerMock()
            );
        }
        return $this->contentUpdater;
    }
}
