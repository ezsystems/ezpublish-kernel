<?php

/**
 * File containing the PatternTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Common\Tests\UrlHandler;

use eZ\Publish\Core\REST\Common;
use PHPUnit_Framework_TestCase;

/**
 * Test for Pattern based url handler.
 */
class PatternTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests parsing unknown URL type.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage URL '/foo' did not match any route.
     */
    public function testParseUnknownUrlType()
    {
        $urlHandler = new Common\RequestParser\Pattern();
        $urlHandler->parse('/foo');
    }

    /**
     * Tests parsing invalid pattern.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Invalid pattern part: '{broken'.
     */
    public function testParseInvalidPattern()
    {
        $urlHandler = new Common\RequestParser\Pattern(
            array(
                'invalid' => '/foo/{broken',
            )
        );
        $urlHandler->parse('/foo');
    }

    /**
     * Tests parsing when pattern does not match.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage URL '/bar' did not match any route.
     */
    public function testPatternDoesNotMatch()
    {
        $urlHandler = new Common\RequestParser\Pattern(
            array(
                'pattern' => '/foo/{foo}',
            )
        );
        $urlHandler->parse('/bar');
    }

    /**
     * Test parsing when pattern does not match the end of the URL.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage URL '/foo/23/bar' did not match any route.
     */
    public function testPatternDoesNotMatchTrailing()
    {
        $urlHandler = new Common\RequestParser\Pattern(
            array(
                'pattern' => '/foo/{foo}',
            )
        );
        $urlHandler->parse('/foo/23/bar');
    }

    /**
     * Data provider.
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
                ),
            ),
            array(
                'objectversion',
                '/content/object/42/23',
                array(
                    'object' => '42',
                    'version' => '23',
                ),
            ),
            array(
                'location',
                '/content/locations/23/42/100',
                array(
                    'location' => '23/42/100',
                ),
            ),
            array(
                'locationChildren',
                '/content/locations/23/42/100/children',
                array(
                    'location' => '23/42/100',
                ),
            ),
        );
    }

    /**
     * Test parsing URL.
     *
     * @dataProvider getParseValues
     */
    public function testParseUrl($type, $url, $values)
    {
        $urlHandler = $this->getWorkingUrlHandler();

        $this->assertSame(
            $values,
            $urlHandler->parse($url)
        );
    }

    /**
     * Test generating unknown URL type.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage No URL for type 'unknown' available.
     */
    public function testGenerateUnknownUrlType()
    {
        $urlHandler = new Common\RequestParser\Pattern();
        $urlHandler->generate('unknown', array());
    }

    /**
     * Test generating URL with missing value.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage No value provided for 'unknown'.
     */
    public function testGenerateMissingValue()
    {
        $urlHandler = new Common\RequestParser\Pattern(
            array(
                'pattern' => '/foo/{unknown}',
            )
        );
        $urlHandler->generate('pattern', array());
    }

    /**
     * Test generating URL with extra value.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Unused values in values array: 'bar'.
     */
    public function testGenerateSuperfluousValue()
    {
        $urlHandler = new Common\RequestParser\Pattern(
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
     * Data provider.
     *
     * @dataProvider getParseValues
     */
    public function testGenerateUrl($type, $url, $values)
    {
        $urlHandler = $this->getWorkingUrlHandler();

        $this->assertSame(
            $url,
            $urlHandler->generate($type, $values)
        );
    }

    /**
     * Returns the URL handler.
     *
     * @return \eZ\Publish\Core\REST\Common\RequestParser\Pattern
     */
    protected function getWorkingUrlHandler()
    {
        return new Common\RequestParser\Pattern(
            array(
                'section' => '/content/section/{section}',
                'objectversion' => '/content/object/{object}/{version}',
                'locationChildren' => '/content/locations/{&location}/children',
                'location' => '/content/locations/{&location}',
            )
        );
    }
}
