<?php
/**
 * File containing the PatternTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\UrlHandler;

use eZ\Publish\Core\REST\Common;

/**
 * Test for Pattern based url handler
 */
class PatternTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests parsing unknown URL type
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage No URL for type 'unknown' available.
     */
    public function testParseUnknownUrlType()
    {
        $urlHandler = new Common\UrlHandler\Pattern();
        $urlHandler->parse( 'unknown', '/foo' );
    }

    /**
     * Tests parsing invalid pattern
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Invalid pattern part: '{broken'.
     */
    public function testParseInvalidPattern()
    {
        $urlHandler = new Common\UrlHandler\Pattern(
            array(
                'invalid' => '/foo/{broken',
            )
        );
        $urlHandler->parse( 'invalid', '/foo' );
    }

    /**
     * Tests parsing when pattern does not match
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage URL '/bar' did not match (^/foo/(?P<foo>[^/]+)$)S.
     */
    public function testPatternDoesNotMatch()
    {
        $urlHandler = new Common\UrlHandler\Pattern(
            array(
                'pattern' => '/foo/{foo}',
            )
        );
        $urlHandler->parse( 'pattern', '/bar' );
    }

    /**
     * Test parsing when pattern does not match the end of the URL
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage URL '/foo/23/bar' did not match (^/foo/(?P<foo>[^/]+)$)S.
     */
    public function testPatternDoesNotMatchTrailing()
    {
        $urlHandler = new Common\UrlHandler\Pattern(
            array(
                'pattern' => '/foo/{foo}',
            )
        );
        $urlHandler->parse( 'pattern', '/foo/23/bar' );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function getParseValues()
    {
        return array(
            array(
                'section',
                '/content/section/42',
                array(
                    'section' => '42',
                )
            ),
            array(
                'objectversion',
                '/content/object/42/23',
                array(
                    'object'  => '42',
                    'version' => '23',
                )
            ),
            array(
                'location',
                '/content/locations/23/42/100',
                array(
                    'location' => '23/42/100',
                )
            ),
            array(
                'locationChildren',
                '/content/locations/23/42/100/children',
                array(
                    'location' => '23/42/100',
                )
            )
        );
    }

    /**
     * Test parsing URL
     *
     * @dataProvider getParseValues
     */
    public function testParseUrl( $type, $url, $values )
    {
        $urlHandler = $this->getWorkingUrlHandler();

        $this->assertSame(
            $values,
            $urlHandler->parse( $type, $url )
        );
    }

    /**
     * Test generating unknown URL type
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage No URL for type 'unknown' available.
     */
    public function testGenerateUnknownUrlType()
    {
        $urlHandler = new Common\UrlHandler\Pattern();
        $urlHandler->generate( 'unknown', array() );
    }

    /**
     * Test generating URL with missing value
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage No value provided for 'unknown'.
     */
    public function testGenerateMissingValue()
    {
        $urlHandler = new Common\UrlHandler\Pattern(
            array(
                'pattern' => '/foo/{unknown}',
            )
        );
        $urlHandler->generate( 'pattern', array() );
    }

    /**
     * Test generating URL with extra value
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Unused values in values array: 'bar'.
     */
    public function testGenerateSuperfluousValue()
    {
        $urlHandler = new Common\UrlHandler\Pattern(
            array(
                'pattern' => '/foo/{foo}',
            )
        );
        $urlHandler->generate(
            'pattern',
            array(
                'foo' => 23,
                'bar' => 42,
            )
        );
    }

    /**
     * Data provider
     *
     * @dataProvider getParseValues
     */
    public function testGenerateUrl( $type, $url, $values )
    {
        $urlHandler = $this->getWorkingUrlHandler();

        $this->assertSame(
            $url,
            $urlHandler->generate( $type, $values )
        );
    }

    /**
     * Returns the URL handler
     *
     * @return \eZ\Publish\Core\REST\Common\UrlHandler\Pattern
     */
    protected function getWorkingUrlHandler()
    {
        return new Common\UrlHandler\Pattern(
            array(
                'section'          => '/content/section/{section}',
                'objectversion'    => '/content/object/{object}/{version}',
                'locationChildren' => '/content/locations/{&location}/children',
                'location'         => '/content/locations/{&location}',
            )
        );
    }
}
