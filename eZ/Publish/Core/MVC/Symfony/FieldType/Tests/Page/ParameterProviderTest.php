<?php

/**
 * File containing the ParameterProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\Page;

use eZ\Publish\Core\MVC\Symfony\FieldType\Page\ParameterProvider;
use PHPUnit\Framework\TestCase;

class ParameterProviderTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\FieldType\Page\ParameterProvider::getViewParameters
     */
    public function testGetViewParameters()
    {
        $pageService = $this
            ->getMockBuilder('eZ\\Publish\\Core\\FieldType\\Page\\PageService')
            ->disableOriginalConstructor()
            ->getMock();
        $field = $this->getMock('eZ\\Publish\\API\\Repository\\Values\\Content\\Field');
        $parameterProvider = new ParameterProvider($pageService);
        $this->assertSame(
            array('pageService' => $pageService),
            $parameterProvider->getViewParameters($field)
        );
    }
}
