<?php

/**
 * File containing the ParameterProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\Page;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\FieldType\Page\ParameterProvider;
use eZ\Publish\Core\FieldType\Page\PageService;
use PHPUnit\Framework\TestCase;

class ParameterProviderTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\FieldType\Page\ParameterProvider::getViewParameters
     */
    public function testGetViewParameters()
    {
        $pageService = $this->createMock(PageService::class);
        $field = $this->createMock(Field::class);
        $parameterProvider = new ParameterProvider($pageService);
        $this->assertSame(
            ['pageService' => $pageService],
            $parameterProvider->getViewParameters($field)
        );
    }
}
