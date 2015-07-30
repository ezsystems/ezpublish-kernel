<?php

/**
 * File containing the LocalPurgeClientTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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

use eZ\Publish\Core\MVC\Symfony\Cache\Http\LocalPurgeClient;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

class LocalPurgeClientTest extends PHPUnit_Framework_TestCase
{
    public function testPurge()
    {
        $locationIds = array(123, 456, 789);
        $expectedBanRequest = Request::create('http://localhost', 'BAN');
        $expectedBanRequest->headers->set('X-Location-Id', '(' . implode('|', $locationIds) . ')');

        $cacheStore = $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\Cache\\Http\\ContentPurger');
        $cacheStore
            ->expects($this->once())
            ->method('purgeByRequest')
            ->with($this->equalTo($expectedBanRequest));

        $purgeClient = new LocalPurgeClient($cacheStore);
        $purgeClient->purge($locationIds);
    }

    public function testPurgeAll()
    {
        $cacheStore = $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\Cache\\Http\\ContentPurger');
        $cacheStore
            ->expects($this->once())
            ->method('purgeAllContent');

        $purgeClient = new LocalPurgeClient($cacheStore);
        $purgeClient->purgeAll();
    }
}
