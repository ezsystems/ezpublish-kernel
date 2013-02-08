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
use eZ\Publish\Core\REST\Server\Values\URLAliasList;
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\Core\REST\Common;

class URLAliasListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the URLAliasList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getURLAliasListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $urlAliasList = new URLAliasList( array(), '/content/urlaliases' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains UrlAliasList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'UrlAliasList',
            ),
            $result,
            'Invalid <UrlAliasList> element.',
            false
        );
    }

    /**
     * Test if result contains UrlAliasList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'UrlAliasList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UrlAliasList+xml',
                    'href'       => '/content/urlaliases',
                )
            ),
            $result,
            'Invalid <UrlAliasList> attributes.',
            false
        );
    }

    /**
     * Test if URLAliasList visitor visits the children
     */
    public function testURLAliasListVisitsChildren()
    {
        $visitor   = $this->getURLAliasListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $urlAliasList = new URLAliasList(
            array(
                new Content\URLAlias(),
                new Content\URLAlias(),
            ),
            '/content/urlaliases'
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasList
        );
    }

    /**
     * Get the URLAliasList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\URLAliasList
     */
    protected function getURLAliasListVisitor()
    {
        return new ValueObjectVisitor\URLAliasList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
