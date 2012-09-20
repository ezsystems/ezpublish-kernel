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
use eZ\Publish\Core\REST\Common;

use eZ\Publish\Core\REST\Server\Values\Version;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\Content\ContentInfo;

class VersionTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Version visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getVersionVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $version = new Version(
            new VersionInfo(
                array(
                    'versionNo' => 21,
                    'contentInfo' => new ContentInfo(
                        array(
                           'id' => 42
                        )
                    )
                )
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $version
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains Version element
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsVersionElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Version',
            ),
            $result,
            'Invalid <Version> element.',
            false
        );
    }

    /**
     * Test if result contains Version element attributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsVersionAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Version',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Version+xml',
                    'href'       => '/content/objects/42/versions/21',
                )
            ),
            $result,
            'Invalid <Version> attributes.',
            false
        );
    }

    /**
     * Test if Version visitor visits the children
     */
    public function testVersionVisitsChildren()
    {
        $visitor   = $this->getVersionVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $version = new Version(
            new VersionInfo(
                array(
                    'versionNo' => 21,
                    'contentInfo' => new ContentInfo(
                        array(
                           'id' => 42
                        )
                    )
                )
            )
        );

        $this->getVisitorMock()->expects( $this->exactly( 1 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $version
        );
    }

    /**
     * Get the Version visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Version
     */
    protected function getVersionVisitor()
    {
        return new ValueObjectVisitor\Version(
            new Common\UrlHandler\eZPublish()
        );
    }
}
