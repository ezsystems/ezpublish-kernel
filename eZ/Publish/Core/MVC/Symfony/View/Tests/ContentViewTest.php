<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\View\Tests;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\View;

/**
 * @group mvc
 */
class ContentViewTest extends AbstractViewTest
{
    /**
     * Params that are always returned by this view.
     * @var array
     */
    private $valueParams = ['content' => null];

    /**
     * @dataProvider constructProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::getTemplateIdentifier
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::getParameters
     */
    public function testConstruct($templateIdentifier, array $params)
    {
        $contentView = new ContentView($templateIdentifier, $params);
        self::assertSame($templateIdentifier, $contentView->getTemplateIdentifier());
        self::assertSame($this->valueParams + $params, $contentView->getParameters());
    }

    public function constructProvider()
    {
        return [
            ['some:valid:identifier', ['foo' => 'bar']],
            ['another::identifier', []],
            ['oops:i_did_it:again', ['singer' => 'Britney Spears']],
            [
                function () {
                    return true;
                },
                [],
            ],
            [
                function () {
                    return true;
                },
                ['truc' => 'muche'],
            ],
        ];
    }

    /**
     * @dataProvider constructFailProvider
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     */
    public function testConstructFail($templateIdentifier)
    {
        new ContentView($templateIdentifier);
    }

    public function constructFailProvider()
    {
        return [
            [123],
            [new \stdClass()],
            [[1, 2, 3]],
        ];
    }

    protected function createViewUnderTest($template = null, array $parameters = [], $viewType = 'full'): View
    {
        return new ContentView($template, $parameters, $viewType);
    }

    protected function getAlwaysAvailableParams(): array
    {
        return $this->valueParams;
    }
}
