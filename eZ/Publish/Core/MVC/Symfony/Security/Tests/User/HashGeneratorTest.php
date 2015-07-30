<?php

/**
 * File containing the HashGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\User;

use eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator;

class HashGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::setIdentityDefiner
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::getIdentityDefiners
     */
    public function testSetIdentityDefiner()
    {
        $hashGenerator = new HashGenerator();
        $identityDefiners = array(
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
        );

        foreach ($identityDefiners as $definer) {
            $hashGenerator->setIdentityDefiner($definer);
        }

        $this->assertSame($identityDefiners, $hashGenerator->getIdentityDefiners());
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::setIdentity
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::getIdentity
     */
    public function testSetIdentity()
    {
        $hashGenerator = new HashGenerator();
        $identity = $this->getMock('eZ\\Publish\\SPI\\User\\Identity');
        $hashGenerator->setIdentity($identity);
        $this->assertSame($identity, $hashGenerator->getIdentity());
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::setIdentity
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::getIdentity
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::setIdentityDefiner
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::getIdentityDefiners
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator::generate
     */
    public function testGenerate()
    {
        $hashGenerator = new HashGenerator();
        $identity = $this->getMock('eZ\\Publish\\SPI\\User\\Identity');
        $hashGenerator->setIdentity($identity);
        $identityDefiners = array(
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
            $this->getMock('eZ\\Publish\\SPI\\User\\IdentityAware'),
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
