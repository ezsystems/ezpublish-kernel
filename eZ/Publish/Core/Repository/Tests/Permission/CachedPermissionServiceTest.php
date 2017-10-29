<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\PermissionCriterionHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Permission;

/**
 * Avoid test failure caused by time passing between generating expected & actual object.
 *
 * @return int
 */
function time()
{
    static $time = 1417624981;

    return ++$time;
}

namespace eZ\Publish\Core\Repository\Tests\Permission;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\PermissionCriterionResolver;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\Core\Repository\Permission\CachedPermissionService;
use PHPUnit\Framework\TestCase;

/**
 * Mock test case for CachedPermissionService.
 */
class CachedPermissionServiceTest extends TestCase
{
    /**
     * Test for the __construct() method.
     */
    public function testConstructor()
    {
        $permissionResolverMock = $this->getPermissionResolverMock();
        $criterionResolverMock = $this->getPermissionCriterionResolverMock();
        $cachedService = $this->getCachedPermissionService(10);

        $this->assertAttributeSame(
            $permissionResolverMock,
            'permissionResolver',
            $cachedService
        );
        $this->assertAttributeSame(
            $criterionResolverMock,
            'permissionCriterionResolver',
            $cachedService
        );
        $this->assertAttributeEquals(
            10,
            'cacheTTL',
            $cachedService
        );
        $this->assertAttributeEmpty(
            'permissionCriterion',
            $cachedService
        );
        $this->assertAttributeEmpty(
            'permissionCriterionTs',
            $cachedService
        );
    }

    public function providerForTestPermissionResolverPassTrough()
    {
        $valueObject = $this
            ->getMockBuilder(ValueObject::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $userRef = $this
            ->getMockBuilder(UserReference::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return [
            ['getCurrentUserReference', [], $userRef],
            ['setCurrentUserReference', [$userRef], null],
            ['hasAccess', ['content', 'remove', $userRef], false],
            ['canUser', ['content', 'remove', $valueObject, [new \stdClass()]], true],
            ['sudo', [function () {}, $repository], null],
        ];
    }

    /**
     * Test for all PermissionResolver methods when they just pass true to underlying service.
     *
     * @dataProvider providerForTestPermissionResolverPassTrough
     *
     * @param $method
     * @param array $arguments
     * @param $return
     */
    public function testPermissionResolverPassTrough($method, array $arguments, $expectedReturn)
    {
        $this->getPermissionResolverMock([$method])
            ->expects($this->once())
            ->method($method)
            ->with(...$arguments)
            ->willReturn($expectedReturn);

        $cachedService = $this->getCachedPermissionService();

        $actualReturn = $cachedService->$method(...$arguments);
        $this->assertSame($expectedReturn, $actualReturn);

        // Make sure no cache properties where set
        $this->assertAttributeEmpty('permissionCriterion', $cachedService);
        $this->assertAttributeEmpty('permissionCriterionTs', $cachedService);
    }

    public function testGetPermissionsCriterionPassTrough()
    {
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->getPermissionCriterionResolverMock(['getPermissionsCriterion'])
            ->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'remove')
            ->willReturn($criterionMock);

        $cachedService = $this->getCachedPermissionService();

        $actualReturn = $cachedService->getPermissionsCriterion('content', 'remove');
        $this->assertSame($criterionMock, $actualReturn);

        // Make sure no cache properties where set
        $this->assertAttributeEmpty('permissionCriterion', $cachedService);
        $this->assertAttributeEmpty('permissionCriterionTs', $cachedService);
    }

    public function testGetPermissionsCriterionCaching()
    {
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->getPermissionCriterionResolverMock(['getPermissionsCriterion'])
            ->expects($this->exactly(2))
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->willReturn($criterionMock);

        $cachedService = $this->getCachedPermissionService(2);

        $actualReturn = $cachedService->getPermissionsCriterion('content', 'read');
        $this->assertSame($criterionMock, $actualReturn);
        $this->assertAttributeSame($criterionMock, 'permissionCriterion', $cachedService);
        $this->assertAttributeEquals(1417624982, 'permissionCriterionTs', $cachedService);

        // +1
        $actualReturn = $cachedService->getPermissionsCriterion('content', 'read');
        $this->assertSame($criterionMock, $actualReturn);
        $this->assertAttributeSame($criterionMock, 'permissionCriterion', $cachedService);
        $this->assertAttributeEquals(1417624982, 'permissionCriterionTs', $cachedService);

        // +3, time() will be called twice and cache will be updated
        $actualReturn = $cachedService->getPermissionsCriterion('content', 'read');
        $this->assertSame($criterionMock, $actualReturn);
        $this->assertAttributeSame($criterionMock, 'permissionCriterion', $cachedService);
        $this->assertAttributeEquals(1417624985, 'permissionCriterionTs', $cachedService);
    }

    public function testSetCurrentUserReferenceCacheClear()
    {
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->getPermissionCriterionResolverMock(['getPermissionsCriterion'])
            ->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->willReturn($criterionMock);

        $cachedService = $this->getCachedPermissionService(2);

        $actualReturn = $cachedService->getPermissionsCriterion('content', 'read');
        $this->assertSame($criterionMock, $actualReturn);
        $this->assertAttributeSame($criterionMock, 'permissionCriterion', $cachedService);

        $userRef = $this
            ->getMockBuilder(UserReference::class)
            ->getMockForAbstractClass();
        $cachedService->setCurrentUserReference($userRef);
        $this->assertAttributeEmpty('permissionCriterion', $cachedService);
    }

    /**
     * Returns the CachedPermissionService to test against.
     *
     * @param int $ttl
     *
     * @return \eZ\Publish\Core\Repository\Permission\CachedPermissionService
     */
    protected function getCachedPermissionService($ttl = 5)
    {
        return new CachedPermissionService(
            $this->getPermissionResolverMock(),
            $this->getPermissionCriterionResolverMock(),
            $ttl
        );
    }

    protected $permissionResolverMock;

    protected function getPermissionResolverMock($methods = [])
    {
        // Tests first calls here with methods set before initiating PermissionCriterionResolver with same instance.
        if ($this->permissionResolverMock !== null) {
            return $this->permissionResolverMock;
        }

        return $this->permissionResolverMock = $this
            ->getMockBuilder(PermissionResolver::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    protected $permissionCriterionResolverMock;

    protected function getPermissionCriterionResolverMock($methods = [])
    {
        // Tests first calls here with methods set before initiating PermissionCriterionResolver with same instance.
        if ($this->permissionCriterionResolverMock !== null) {
            return $this->permissionCriterionResolverMock;
        }

        return $this->permissionCriterionResolverMock = $this
            ->getMockBuilder(PermissionCriterionResolver::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }
}
