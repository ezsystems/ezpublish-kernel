<?php

/**
 * File containing the ParameterProviderRegistryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\View;

use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry;
use PHPUnit_Framework_TestCase;

class ParameterProviderRegistryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::setParameterProvider
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::hasParameterProvider
     */
    public function testSetHasParameterProvider()
    {
        $registry = new ParameterProviderRegistry();
        $this->assertFalse($registry->hasParameterProvider('foo'));
        $registry->setParameterProvider(
            $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\FieldType\\View\\ParameterProviderInterface'),
            'foo'
        );
        $this->assertTrue($registry->hasParameterProvider('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::getParameterProvider
     */
    public function testGetParameterProviderFail()
    {
        $registry = new ParameterProviderRegistry();
        $registry->getParameterProvider('foo');
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::setParameterProvider
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistry::getParameterProvider
     */
    public function testGetParameterProvider()
    {
        $provider = $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\FieldType\\View\\ParameterProviderInterface');
        $registry = new ParameterProviderRegistry();
        $registry->setParameterProvider($provider, 'foo');
        $this->assertSame($provider, $registry->getParameterProvider('foo'));
    }
}
