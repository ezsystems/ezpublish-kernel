<?php
/**
 * File containing the HttpBasedPurgeClientTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http;

use PHPUnit_Framework_TestCase;

abstract class HttpBasedPurgeClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpBrowser;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configResolver;

    protected function setUp()
    {
        if ( !class_exists( 'Buzz\\Browser' ) )
            self::markTestSkipped( 'Please install kriswallsmith/buzz library from Composer' );

        parent::setUp();

        $this->httpClient = $this->getMock( 'Buzz\\Client\\BatchClientInterface' );
        $this->httpBrowser = $this
            ->getMockBuilder( 'Buzz\\Browser' )
            ->setConstructorArgs( array( $this->httpClient ) )
            ->getMock();
        $this->httpBrowser
            ->expects( $this->any() )
            ->method( 'getClient' )
            ->will( $this->returnValue( $this->httpClient ) );

        $this->configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
    }

    abstract public function testPurge();
}
