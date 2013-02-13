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

class URLAliasTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the URLAlias visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getURLAliasVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $urlAlias = new Content\URLAlias(
            array(
                'id' => 'some-id',
                'type' => 1,
                'destination' => '/destination/url',
                'path' => '/some/path',
                'languageCodes' => array( 'eng-GB', 'eng-US' ),
                'alwaysAvailable' => true,
                'isHistory' => true,
                'isCustom' => false,
                'forward' => false
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAlias
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains UrlAlias element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'UrlAlias',
                'children' => array(
                    'less_than'    => 8,
                    'greater_than' => 6,
                )
            ),
            $result,
            'Invalid <UrlAlias> element.',
            false
        );
    }

    /**
     * Test if result contains UrlAlias element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'UrlAlias',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UrlAlias+xml',
                    'href'       => '/content/urlaliases/some-id',
                    'id'         => 'some-id',
                    'type'       => 'RESOURCE'
                )
            ),
            $result,
            'Invalid <UrlAlias> attributes.',
            false
        );
    }

    /**
     * Test if result contains url value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'resource',
                'content'  => '/destination/url',
            ),
            $result,
            'Invalid or non-existing <UrlAlias> url value element.',
            false
        );
    }

    /**
     * Test if result contains path value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPathValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'path',
                'content'  => '/some/path',
            ),
            $result,
            'Invalid or non-existing <UrlAlias> path value element.',
            false
        );
    }

    /**
     * Test if result contains languageCodes value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLanguageCodesValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'languageCodes',
                'content'  => 'eng-GB,eng-US',
            ),
            $result,
            'Invalid or non-existing <UrlAlias> languageCodes value element.',
            false
        );
    }

    /**
     * Test if result contains alwaysAvailable value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsAlwaysAvailableValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'alwaysAvailable',
                'content'  => 'true',
            ),
            $result,
            'Invalid or non-existing <UrlAlias> alwaysAvailable value element.',
            false
        );
    }

    /**
     * Test if result contains isHistory value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIsHistoryValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'isHistory',
                'content'  => 'true',
            ),
            $result,
            'Invalid or non-existing <UrlAlias> isHistory value element.',
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
                'content'  => 'false',
            ),
            $result,
            'Invalid or non-existing <UrlAlias> forward value element.',
            false
        );
    }

    /**
     * Test if result contains custom value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsCustomValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'custom',
                'content'  => 'false',
            ),
            $result,
            'Invalid or non-existing <UrlAlias> custom value element.',
            false
        );
    }

    /**
     * Get the URLAlias visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\URLAlias
     */
    protected function getURLAliasVisitor()
    {
        return new ValueObjectVisitor\URLAlias(
            new Common\UrlHandler\eZPublish()
        );
    }
}
