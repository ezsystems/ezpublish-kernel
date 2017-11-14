<?php

/**
 * File containing the FOSPurgeClientTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\FOSPurgeClient;
use FOS\HttpCacheBundle\CacheManager;
use FOS\HttpCache\ProxyClient\ProxyClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;

class FOSPurgeClientTest extends TestCase
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
        $this->cacheManager = $this->getMockBuilder(CacheManager::class)
            ->setConstructorArgs(
                array(
                    $this->createMock(ProxyClientInterface::class),
                    $this->createMock(UrlGeneratorInterface::class),
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
            ->with(array('X-Location-Id' => "^($locationId)$"));

        $this->purgeClient->purge($locationId);
    }

    /**
     * @dataProvider purgeTestProvider
     */
    public function testPurge(array $locationIds)
    {
        $this->cacheManager
            ->expects($this->once())
            ->method('invalidate')
            ->with(array('X-Location-Id' => '^(' . implode('|', $locationIds) . ')$'));

        $this->purgeClient->purge($locationIds);
    }

    public function purgeTestProvider()
    {
        return array(
            array(array(123)),
            array(array(123, 456)),
            array(array(123, 456, 789)),
        );
    }

    public function testPurgeAll()
    {
        $this->cacheManager
            ->expects($this->once())
            ->method('invalidate')
            ->with(array('X-Location-Id' => '.*'));

        $this->purgeClient->purgeAll();
    }
}
