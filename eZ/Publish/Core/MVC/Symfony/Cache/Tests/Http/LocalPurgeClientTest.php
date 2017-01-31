<?php

/**
 * File containing the LocalPurgeClientTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Symfony\Component\HttpFoundation;

/**
 * Avoid test failure caused by time passing between generating expected & actual object.
 *
 * @return int
 */
function time()
{
    return 1417624982;
}

namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ContentPurger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\LocalPurgeClient;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

class LocalPurgeClientTest extends PHPUnit_Framework_TestCase
{
    public function testPurge()
    {
        $locationIds = array(123, 456, 789);
        $expectedBanRequest = Request::create('http://localhost', 'PURGE');
        $expectedBanRequest->headers->set('key', 'location-123 location-456 location-789');

        $cacheStore = $this->getMock(ContentPurger::class);
        $cacheStore
            ->expects($this->once())
            ->method('purgeByRequest')
            ->with($this->equalTo($expectedBanRequest));

        $purgeClient = new LocalPurgeClient($cacheStore);
        $purgeClient->purge($locationIds);
    }

    public function testPurgeAll()
    {
        $cacheStore = $this->getMock(ContentPurger::class);
        $cacheStore
            ->expects($this->once())
            ->method('purgeAllContent');

        $purgeClient = new LocalPurgeClient($cacheStore);
        $purgeClient->purgeAll();
    }
}
