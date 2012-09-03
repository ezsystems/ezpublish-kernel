<?php
/**
 * File containing a ContentTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\API\Repository\Values;

class ContentTest extends BaseTest
{
    /**
     * @var eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo
     */
    protected $versionInfoParserMock;

    /**
     * Tests the section parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testParse()
    {
        $relationParser = $this->getParser();

        $inputArray = array (
            '_media-type' => 'application/vnd.ez.api.Version+json',
            '_href' => '/content/objects/10/versions/2',
            'VersionInfo' => array(),
            'Fields' => array(
                'Field' => array(
                    0 => array(
                        'id' => 19,
                        'fieldDefinitionIdentifier' => 'first_name',
                        'languageCode' => 'eng-US',
                        'fieldValue' => 'Anonymous',
                    ),
                    1 => array(
                        'id' => 20,
                        'fieldDefinitionIdentifier' => 'last_name',
                        'languageCode' => 'eng-US',
                        'fieldValue' => 'User',
                    ),
                ),
            ),
            'Relations' => array(
                'Relation' => array(
                    0 => array(
                        '_media-type' => 'application/vnd.ez.api.Relation+xml'
                    ),
                    1 => array(
                        '_media-type' => 'application/vnd.ez.api.Relation+xml'
                    ),
                )
            ),
        );

        $this->getVersionInfoParserMock()->expects( $this->once() )
            ->method( 'parse' )
            ->with(
                $this->equalTo( array() ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher' )
            )->will( $this->returnValue( 'VersionInfoMock' ) );

        $this->getParsingDispatcherMock()->expects( $this->exactly( 2 ) )
            ->method( 'parse' )
            ->will( $this->returnValue( 'RelationMock' ) );

        $result = $relationParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * @depends testParse
     */
    public function testParsedVersionInfo( $parsedContent )
    {
        $this->assertEquals(
            // Mocked result
            'VersionInfoMock',
            $parsedContent->versionInfo
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedRelations( $parsedContent )
    {
        $this->assertEquals(
            // Mocked result
            array( 'RelationMock', 'RelationMock' ),
            $parsedContent->relations
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedFirstNameField( $parsedContent )
    {
        $this->assertEquals(
            // Mocked result
            new Values\Content\Field( array(
                'id' => 19,
                'fieldDefIdentifier' => 'first_name',
                'languageCode' => 'eng-US',
                'value' => 'Anonymous'
            ) ),
            $parsedContent->getField( 'first_name', 'eng-US' )
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedLastNameField( $parsedContent )
    {
        $this->assertEquals(
            // Mocked result
            new Values\Content\Field( array(
                'id' => 20,
                'fieldDefIdentifier' => 'last_name',
                'languageCode' => 'eng-US',
                'value' => 'User'
            ) ),
            $parsedContent->getField( 'last_name', 'eng-US' )
        );
    }

    /**
     * Gets the section parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Content
     */
    protected function getParser()
    {
        return new Parser\Content( $this->getVersionInfoParserMock() );
    }

    /**
     * @return eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo
     */
    protected function getVersionInfoParserMock()
    {
        if ( !isset( $this->contentServiceMock ) )
        {
            $this->contentServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\REST\\Client\\Input\\Parser\\VersionInfo',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentServiceMock;
    }
}
