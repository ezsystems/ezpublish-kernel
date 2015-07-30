<?php

/**
 * File containing the ViewTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\Block;

use eZ\Publish\Core\MVC\Symfony\Matcher\Block\View as BlockViewMatcher;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use PHPUnit_Framework_TestCase;

class ViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\Block\MatcherInterface
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new BlockViewMatcher();
    }

    /**
     * @dataProvider matchBlockProvider
     *
     * @param $matchingConfig
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param $expectedResult
     */
    public function testMatchBlock($matchingConfig, Block $block, $expectedResult)
    {
        $this->matcher->setMatchingConfig($matchingConfig);
        $this->assertSame($expectedResult, $this->matcher->matchBlock($block));
    }

    public function matchBlockProvider()
    {
        $data = array();

        $data[] = array(
            'foo',
            $this->generateBlockForView('foo'),
            true,
        );

        $data[] = array(
            'foo',
            $this->generateBlockForView('bar'),
            false,
        );

        $data[] = array(
            array('foo', 'baz'),
            $this->generateBlockForView('bar'),
            false,
        );

        $data[] = array(
            array('foo', 'baz'),
            $this->generateBlockForView('baz'),
            true,
        );

        return $data;
    }

    /**
     * @param $view
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    private function generateBlockForView($view)
    {
        return new Block(
            array('view' => $view)
        );
    }
}
