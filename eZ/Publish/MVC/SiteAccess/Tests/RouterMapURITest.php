<?php
/**
 * File containing the RouterMapURITest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Tests;

use eZ\Publish\MVC\SiteAccess\Matcher\Map\URI as URIMapMatcher;

class RouterMapURITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $uri
     * @param $expectedFixedUpURI
     * @dataProvider fixupURIProvider
     * @covers \eZ\Publish\MVC\SiteAccess\Matcher\Map\URI::analyseURI
     */
    public function testAnalyseURI( $uri, $expectedFixedUpURI )
    {
        $matcher = new URIMapMatcher(
            array( 'path' => $uri ),
            array()
        );
        $this->assertSame( $expectedFixedUpURI, $matcher->analyseURI( $uri ) );
    }

    /**
     * @param $fullUri
     * @param $linkUri
     * @dataProvider fixupURIProvider
     * @covers \eZ\Publish\MVC\SiteAccess\Matcher\Map\URI::analyseLink
     */
    public function testAnalyseLink( $fullUri, $linkUri )
    {
        $matcher = new URIMapMatcher(
            array( 'path' => $fullUri ),
            array()
        );
        $this->assertSame( $fullUri, $matcher->analyseLink( $linkUri ) );
    }

    public function fixupURIProvider()
    {
        return array(
            array( '/my_siteaccess/foo/bar', '/foo/bar' ),
            array( '/vive/le/sucre', '/le/sucre' )
        );
    }
}
