<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\VersionList;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\REST\Common;

class VersionListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the VersionList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getVersionListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $versionList = new VersionList( array(), '/some/path' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $versionList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains VersionList element
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsVersionListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'VersionList',
            ),
            $result,
            'Invalid <VersionList> element.',
            false
        );
    }

    /**
     * Test if result contains VersionList element attributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsVersionListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'VersionList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.VersionList+xml',
                    'href'       => '/some/path',
                )
            ),
            $result,
            'Invalid <VersionList> attributes.',
            false
        );
    }

    /**
     * Test if VersionList visitor visits the children
     */
    public function testVersionListVisitsChildren()
    {
        $visitor   = $this->getVersionListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $versionList = new VersionList(
            array(
                new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(
                            array(
                                'id' => 42
                            )
                        ),
                        'versionNo' => 1
                    )
                ),
                new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(
                            array(
                                'id' => 42
                            )
                        ),
                        'versionNo' => 2
                    )
                )
            ),
            '/some/path'
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $versionList
        );
    }

    /**
     * Get the VersionList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\VersionList
     */
    protected function getVersionListVisitor()
    {
        return new ValueObjectVisitor\VersionList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
