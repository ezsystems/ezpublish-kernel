<?php
/**
 * File containing the FieldTypeParserTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Input;

use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\Core\REST\Client\Values\Content\ContentInfo;
use eZ\Publish\Core\REST\Client\Values\ContentType\FieldDefinition;

/**
 * FieldTypeParser test class
 */
class FieldTypeParserTest extends \PHPUnit_Framework_TestCase
{
    protected $contentServiceMock;

    protected $contentTypeServiceMock;

    protected $fieldTypeServiceMock;

    protected $contentTypeMock;

    protected $fieldTypeMock;

    public function setUp()
    {
        $this->contentServiceMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\ContentService',
            array(),
            array(),
            '',
            false
        );
        $this->contentTypeServiceMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\ContentTypeService',
            array(),
            array(),
            '',
            false
        );
        $this->fieldTypeServiceMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\FieldTypeService',
            array(),
            array(),
            '',
            false
        );
        $this->contentTypeMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\Values\\ContentType\\ContentType',
            array(),
            array(),
            '',
            false
        );
        $this->fieldTypeMock = $this->getMock(
            'eZ\\Publish\\SPI\\FieldType\\FieldType',
            array(),
            array(),
            '',
            false
        );
    }

    public function testParseFieldValue()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->contentServiceMock->expects( $this->once() )
            ->method( 'loadContentInfo' )
            ->with( '/content/23' )
            ->will( $this->returnValue(
                new ContentInfo(
                    $this->contentTypeServiceMock,
                    array(
                    'contentTypeId' => '/content/types/42'
                    )
                )
            ) );

        $contentTypeMock = $this->contentTypeMock;
        $this->contentTypeServiceMock->expects( $this->once() )
            ->method( 'loadContentType' )
            ->with( '/content/types/42' )
            ->will( $this->returnCallback(
                // Avoid PHPUnit cloning
                function () use ( $contentTypeMock )
                {
                    return $contentTypeMock;
                }
            ) );

        $contentTypeMock->expects( $this->once() )
            ->method( 'getFieldDefinition' )
            ->with( $this->equalTo( 'my-field-definition' ) )
            ->will( $this->returnValue(
                new FieldDefinition( array(
                    'fieldTypeIdentifier' => 'some-fancy-field-type'
                ) )
            ) );

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects( $this->once() )
            ->method( 'getFieldType' )
            ->with( $this->equalTo( 'some-fancy-field-type' ) )
            ->will( $this->returnCallback(
                // Avoid PHPUnit cloning
                function () use ( $fieldTypeMock )
                {
                    return $fieldTypeMock;
                }
            ) );

        $fieldTypeMock->expects( $this->once() )
            ->method( 'fromHash' )
            ->with( $this->equalTo( array( 1, 2, 3 ) ) )
            ->will( $this->returnValue( array( 'foo', 'bar' ) ) );

        $this->assertEquals(
            array( 'foo', 'bar' ),
            $fieldTypeParser->parseFieldValue(
                '/content/23',
                'my-field-definition',
                array( 1, 2, 3 )
            )
        );
    }

    public function testParseValue()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects( $this->once() )
            ->method( 'getFieldType' )
            ->with( $this->equalTo( 'some-fancy-field-type' ) )
            ->will( $this->returnCallback(
                // Avoid PHPUnit cloning
                function () use ( $fieldTypeMock )
                {
                    return $fieldTypeMock;
                }
            ) );

        $fieldTypeMock->expects( $this->once() )
            ->method( 'fromHash' )
            ->with( $this->equalTo( array( 1, 2, 3 ) ) )
            ->will( $this->returnValue( array( 'foo', 'bar' ) ) );

        $this->assertEquals(
            array( 'foo', 'bar' ),
            $fieldTypeParser->parseValue(
                'some-fancy-field-type',
                array( 1, 2, 3 )
            )
        );
    }

    public function testParseFieldSettings()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects( $this->once() )
            ->method( 'getFieldType' )
            ->with( $this->equalTo( 'some-fancy-field-type' ) )
            ->will( $this->returnCallback(
                // Avoid PHPUnit cloning
                function () use ( $fieldTypeMock )
                {
                    return $fieldTypeMock;
                }
            ) );

        $fieldTypeMock->expects( $this->once() )
            ->method( 'fieldSettingsFromHash' )
            ->with( $this->equalTo( array( 1, 2, 3 ) ) )
            ->will( $this->returnValue( array( 'foo', 'bar' ) ) );

        $this->assertEquals(
            array( 'foo', 'bar' ),
            $fieldTypeParser->parseFieldSettings(
                'some-fancy-field-type',
                array( 1, 2, 3 )
            )
        );
    }

    public function testParseValidatorConfiguration()
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects( $this->once() )
            ->method( 'getFieldType' )
            ->with( $this->equalTo( 'some-fancy-field-type' ) )
            ->will( $this->returnCallback(
                // Avoid PHPUnit cloning
                function () use ( $fieldTypeMock )
                {
                    return $fieldTypeMock;
                }
            ) );

        $fieldTypeMock->expects( $this->once() )
            ->method( 'validatorConfigurationFromHash' )
            ->with( $this->equalTo( array( 1, 2, 3 ) ) )
            ->will( $this->returnValue( array( 'foo', 'bar' ) ) );

        $this->assertEquals(
            array( 'foo', 'bar' ),
            $fieldTypeParser->parseValidatorConfiguration(
                'some-fancy-field-type',
                array( 1, 2, 3 )
            )
        );
    }

    protected function getFieldTypeParser()
    {
        return new FieldTypeParser(
            $this->contentServiceMock,
            $this->contentTypeServiceMock,
            $this->fieldTypeServiceMock
        );
    }
}
