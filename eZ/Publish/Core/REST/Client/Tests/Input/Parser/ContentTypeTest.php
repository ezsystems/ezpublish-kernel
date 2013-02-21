<?php
/**
 * File containing a ContentTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\Values;

class ContentTypeTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo
     */
    protected $versionInfoParserMock;

    /**
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    protected $contentTypeServiceMock;

    /**
     * Tests the section parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testParse()
    {
        $contentTypeParser = $this->getParser();

        $inputArray = array(
            '_media-type' => 'application/vnd.ez.api.ContentType+json',
            '_href' => '/content/types/1',
            'id' => 1,
            'status' => 'DEFINED',
            'identifier' => 'folder',
            'names' => array(
                'value' => array(
                    1 => array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Folder',
                    ),
                ),
            ),
            'descriptions' => array(
                'value' => array(
                    1 => array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Bielefeld',
                    ),
                ),
            ),
            'creationDate' => '2002-06-18T11:21:38+02:00',
            'modificationDate' => '2004-04-20T11:54:35+02:00',
            'Creator' => array(
                '_media-type' => 'application/vnd.ez.api.User+json',
                '_href' => '/user/users/10',
            ),
            'Modifier' => array(
                '_media-type' => 'application/vnd.ez.api.User+json',
                '_href' => '/user/users/14',
            ),
            'remoteId' => 'a3d405b81be900468eb153d774f4f0d2',
            'urlAliasSchema' => '',
            'nameSchema' => '<short_name|name>',
            'isContainer' => 'true',
            'mainLanguageCode' => 'eng-US',
            'defaultAlwaysAvailable' => 'true',
            'defaultSortField' => 'PATH',
            'defaultSortOrder' => 'ASC',
            // Only "mock"
            'FieldDefinitions' => array(
                '_media-type' => 'application/vnd.ez.api.FieldDefinitionList+json',
                '_href' => '/content/types/1/fieldDefinitions',
                'FieldDefinition' => array(),
            )
        );

        $result = $contentTypeParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * @dataProvider provideExpectedContentTypeProperties
     * @depends testParse
     */
    public function testParsedProperties( $propertyName, $expectedValue, $parsedContentType )
    {
        $this->assertEquals(
            $expectedValue,
            $parsedContentType->$propertyName,
            "Property \${$propertyName} parsed incorrectly."
        );
    }

    public function provideExpectedContentTypeProperties()
    {
        return array(
            array(
                'id',
                '/content/types/1',
            ),
            array(
                'status',
                Values\ContentType\ContentType::STATUS_DEFINED,
            ),
            array(
                'identifier',
                'folder',
            ),
            array(
                'creationDate',
                new \DateTime( '2002-06-18T11:21:38+02:00' ),
            ),
            array(
                'modificationDate',
                new \DateTime( '2004-04-20T11:54:35+02:00' ),
            ),
            array(
                'creatorId',
                '/user/users/10',
            ),
            array(
                'modifierId',
                '/user/users/14',
            ),
            array(
                'remoteId',
                'a3d405b81be900468eb153d774f4f0d2',
            ),
            array(
                'urlAliasSchema',
                '',
            ),
            array(
                'nameSchema',
                '<short_name|name>',
            ),
            array(
                'isContainer',
                true,
            ),
            array(
                'mainLanguageCode',
                'eng-US',
            ),
            array(
                'defaultAlwaysAvailable',
                true,
            ),
            array(
                'defaultSortField',
                Values\Content\Location::SORT_FIELD_PATH,
            ),
            array(
                'defaultSortOrder',
                Values\Content\Location::SORT_ORDER_ASC,
            ),
            array(
                'names',
                array( 'eng-US' => 'Folder' ),
            ),
            array(
                'descriptions',
                array( 'eng-US' => 'Bielefeld' ),
            ),
        );
    }

    /**
     * Gets the section parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\ContentType
     */
    protected function getParser()
    {
        return new Input\Parser\ContentType(
            new ParserTools(),
            $this->getContentTypeServiceMock()
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\ContentService
     */
    protected function getContentTypeServiceMock()
    {
        if ( !isset( $this->contentTypeServiceMock ) )
        {
            $this->contentTypeServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\REST\\Client\\ContentTypeService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentTypeServiceMock;
    }
}
