<?php

/**
 * File containing the FOSPurgeClientTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\FOSPurgeClient;
use PHPUnit_Framework_TestCase;

class FOSPurgeClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    /**
     * @var FOSPurgeClient
     */
    private $purgeClient;

    protected function setUp()
    {
        parent::setUp();
        $this->cacheManager = $this->getMockBuilder('\FOS\HttpCacheBundle\CacheManager')
            ->setConstructorArgs(
                array(
                    $this->getMock('\FOS\HttpCache\ProxyClient\ProxyClientInterface'),
                    $this->getMock(
                        '\Symfony\Component\Routing\Generator\UrlGeneratorInterface'
                    ),
                )
            )
            ->getMock();
        $this->purgeClient = new FOSPurgeClient($this->cacheManager);
    }

    public function testPurgeNoLocationIds()
    {
        $this->cacheManager
            ->expects($this->never())
            ->method('invalidate');
        $this->purgeClient->purge(array());
    }

    public function testPurgeOneLocationId()
    {
        $locationId = 123;
        $this->cacheManager
            ->expects($this->once())
            ->method('invalidate')
            ->with(array('xkey' => "^(location-$locationId)$"));

        $this->purgeClient->purge($locationId);
    }

    /**
     * @dataProvider purgeTestProvider
     */
    public function testPurge(array $locationIds, $headerString)
    {
        $this->cacheManager
            ->expects($this->once())
            ->method('invalidate')
            ->with(array('xkey' => "^($headerString)$"));

        $this->purgeClient->purge($locationIds);
    }

    public function purgeTestProvider()
    {
        return array(
            array(array(123), 'location-123'),
            array(array(123, 456), 'location-123|location-456'),
            array(array(123, 456, 789), 'location-123|location-456|location-789'),
        );
    }

    public function testPurgeAll()
    {
        $this->cacheManager
            ->expects($this->once())
            ->method('invalidate')
            ->with(array('xkey' => '.*'));

        $this->purgeClient->purgeAll();
    }
}
