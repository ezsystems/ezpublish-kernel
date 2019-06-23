<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\Limitation;

use eZ\Publish\Core\REST\Server\Input\Parser\Limitation\RouteBasedLimitationParser;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;

class RouteBasedLimitationParserTest extends BaseTest
{
    public function testParse()
    {
        $inputArray = [
            '_identifier' => 'Section',
            'values' => [
                'ref' => [
                    ['_href' => '/content/sections/42'],
                ],
            ],
        ];

        $result = $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf('stdClass', $result);
        self::assertObjectHasAttribute('limitationValues', $result);
        self::assertArrayHasKey(0, $result->limitationValues);
        self::assertEquals(42, $result->limitationValues[0]);
    }

    /**
     * Must return the tested parser object.
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Limitation\RouteBasedLimitationParser
     */
    protected function internalGetParser()
    {
        return new RouteBasedLimitationParser('sectionId', 'stdClass');
    }

    public function getParseHrefExpectationsMap()
    {
        return [
            ['/content/sections/42', 'sectionId', 42],
        ];
    }
}
