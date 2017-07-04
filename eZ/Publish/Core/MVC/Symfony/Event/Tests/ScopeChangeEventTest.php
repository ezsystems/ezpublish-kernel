<?php

/**
 * File containing the ScopeChangeEventTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Event\Tests;

use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;

class ScopeChangeEventTest extends TestCase
{
    public function testGetSiteAccess()
    {
        $siteAccess = new SiteAccess('foo', 'test');
        $event = new ScopeChangeEvent($siteAccess);
        $this->assertSame($siteAccess, $event->getSiteAccess());
    }
}
