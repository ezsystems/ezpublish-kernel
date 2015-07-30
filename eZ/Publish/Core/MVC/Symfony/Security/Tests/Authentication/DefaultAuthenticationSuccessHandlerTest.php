<?php

/**
 * File containing the DefaultAuthenticationSuccessHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Authentication;

use eZ\Publish\Core\MVC\Symfony\Security\Authentication\DefaultAuthenticationSuccessHandler;
use eZ\Publish\Core\MVC\Symfony\Security\HttpUtils;
use PHPUnit_Framework_TestCase;
use ReflectionObject;

class DefaultAuthenticationSuccessHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testSetConfigResolver()
    {
        $successHandler = new DefaultAuthenticationSuccessHandler(new HttpUtils(), array());
        $refHandler = new ReflectionObject($successHandler);
        $refOptions = $refHandler->getProperty('options');
        $refOptions->setAccessible(true);
        $options = $refOptions->getValue($successHandler);
        $this->assertSame('/', $options['default_target_path']);

        $defaultPage = '/foo/bar';
        $configResolver = $this->getMock('eZ\Publish\Core\MVC\ConfigResolverInterface');
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
