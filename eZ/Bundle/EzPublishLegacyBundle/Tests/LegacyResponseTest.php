<?php
/**
 * File containing the LegacyResponseTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests;

use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse;
use PHPUnit_Framework_TestCase;
use DateTime;

class LegacyResponseTest extends PHPUnit_Framework_TestCase
{
    public function generateMockResponse()
    {
        return $this->getMockBuilder( 'eZ\Bundle\EzPublishLegacyBundle\LegacyResponse' )
                ->setMethods( array( 'removeHeader' ) )
                ->getMock();
    }

    public function testSetHeaders()
    {
        $etag = '86fb269d190d2c85f6e0468ceca42a20';
        $date = new DateTime();
        $dateForCache = $date->format( 'D, d M Y H:i:s' ).' GMT';

        $headers = array(
            'X-Foo: Bar',
            'Etag: '.$etag,
            'Last-Modified: '.$dateForCache,
            'Expires: '.$dateForCache,
        );

        // Partially mock the legacy response to emulate calls
        // to the header_remove() function.
        $response = $this->generateMockResponse();

        $response
            ->expects( $this->exactly( count( $headers ) ) )
            ->method( 'removeHeader' );

        $response->setLegacyHeaders( $headers );

        $this->assertSame( 'Bar', $response->headers->get( 'X-Foo' ) );
        $this->assertSame( '"' . $etag . '"', $response->getEtag() );
        $this->assertEquals( new DateTime( $dateForCache ), $response->getLastModified() );
        $this->assertEquals( new DateTime( $dateForCache ), $response->getExpires() );
    }
}
