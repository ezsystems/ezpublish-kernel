<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Type\ContentTypeUpdaterTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Type;
use ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Type;

/**
 * Test case for Content Type Updater.
 */
class ContentTypeHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Content gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * FieldValue converter registry mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $converterRegistryMock;

    /**
     * Content Updater to test
     *
     * @var ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater
     */
    protected $contentUpdater;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater::__construct
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
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater::determineActions
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater::hasFieldDefinition
     */
    public function testDetermineActions()
    {
        $fromType = $this->getFromTypeFixture();
        $toType   = $this->getToTypeFixture();

        $converterRegMock = $this->getConverterRegistryMock();
        $converterRegMock->expects( $this->once() )
            ->method( 'getConverter' )
            ->with( 'ezstring' )
            ->will(
                $this->returnValue(
                    ( $converterMock = $this->getMock(
                        '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter'
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
                    $fromType->fieldDefinitions[0]
                ),
                new ContentUpdater\Action\AddField(
                    $this->getContentGatewayMock(),
                    $toType->fieldDefinitions[2],
                    $converterMock
                )
            ),
            $actions
        );
    }

    /**
     * Returns a fixture for the from Type
     *
     * @return \ezp\Persistence\Content\Type
     */
    protected function getFromTypeFixture()
    {
        $type = new Type();

        $fieldA            = new Type\FieldDefinition();
        $fieldA->id        = 1;
        $fieldA->fieldType = 'ezstring';

        $fieldB            = new Type\FieldDefinition();
        $fieldB->id        = 2;
        $fieldB->fieldType = 'ezstring';

        $type->fieldDefinitions = array(
            $fieldA, $fieldB
        );

        return $type;
    }

    /**
     * Returns a fixture for the to Type
     *
     * @return \ezp\Persistence\Content\Type
     */
    protected function getToTypeFixture()
    {
        $type = clone $this->getFromTypeFixture();

        unset( $type->fieldDefinitions[0] );

        $fieldC            = new Type\FieldDefinition();
        $fieldC->id        = 3;
        $fieldC->fieldType = 'ezstring';

        $type->fieldDefinitions[] = $fieldC;

        return $type;
    }

    /**
     * Returns a Content Gateway mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if ( !isset( $this->contentGatewayMock ) )
        {
            $this->contentGatewayMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Gateway'
            );
        }
        return $this->contentGatewayMock;
    }

    /**
     * Returns a FieldValue Converter registry mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected function getConverterRegistryMock()
    {
        if ( !isset( $this->converterRegistryMock ) )
        {
            $this->converterRegistryMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry'
            );
        }
        return $this->converterRegistryMock;
    }

    /**
     * Returns the content updater to test
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater
     */
    protected function getContentUpdater()
    {
        if ( !isset( $this->contentUpdater ) )
        {
            $this->contentUpdater = new ContentUpdater(
                $this->getContentGatewayMock(),
                $this->getConverterRegistryMock()
            );
        }
        return $this->contentUpdater;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
