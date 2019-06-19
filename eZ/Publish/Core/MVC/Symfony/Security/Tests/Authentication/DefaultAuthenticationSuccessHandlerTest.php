<?php

/**
 * File containing the DefaultAuthenticationSuccessHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Authentication;

use eZ\Publish\Core\MVC\Symfony\Security\Authentication\DefaultAuthenticationSuccessHandler;
use eZ\Publish\Core\MVC\Symfony\Security\HttpUtils;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class DefaultAuthenticationSuccessHandlerTest extends TestCase
{
    public function testSetConfigResolver()
    {
        $successHandler = new DefaultAuthenticationSuccessHandler(new HttpUtils(), []);
        $refHandler = new ReflectionObject($successHandler);
        $refOptions = $refHandler->getProperty('options');
        $refOptions->setAccessible(true);
        $options = $refOptions->getValue($successHandler);
        $this->assertSame('/', $options['default_target_path']);

        $defaultPage = '/foo/bar';
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('default_page')
            ->will($this->returnValue($defaultPage));
        $successHandler->setConfigResolver($configResolver);
        $options = $refOptions->getValue($successHandler);
        $this->assertSame($defaultPage, $options['default_target_path']);
    }
}
