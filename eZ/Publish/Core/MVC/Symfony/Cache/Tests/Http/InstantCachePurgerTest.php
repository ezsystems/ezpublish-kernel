<?php

/**
 * File containing the InstantCachePurgerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\Core\Base\Tests\PHPUnit5CompatTrait;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\InstantCachePurger;
use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use eZ\Publish\Core\MVC\Symfony\Event\ContentCacheClearEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\Repository\Helper\LimitationService;
use eZ\Publish\Core\Repository\Helper\RoleDomainMapper;
use eZ\Publish\Core\Repository\Permission\PermissionResolver;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\SPI\Persistence\User\Handler as UserHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InstantCachePurgerTest extends TestCase
{
    use PHPUnit5CompatTrait;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $purgeClient;

    /**
     * @var \eZ\Publish\API\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentService;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var \eZ\Publish\Core\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->purgeClient = $this->createMock(PurgeClientInterface::class);
        $this->contentService = $this->createMock(ContentService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                array_diff(
                    get_class_methods(Repository::class),
                    array('sudo')
                )
            )
            ->getMock();

        $this->repository
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->will($this->returnValue($this->getPermissionResolverMock()));
    }

    public function testPurge()
    {
        $locationIds = array(123, 456, 789);
        $this->purgeClient
            ->expects($this->once())
            ->method('purge')
            ->with($locationIds)
            ->will($this->returnArgument(0));

        $purger = new InstantCachePurger($this->purgeClient, $this->contentService, $this->eventDispatcher, $this->repository);
        $this->assertSame($locationIds, $purger->purge($locationIds));
    }

    public function testPurgeAll()
    {
        $this->purgeClient
            ->expects($this->once())
            ->method('purgeAll');

        $purger = new InstantCachePurger($this->purgeClient, $this->contentService, $this->eventDispatcher, $this->repository);
        $purger->purgeAll();
    }

    public function testPurgeForContent()
    {
        $contentId = 123;
        $contentInfo = new ContentInfo(['id' => $contentId, 'published' => true]);
        // Assume listeners have added locations.
        // Adding duplicates on purpose.
        $locationIds = [123, 456, 789, 234, 567];

        $this->contentService
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($contentId)
            ->will($this->returnValue($contentInfo));

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(
            MVCEvents::CACHE_CLEAR_CONTENT,
            function (ContentCacheClearEvent $event) use ($locationIds) {
                foreach ($locationIds as $id) {
                    $event->addLocationToClear(new Location(['id' => $id]));
                }

                // Adding a few duplicates on purpose.
                $event->addLocationToClear(new Location(['id' => 123]));
                $event->addLocationToClear(new Location(['id' => 567]));
            }
        );

        $this->purgeClient
            ->expects($this->once())
            ->method('purge')
            ->with($locationIds);

        $purger = new InstantCachePurger($this->purgeClient, $this->contentService, $eventDispatcher, $this->repository);
        $purger->purgeForContent($contentId);
    }

    /**
     * @return \eZ\Publish\Core\Repository\Permission\PermissionResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPermissionResolverMock()
    {
        return $this
            ->getMockBuilder(PermissionResolver::class)
            ->setMethods(null)
            ->setConstructorArgs(
                [
                    $this
                        ->getMockBuilder(RoleDomainMapper::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this
                        ->getMockBuilder(LimitationService::class)
                        ->getMock(),
                    $this
                        ->getMockBuilder(UserHandler::class)
                        ->getMock(),
                    $this
                        ->getMockBuilder(UserReference::class)
                        ->getMock(),
                ]
            )
            ->getMock();
    }
}
