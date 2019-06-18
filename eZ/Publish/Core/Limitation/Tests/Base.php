<?php

/**
 * File containing the eZ\Publish\Core\Limitation\Tests\Base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation\Tests;

use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceHandlerMock;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userMock;

    /**
     * @param array $mockMethods For specifying the methods to mock, all by default
     *
     * @return \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getPersistenceMock(array $mockMethods = [])
    {
        if ($this->persistenceHandlerMock !== null) {
            return $this->persistenceHandlerMock;
        }

        return $this->persistenceHandlerMock = $this->getMock(
            'eZ\\Publish\\SPI\\Persistence\\Handler',
            $mockMethods,
            [],
            '',
            false
        );
    }

    /**
     * @param array $mockMethods For specifying the methods to mock, all by default
     *
     * @return \eZ\Publish\API\Repository\Values\User\User|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getUserMock(array $mockMethods = [])
    {
        if ($this->userMock !== null) {
            return $this->userMock;
        }

        return $this->userMock = $this->getMock(
            'eZ\\Publish\\API\\Repository\\Values\\User\\User',
            $mockMethods,
            [],
            '',
            false
        );
    }

    /**
     * unset properties.
     */
    public function tearDown()
    {
        if ($this->persistenceHandlerMock !== null) {
            unset($this->persistenceHandlerMock);
        }

        if ($this->userMock !== null) {
            unset($this->userMock);
        }

        parent::tearDown();
    }
}
