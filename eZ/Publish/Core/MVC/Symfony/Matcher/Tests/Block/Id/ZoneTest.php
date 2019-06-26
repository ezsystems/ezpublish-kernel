<?php

/**
 * File containing the ZoneTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\Block\Id;

use eZ\Publish\Core\MVC\Symfony\Matcher\Block\Id\Zone as ZoneIdMatcher;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use PHPUnit\Framework\TestCase;

class ZoneTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\Block\MatcherInterface */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ZoneIdMatcher();
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
        return [
            [
                123,
                $this->generateBlockForZoneId(123),
                true,
            ],
            [
                123,
                $this->generateBlockForZoneId(456),
                false,
            ],
            [
                [123, 789],
                $this->generateBlockForZoneId(456),
                false,
            ],
            [
                [123, 789],
                $this->generateBlockForZoneId(789),
                true,
            ],
        ];
    }

    /**
     * @param $id
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    private function generateBlockForZoneId($id)
    {
        return new Block(
            ['zoneId' => $id]
        );
    }
}
