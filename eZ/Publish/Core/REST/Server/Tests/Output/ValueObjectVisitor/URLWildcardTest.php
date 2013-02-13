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
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\Core\REST\Common;

class URLWildcardTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the URLWildcard visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getURLWildcardVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $urlWildcard = new Content\URLWildcard(
            array(
                'id' => 42,
                'sourceUrl' => '/source/url',
                'destinationUrl' => '/destination/url',
                'forward' => true
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlWildcard
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains UrlWildcard element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'UrlWildcard',
                'children' => array(
                    'less_than'    => 4,
                    'greater_than' => 2,
                )
            ),
            $result,
            'Invalid <UrlWildcard> element.',
            false
        );
    }

    /**
     * Test if result contains UrlWildcard element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'UrlWildcard',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UrlWildcard+xml',
                    'href'       => '/content/urlwildcards/42',
                    'id'         => '42',
                )
            ),
            $result,
            'Invalid <UrlWildcard> attributes.',
            false
        );
    }

    /**
     * Test if result contains sourceUrl value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSourceUrlValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'sourceUrl',
                'content'  => '/source/url',
            ),
            $result,
            'Invalid or non-existing <UrlWildcard> sourceUrl value element.',
            false
        );
    }

    /**
     * Test if result contains destinationUrl value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDestinationUrlValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'destinationUrl',
                'content'  => '/destination/url',
            ),
            $result,
            'Invalid or non-existing <UrlWildcard> destinationUrl value element.',
            false
        );
    }

    /**
     * Test if result contains forward value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsForwardValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'forward',
                'content'  => 'true',
            ),
            $result,
            'Invalid or non-existing <UrlWildcard> forward value element.',
            false
        );
    }

    /**
     * Get the URLWildcard visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\URLWildcard
     */
    protected function getURLWildcardVisitor()
    {
        return new ValueObjectVisitor\URLWildcard(
            new Common\UrlHandler\eZPublish()
        );
    }
}
