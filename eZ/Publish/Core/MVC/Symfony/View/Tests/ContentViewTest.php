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
        return array(
            array('some:valid:identifier', array('foo' => 'bar')),
            array('another::identifier', array()),
            array('oops:i_did_it:again', array('singer' => 'Britney Spears')),
            array(
                function () {
                    return true;
                },
                array(),
            ),
            array(
                function () {
                    return true;
                },
                array('truc' => 'muche'),
            ),
        );
    }

    /**
     * @dataProvider constructFailProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     */
    public function testConstructFail($templateIdentifier)
    {
        $this->expectException(\eZ\Publish\Core\Base\Exceptions\InvalidArgumentType::class);

        new ContentView($templateIdentifier);
    }

    public function constructFailProvider()
    {
        return array(
            array(123),
            array(new \stdClass()),
            array(array(1, 2, 3)),
        );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::getParameters
     */
    public function testGetSetParameters()
    {
        $params = array('bar' => 'baz', 'fruit' => 'apple');
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
        $params = array('bar' => 'baz', 'fruit' => 'apple');
        $contentView = new ContentView('foo', $params);

        $additionalParams = array('truc' => 'muche', 'laurel' => 'hardy');
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
        $contentView = new ContentView(__METHOD__, array('foo' => 'bar'));
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
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::setParameters
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentView::getParameters
     */
    public function testGetParameterFail(ContentView $contentView)
    {
        $this->expectException(\InvalidArgumentException::class);

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
        return array(
            array('foo:bar:baz.html.twig'),
            array(
                function () {
                    return 'foo';
                },
            ),
        );
    }

    /**
     * @dataProvider badTemplateIdentifierProvider
     *
     *
     * @param $badTemplateIdentifier
     */
    public function testSetTemplateIdentifierWrongType($badTemplateIdentifier)
    {
        $this->expectException(\eZ\Publish\Core\Base\Exceptions\InvalidArgumentType::class);

        $contentView = new ContentView();
        $contentView->setTemplateIdentifier($badTemplateIdentifier);
    }

    public function badTemplateIdentifierProvider()
    {
        return array(
            array(123),
            array(true),
            array(new \stdClass()),
            array(array('foo', 'bar')),
        );
    }
}
