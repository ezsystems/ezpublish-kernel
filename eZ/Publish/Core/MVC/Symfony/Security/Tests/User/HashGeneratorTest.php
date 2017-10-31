<?php

/**
 * File containing the HashGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\User;

use eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator;
use eZ\Publish\SPI\User\Identity;
use eZ\Publish\SPI\User\IdentityAware;
use PHPUnit\Framework\TestCase;

class HashGeneratorTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::setIdentityDefiner
     * @covers \eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::getIdentityDefiners
     */
    public function testSetIdentityDefiner()
    {
        $hashGenerator = new HashGenerator();
        $identityDefiners = array(
            $this->createMock(IdentityAware::class),
            $this->createMock(IdentityAware::class),
            $this->createMock(IdentityAware::class),
            $this->createMock(IdentityAware::class),
            $this->createMock(IdentityAware::class),
        );

        foreach ($identityDefiners as $definer) {
            $hashGenerator->setIdentityDefiner($definer);
        }

        $this->assertSame($identityDefiners, $hashGenerator->getIdentityDefiners());
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::setIdentity
     * @covers \eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::getIdentity
     */
    public function testSetIdentity()
    {
        $hashGenerator = new HashGenerator();
        $identity = $this->createMock(Identity::class);
        $hashGenerator->setIdentity($identity);
        $this->assertSame($identity, $hashGenerator->getIdentity());
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::setIdentity
     * @covers \eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::getIdentity
     * @covers \eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::setIdentityDefiner
     * @covers \eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::getIdentityDefiners
     * @covers \eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::generate
     */
    public function testGenerate()
    {
        $hashGenerator = new HashGenerator();
        $identity = $this->createMock(Identity::class);
        $hashGenerator->setIdentity($identity);
        $identityDefiners = array(
            $this->createMock(IdentityAware::class),
            $this->createMock(IdentityAware::class),
            $this->createMock(IdentityAware::class),
            $this->createMock(IdentityAware::class),
            $this->createMock(IdentityAware::class),
        );

        /** @var $definer \PHPUnit_Framework_MockObject_MockObject */
        foreach ($identityDefiners as $definer) {
            $hashGenerator->setIdentityDefiner($definer);
            $definer
                ->expects($this->once())
                ->method('setIdentity')
                ->with($identity);
        }

        $identity
            ->expects($this->once())
            ->method('getHash');

        $hashGenerator->generate();
    }
}
