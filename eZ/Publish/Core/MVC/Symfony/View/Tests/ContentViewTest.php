<?php

/**
 * File containing the ContentViewTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Tests;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use PHPUnit\Framework\TestCase;

/**
 * @group mvc
 */
class ContentViewTest extends TestCase
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

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::getParameters
     */
    public function testGetSetParameters()
    {
        $params = ['bar' => 'baz', 'fruit' => 'apple'];
        $contentView = new ContentView('foo');
        $contentView->setParameters($params);
        self::assertSame($this->valueParams + $params, $contentView->getParameters());
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::getParameters
     */
    public function testAddParameters()
    {
        $params = ['bar' => 'baz', 'fruit' => 'apple'];
        $contentView = new ContentView('foo', $params);

        $additionalParams = ['truc' => 'muche', 'laurel' => 'hardy'];
        $contentView->addParameters($additionalParams);
        self::assertSame($this->valueParams + $params + $additionalParams, $contentView->getParameters());
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::getParameters
     */
    public function testHasParameter()
    {
        $contentView = new ContentView(__METHOD__, ['foo' => 'bar']);
        self::assertTrue($contentView->hasParameter('foo'));
        self::assertFalse($contentView->hasParameter('nonExistent'));

        return $contentView;
    }

    /**
     * @depends testHasParameter
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::getParameters
     */
    public function testGetParameter(ContentView $contentView)
    {
        self::assertSame('bar', $contentView->getParameter('foo'));

        return $contentView;
    }

    /**
     * @depends testGetParameter
     * @expectedException \InvalidArgumentException
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::getParameters
     */
    public function testGetParameterFail(ContentView $contentView)
    {
        $contentView->getParameter('nonExistent');
    }

    /**
     * @dataProvider goodTemplateIdentifierProvider
     *
     * @param $templateIdentifier
     */
    public function testSetTemplateIdentifier($templateIdentifier)
    {
        $contentView = new ContentView();
        $contentView->setTemplateIdentifier($templateIdentifier);
        $this->assertSame($templateIdentifier, $contentView->getTemplateIdentifier());
    }

    public function goodTemplateIdentifierProvider()
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
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     *
     * @param $badTemplateIdentifier
     */
    public function testSetTemplateIdentifierWrongType($badTemplateIdentifier)
    {
        $contentView = new ContentView();
        $contentView->setTemplateIdentifier($badTemplateIdentifier);
    }

    public function badTemplateIdentifierProvider()
    {
        return [
            [123],
            [true],
            [new \stdClass()],
            [['foo', 'bar']],
        ];
    }
}
