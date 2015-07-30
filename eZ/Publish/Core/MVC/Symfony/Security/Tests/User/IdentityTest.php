<?php

/**
 * File containing the IdentityTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\User;

use eZ\Publish\Core\MVC\Symfony\Security\User\Identity;

class IdentityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::addInformation
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::getInformation
     */
    public function testAddInformation()
    {
        $identity = new Identity();
        $this->assertSame(array(), $identity->getInformation());

        $additionalInfo = array(
            'foo' => 'bar',
            'truc' => 'muche',
            'number' => 123,
        );

        $identity->addInformation($additionalInfo);
        $this->assertSame($additionalInfo, $identity->getInformation());

        $moreInfo = array(
            'another' => 'one',
            'foot' => 'print',
        );

        $identity->addInformation($moreInfo);
        $this->assertEquals($additionalInfo + $moreInfo, $identity->getInformation());
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::replaceInformation
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::getInformation
     */
    public function testReplaceInformation()
    {
        $identity = new Identity();
        $this->assertSame(array(), $identity->getInformation());

        $additionalInfo = array(
            'foo' => 'bar',
            'truc' => 'muche',
            'number' => 123,
        );

        $identity->replaceInformation($additionalInfo);
        $this->assertSame($additionalInfo, $identity->getInformation());

        $moreInfo = array(
            'another' => 'one',
            'foot' => 'print',
        );

        $identity->replaceInformation($moreInfo);
        $this->assertEquals($moreInfo, $identity->getInformation());
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::setInformation
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::getInformation
     */
    public function testSetInformation()
    {
        $identity = new Identity();
        $this->assertSame(array(), $identity->getInformation());
        $info = array(
            'foo' => 'bar',
            'truc' => 'muche',
            'number' => 123,
        );

        foreach ($info as $name => $value) {
            $identity->setInformation($name, $value);
        }

        $this->assertSame($info, $identity->getInformation());
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::setInformation
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\Identity::getHash
     */
    public function testGetHash()
    {
        $identity = new Identity();
        $identity->setInformation('foo', 'bar');
        $hash1 = $identity->getHash();
        $this->assertInternalType('string', $hash1);

        $identity->setInformation('truc', 'muche');
        $hash2 = $identity->getHash();
        $this->assertInternalType('string', $hash2);
        $this->assertTrue($hash1 !== $hash2);

        $identity->setInformation('number', 123);
        $hash3 = $identity->getHash();
        $this->assertInternalType('string', $hash3);
        $this->assertTrue($hash3 !== $hash1);
        $this->assertTrue($hash3 !== $hash2);

        $identity->replaceInformation(array('foo' => 'bar'));
        $this->assertSame($hash1, $identity->getHash());
    }
}
