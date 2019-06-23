<?php

/**
 * File containing the BaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased;

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
    protected function getPartiallyMockedViewProvider(array $matchingConfig = [])
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Location\\Configured')
            ->setConstructorArgs(
                [
                    $this->repositoryMock,
                    $matchingConfig,
                ]
            )
            ->setMethods(['getMatcher'])
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        $repositoryClass = 'eZ\\Publish\\Core\\Repository\\Repository';

        return $this
            ->getMockBuilder($repositoryClass)
            ->disableOriginalConstructor()
            ->setMethods(
                array_diff(
                    get_class_methods($repositoryClass),
                    ['sudo']
                )
            )
            ->getMock();
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLocationMock(array $properties = [])
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Location')
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentInfoMock(array $properties = [])
    {
        return $this->
            getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo')
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
    }

    protected function getPermissionResolverMock()
    {
        return $this
            ->getMockBuilder('\eZ\Publish\Core\Repository\Permission\PermissionResolver')
            ->setMethods(null)
            ->setConstructorArgs(
                [
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\RoleDomainMapper')
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\LimitationService')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\SPI\Persistence\User\Handler')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\API\Repository\Values\User\UserReference')
                        ->getMock(),
                ]
            )
            ->getMock();
    }
}
