<?php
/**
 * File containing the InstantCachePurgerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\InstantCachePurger;
use PHPUnit_Framework_TestCase;

class InstantCachePurgerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $purgeClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    protected function setUp()
    {
        parent::setUp();
        $this->purgeClient = $this->getMock( '\eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface' );
        $this->contentService = $this->getMock( '\eZ\Publish\API\Repository\ContentService' );
        $this->eventDispatcher = $this->getMock( '\Symfony\Component\EventDispatcher\EventDispatcherInterface' );
    }

    public function testPurge()
    {
        $locationIds = array( 123, 456, 789 );
        $this->purgeClient
            ->expects( $this->once() )
            ->method( 'purge' )
            ->with( $locationIds )
            ->will( $this->returnArgument( 0 ) );

        $purger = new InstantCachePurger( $this->purgeClient, $this->contentService, $this->eventDispatcher );
        $this->assertSame( $locationIds, $purger->purge( $locationIds ) );
    }

    public function testPurgeAll()
    {
        $this->purgeClient
            ->expects( $this->once() )
            ->method( 'purgeAll' );

        $purger = new InstantCachePurger( $this->purgeClient, $this->contentService, $this->eventDispatcher );
        $purger->purgeAll();
    }
}
