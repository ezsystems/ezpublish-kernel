<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\NameSchemaBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\Core\Repository\NameSchemaService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;

/**
 * Test case for NameSchema service
 */
abstract class NameSchemaBase extends BaseServiceTest
{
    /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService::resolve
     * @dataProvider providerForTestResolve
     */
    public function testResolve( $nameSchema, $expectedName )
    {
        /** @var $service \eZ\Publish\Core\Repository\NameSchemaService */
        $service = $this->repository->getNameSchemaService();

        list( $content, $contentType ) = $this->buildTestObjects();

        $name = $service->resolve(
            $nameSchema,
            $contentType,
            $content->fields,
            $content->versionInfo->languageCodes
        );

        self::assertEquals( $expectedName, $name );
    }

    /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService::resolve
     */
    public function testResolveWithSettings()
    {
        /** @var $service \eZ\Publish\Core\Repository\NameSchemaService */
        $service = $this->repository->getNameSchemaService();

        $this->setConfiguration(
            $service,
            array(
                "limit" => 38,
                "sequence" => "..."
            )
        );

        list( $content, $contentType ) = $this->buildTestObjects();

        $name = $service->resolve(
            "Hello, <text1> and <text2> and then goodbye and hello again",
            $contentType,
            $content->fields,
            $content->versionInfo->languageCodes
        );

        self::assertEquals(
            array(
                "eng-GB" => "Hello, one and two and then goodbye...",
                "cro-HR" => "Hello, jedan and dva and then goodb..."
            ),
            $name
        );
    }

    /**
     */
    public function providerForTestResolve()
    {
        return array(
            array(
                "<text1>",
                array(
                    "eng-GB" => "one",
                    "cro-HR" => "jedan",
                )
            ),
            array(
                "<text1> <text2>",
                array(
                    "eng-GB" => "one two",
                    "cro-HR" => "jedan dva",
                )
            ),
            array(
                "Hello <text1>",
                array(
                    "eng-GB" => "Hello one",
                    "cro-HR" => "Hello jedan",
                )
            ),
            array(
                "Hello, <text1> and <text2> and then goodbye",
                array(
                    "eng-GB" => "Hello, one and two and then goodbye",
                    "cro-HR" => "Hello, jedan and dva and then goodbye",
                )
            ),
            array(
                "<text1|text2>",
                array(
                    "eng-GB" => "one",
                    "cro-HR" => "jedan",
                )
            ),
            array(
                "<text2|text1>",
                array(
                    "eng-GB" => "two",
                    "cro-HR" => "dva",
                )
            ),
            array(
                "<text3|text1>",
                array(
                    "eng-GB" => "one",
                    "cro-HR" => "jedan",
                )
            ),
            array(
                "<(<text1> <text2>)>",
                array(
                    "eng-GB" => "one two",
                    "cro-HR" => "jedan dva",
                )
            ),
            array(
                "<(<text3|text2>)>",
                array(
                    "eng-GB" => "two",
                    "cro-HR" => "dva",
                )
            ),
            array(
                "<text3|(<text3|text2>)>",
                array(
                    "eng-GB" => "two",
                    "cro-HR" => "dva",
                )
            ),
            array(
                "<text3|(Hello <text2> and <text1>!)>",
                array(
                    "eng-GB" => "Hello two and one!",
                    "cro-HR" => "Hello dva and jedan!",
                )
            ),
            array(
                "<text3|(Hello <text3> and <text1>)|text2>!",
                array(
                    "eng-GB" => "Hello  and one!",
                    "cro-HR" => "Hello  and jedan!",
                )
            ),
            array(
                "<text3|(Hello <text3|text2> and <text1>)|text2>!",
                array(
                    "eng-GB" => "Hello two and one!",
                    "cro-HR" => "Hello dva and jedan!",
                )
            ),
        );
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
        $contentType = new ContentType(
            array(
                "nameSchema" => $nameSchema,
                "urlAliasSchema" => $urlAliasSchema,
                "fieldDefinitions" => $this->getFieldDefinitions()
            )
        );
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

        return array( $content, $contentType );
    }

    /**
     * @param object $service
     * @param array $configuration
     */
    protected function setConfiguration( $service, array $configuration )
    {
        $refObject = new \ReflectionObject( $service );
        $refProperty = $refObject->getProperty( 'settings' );
        $refProperty->setAccessible( true );
        $refProperty->setValue(
            $service,
            $configuration
        );
    }
}
