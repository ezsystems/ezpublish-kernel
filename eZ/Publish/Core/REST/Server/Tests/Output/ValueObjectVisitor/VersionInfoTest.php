<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\Content;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class VersionInfoTest extends ValueObjectVisitorBaseTest
{
    /**
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @var \DateTime
     */
    protected $modificationDate;

    public function setUp()
    {
        $this->creationDate = new \DateTime( '2012-05-19 12:23 Europe/Berlin' );
        $this->modificationDate = new \DateTime( '2012-08-31 23:42 Europe/Berlin' );
    }

    /**
     * Test the VersionInfo visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getVersionInfoVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $section = new Content\VersionInfo(
            array(
                'id' => 23,
                'versionNo' => 5,
                'status' => Content\VersionInfo::STATUS_PUBLISHED,
                'creationDate' => $this->creationDate,
                'creatorId' => 14,
                'modificationDate' => $this->modificationDate,
                'initialLanguageCode' => 'eng-US',
                'languageCodes' => array( 'eng-US', 'ger-DE' ),
                'names' => array(
                    'eng-US' => 'Sindelfingen',
                    'eng-GB' => 'Bielefeld',
                ),
                'contentInfo' => new ContentInfo( array( 'id' => 42 ) ),
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $section
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsVersionInfoChildren( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'VersionInfo',
                'children' => array(
                    'less_than'    => 11,
                    'greater_than' => 9,
                )
            ),
            $result,
            'Invalid <VersionInfo> element.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoIdElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'id',
                'content'  => '23',
            ),
            $result,
            'Invalid <id> value.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoVersionNoElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'versionNo',
                'content'  => '5',
            ),
            $result,
            'Invalid <versionNo> value.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoStatusElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'status',
                'content'  => 'PUBLISHED',
            ),
            $result,
            'Invalid <status> value.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoCreationDateElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'creationDate',
                'content'  => $this->creationDate->format( 'c' ),
            ),
            $result,
            'Invalid <creationDate> value.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoModificationDateElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'modificationDate',
                'content'  => $this->modificationDate->format( 'c' ),
            ),
            $result,
            'Invalid <modificationDate> value.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoInitialLanguageCodeElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'initialLanguageCode',
                'content'  => 'eng-US',
            ),
            $result,
            'Invalid <initialLanguageCode> value.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoLanguageCodesElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'languageCodes',
                'content'  => 'eng-US,ger-DE',
            ),
            $result,
            'Invalid <languageCodes> value.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoNamesElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'names',
                'children' => array(
                    'less_than'    => 3,
                    'greater_than' => 1,
                )
            ),
            $result,
            'Invalid <names> value.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoContentElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Content',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentInfo+xml',
                    'href' => '/content/objects/42'
                )
            ),
            $result,
            'Invalid <initialLanguageCode> value.',
            false
        );
    }

    /**
     * Get the VersionInfo visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\VersionInfo
     */
    protected function getVersionInfoVisitor()
    {
        return new ValueObjectVisitor\VersionInfo(
            new Common\UrlHandler\eZPublish()
        );
    }
}
