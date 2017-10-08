<?php

/**
 * File containing the BaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Permission\PermissionResolver;
use eZ\Publish\Core\Repository\Helper\RoleDomainMapper;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\SPI\Persistence\User\Handler as SPIUserHandler;
use eZ\Publish\Core\Repository\Helper\LimitationService;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Location\Configured;
use eZ\Publish\Core\Repository\Repository;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    protected function setUp()
    {
        parent::setUp();
        $this->repositoryMock = $this->getRepositoryMock();
    }

    /**
     * @param array $matchingConfig
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartiallyMockedViewProvider(array $matchingConfig = array())
    {
        return $this
            ->getMockBuilder(Configured::class)
            ->setConstructorArgs(
                array(
                    $this->repositoryMock,
                    $matchingConfig,
                )
            )
            ->setMethods(array('getMatcher'))
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        $repositoryClass = Repository::class;

        return $this
            ->getMockBuilder($repositoryClass)
            ->disableOriginalConstructor()
            ->setMethods(
                array_diff(
                    get_class_methods($repositoryClass),
                    array('sudo')
                )
            )
            ->getMock();
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLocationMock(array $properties = array())
    {
        return $this
            ->getMockBuilder(Location::class)
            ->setConstructorArgs(array($properties))
            ->getMockForAbstractClass();
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentInfoMock(array $properties = array())
    {
        return $this->
            getMockBuilder(ContentInfo::class)
            ->setConstructorArgs(array($properties))
            ->getMockForAbstractClass();
    }

    protected function getPermissionResolverMock()
    {
        return $this
            ->getMockBuilder(PermissionResolver::class)
            ->setMethods(null)
            ->setConstructorArgs(
                [
                    $this->createMock(RoleDomainMapper::class),
                    $this->createMock(LimitationService::class),
                    $this->createMock(SPIUserHandler::class),
                    $this->createMock(UserReference::class),
                ]
            )
            ->getMock();
    }
}
