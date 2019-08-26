<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\View\Tests;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\MVC\Symfony\View\View;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

abstract class AbstractViewTest extends TestCase
{
    abstract protected function createViewUnderTest($template = null, array $parameters = [], $viewType = 'full'): View;

    /**
     * Returns parameters that are always returned by this view.
     *
     * @return array
     */
    protected function getAlwaysAvailableParams(): array
    {
        return [];
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\View::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\View::getParameters
     */
    public function testGetSetParameters(): void
    {
        $params = [
            'bar' => 'baz',
            'fruit' => 'apple',
        ];

        $view = $this->createViewUnderTest('foo');
        $view->setParameters($params);

        self::assertSame($this->getAlwaysAvailableParams() + $params, $view->getParameters());
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\View::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\View::getParameters
     */
    public function testAddParameters(): void
    {
        $params = ['bar' => 'baz', 'fruit' => 'apple'];
        $view = $this->createViewUnderTest('foo', $params);

        $additionalParams = ['truc' => 'muche', 'laurel' => 'hardy'];
        $view->addParameters($additionalParams);

        $this->assertSame($this->getAlwaysAvailableParams() + $params + $additionalParams, $view->getParameters());
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\View::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\View::getParameters
     */
    public function testHasParameter(): View
    {
        $view = $this->createViewUnderTest(__METHOD__, ['foo' => 'bar']);

        $this->assertTrue($view->hasParameter('foo'));
        $this->assertFalse($view->hasParameter('nonExistent'));

        return $view;
    }

    /**
     * @depends testHasParameter
     * @covers  \eZ\Publish\Core\MVC\Symfony\View\View::setParameters
     * @covers  \eZ\Publish\Core\MVC\Symfony\View\View::getParameters
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     */
    public function testGetParameter(View $view): View
    {
        $this->assertSame('bar', $view->getParameter('foo'));

        return $view;
    }

    /**
     * @depends testGetParameter
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\View\View::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\View::getParameters
     */
    public function testGetParameterFail(View $view): void
    {
        $this->expectException(InvalidArgumentException::class);

        $view->getParameter('nonExistent');
    }

    /**
     * @dataProvider goodTemplateIdentifierProvider
     *
     * @param string|callable $templateIdentifier
     */
    public function testSetTemplateIdentifier($templateIdentifier): void
    {
        $contentView = $this->createViewUnderTest();
        $contentView->setTemplateIdentifier($templateIdentifier);

        $this->assertSame($templateIdentifier, $contentView->getTemplateIdentifier());
    }

    public function goodTemplateIdentifierProvider(): array
    {
        return [
            ['foo:bar:baz.html.twig'],
            [
                function () {
                    return 'foo';
                },
            ],
        ];
    }

    /**
     * @dataProvider badTemplateIdentifierProvider
     *
     * @param mixed $badTemplateIdentifier
     */
    public function testSetTemplateIdentifierWrongType($badTemplateIdentifier): void
    {
        $this->expectException(InvalidArgumentType::class);

        $contentView = $this->createViewUnderTest();
        $contentView->setTemplateIdentifier($badTemplateIdentifier);
    }

    public function badTemplateIdentifierProvider(): array
    {
        return [
            [123],
            [true],
            [new \stdClass()],
            [['foo', 'bar']],
        ];
    }
}
