<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\NameSchemaTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\NameSchemaService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;

/**
 * Mock Test case for NameSchema service
 */
class NameSchemaTest extends BaseServiceMockTest
{
    /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService::resolveUrlAliasSchema
     */
    public function testResolveUrlAliasSchema()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService( array( "resolve" ) );

        list( $content, $contentType ) = $this->buildTestObjects();

        $serviceMock->expects(
            $this->once()
        )->method(
            "resolve"
        )->with(
            "<urlalias_schema>",
            $this->equalTo( $contentType ),
            $this->equalTo( $content->fields ),
            $this->equalTo( $content->versionInfo->languageCodes )
        )->will(
            $this->returnValue( 42 )
        );

        $result = $serviceMock->resolveUrlAliasSchema( $content, $contentType );

        self::assertEquals( 42, $result );
    }

    /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService::resolveUrlAliasSchema
     */
    public function testResolveUrlAliasSchemaFallbackToNameSchema()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService( array( "resolve" ) );

        list( $content, $contentType ) = $this->buildTestObjects( "<name_schema>", "" );

        $serviceMock->expects(
            $this->once()
        )->method(
            "resolve"
        )->with(
            "<name_schema>",
            $this->equalTo( $contentType ),
            $this->equalTo( $content->fields ),
            $this->equalTo( $content->versionInfo->languageCodes )
        )->will(
            $this->returnValue( 42 )
        );

        $result = $serviceMock->resolveUrlAliasSchema( $content, $contentType );

        self::assertEquals( 42, $result );
    }

    /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService::resolveNameSchema
     */
    public function testResolveNameSchema()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService( array( "resolve" ) );

        list( $content, $contentType ) = $this->buildTestObjects();

        $serviceMock->expects(
            $this->once()
        )->method(
            "resolve"
        )->with(
            "<name_schema>",
            $this->equalTo( $contentType ),
            $this->equalTo( $content->fields ),
            $this->equalTo( $content->versionInfo->languageCodes )
        )->will(
            $this->returnValue( 42 )
        );

        $result = $serviceMock->resolveNameSchema( $content, array(), array(), $contentType );

        self::assertEquals( 42, $result );
    }

    /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService::resolveNameSchema
     */
    public function testResolveNameSchemaWithFields()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService( array( "resolve" ) );

        list( $content, $contentType ) = $this->buildTestObjects();

        $fields = array();
        $fields["text3"]["cro-HR"] = new TextLineValue( "tri" );
        $fields["text1"]["ger-DE"] = new TextLineValue( "ein" );
        $fields["text2"]["ger-DE"] = new TextLineValue( "zwei" );
        $fields["text3"]["ger-DE"] = new TextLineValue( "drei" );
        $mergedFields = $fields;
        $mergedFields["text1"]["cro-HR"] = new TextLineValue( "jedan" );
        $mergedFields["text2"]["cro-HR"] = new TextLineValue( "dva" );
        $mergedFields["text1"]["eng-GB"] = new TextLineValue( "one" );
        $mergedFields["text2"]["eng-GB"] = new TextLineValue( "two" );
        $mergedFields["text3"]["eng-GB"] = new TextLineValue( "" );
        $languages = array( "eng-GB", "cro-HR", "ger-DE" );

        $serviceMock->expects(
            $this->once()
        )->method(
            "resolve"
        )->with(
            "<name_schema>",
            $this->equalTo( $contentType ),
            $this->equalTo( $mergedFields ),
            $this->equalTo( $languages )
        )->will(
            $this->returnValue( 42 )
        );

        $result = $serviceMock->resolveNameSchema( $content, $fields, $languages, $contentType );

        self::assertEquals( 42, $result );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    protected function getFields()
    {
        return array(
            new Field(
                array(
                    "languageCode" => "eng-GB",
                    "fieldDefIdentifier" => "text1",
                    "value" => new TextLineValue( "one" )
                )
            ),
            new Field(
                array(
                    "languageCode" => "eng-GB",
                    "fieldDefIdentifier" => "text2",
                    "value" => new TextLineValue( "two" )
                )
            ),
            new Field(
                array(
                    "languageCode" => "eng-GB",
                    "fieldDefIdentifier" => "text3",
                    "value" => new TextLineValue( "" )
                )
            ),
            new Field(
                array(
                    "languageCode" => "cro-HR",
                    "fieldDefIdentifier" => "text1",
                    "value" => new TextLineValue( "jedan" )
                )
            ),
            new Field(
                array(
                    "languageCode" => "cro-HR",
                    "fieldDefIdentifier" => "text2",
                    "value" => new TextLineValue( "dva" )
                )
            ),
            new Field(
                array(
                    "languageCode" => "cro-HR",
                    "fieldDefIdentifier" => "text3",
                    "value" => new TextLineValue( "" )
                )
            ),
        );
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition[]
     */
    protected function getFieldDefinitions()
    {
        return array(
            new FieldDefinition(
                array(
                    "id" => "1",
                    "identifier" => "text1",
                    "fieldTypeIdentifier" => "ezstring"
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "2",
                    "identifier" => "text2",
                    "fieldTypeIdentifier" => "ezstring"
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "3",
                    "identifier" => "text3",
                    "fieldTypeIdentifier" => "ezstring"
                )
            ),
        );
    }

    /**
     * Builds stubbed content for testing purpose.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function buildTestObjects( $nameSchema = "<name_schema>", $urlAliasSchema = "<urlalias_schema>" )
    {
        $content = new Content(
            array(
                "internalFields" => $this->getFields(),
                "versionInfo" => new VersionInfo(
                    array(
                        "languageCodes" => array( "eng-GB", "cro-HR" )
                    )
                )
            )
        );
        $contentType = new ContentType(
            array(
                "nameSchema" => $nameSchema,
                "urlAliasSchema" => $urlAliasSchema,
                "fieldDefinitions" => $this->getFieldDefinitions()
            )
        );

        return array( $content, $contentType );
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\NameSchemaService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedNameSchemaService( array $methods = null )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\NameSchemaService",
            $methods,
            array(
                $this->getRepositoryMock()
            )
        );
    }
}
