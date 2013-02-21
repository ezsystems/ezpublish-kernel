<?php
/**
 * File containing a VersionInfoTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\Values;

class VersionInfoTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    protected $contentServiceMock;

    /**
     * Tests the section parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function testParse()
    {
        $relationParser = $this->getParser();

        $inputArray = array(
            'id' => 474,
            'versionNo' => 2,
            'status' => 'PUBLISHED',
            'modificationDate' => '2003-12-23T12:53:25+01:00',
            'Creator' => array(
                '_media-type' => 'application/vnd.ez.api.User+json',
                '_href' => '/user/users/14',
            ),
            'creationDate' => '2003-12-23T12:52:17+01:00',
            'initialLanguageCode' => 'eng-US',
            'languageCodes' => 'eng-US,ger-DE',
            'names' => array(
                'value' => array(
                    0 => array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Anonymous User',
                    ),
                ),
            ),
            'Content' => array(
                '_media-type' => 'application/vnd.ez.api.ContentInfo+json',
                '_href' => '/content/objects/10',
            ),
        );

        $result = $relationParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * @depends testParse
     */
    public function testParsedId( $parsedVersionInfo )
    {
        $this->assertEquals(
            474,
            $parsedVersionInfo->id
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedVersionNo( $parsedVersionInfo )
    {
        $this->assertEquals(
            2,
            $parsedVersionInfo->versionNo
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedStatus( $parsedVersionInfo )
    {
        $this->assertEquals(
            Values\Content\VersionInfo::STATUS_PUBLISHED,
            $parsedVersionInfo->status
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedCreatorId( $parsedVersionInfo )
    {
        $this->assertEquals(
            '/user/users/14',
            $parsedVersionInfo->creatorId
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedCreationDate( $parsedVersionInfo )
    {
        $this->assertEquals(
            new \DateTime( '2003-12-23T12:52:17+01:00' ),
            $parsedVersionInfo->creationDate
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedModificationDate( $parsedVersionInfo )
    {
        $this->assertEquals(
            new \DateTime( '2003-12-23T12:53:25+01:00' ),
            $parsedVersionInfo->modificationDate
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedInitialLanguageCode( $parsedVersionInfo )
    {
        $this->assertEquals(
            'eng-US',
            $parsedVersionInfo->initialLanguageCode
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedLanguageCodes( $parsedVersionInfo )
    {
        $this->assertEquals(
            array( 'eng-US', 'ger-DE' ),
            $parsedVersionInfo->languageCodes
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedNames( $parsedVersionInfo )
    {
        $this->assertEquals(
            array(
                'eng-US' => 'Anonymous User',
            ),
            $parsedVersionInfo->names
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedContentInfoId( $parsedVersionInfo )
    {
        $this->assertEquals(
            '/content/objects/10',
            $parsedVersionInfo->contentInfoId
        );
    }

    /**
     * Gets the section parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo
     */
    protected function getParser()
    {
        return new Input\Parser\VersionInfo(
            new ParserTools(),
            $this->getContentServiceMock()
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\ContentService
     */
    protected function getContentServiceMock()
    {
        if ( !isset( $this->contentServiceMock ) )
        {
            $this->contentServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\REST\\Client\\ContentService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentServiceMock;
    }
}
