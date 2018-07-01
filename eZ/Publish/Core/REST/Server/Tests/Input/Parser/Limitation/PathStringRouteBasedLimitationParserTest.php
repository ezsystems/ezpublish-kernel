<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\Limitation;

use eZ\Publish\Core\REST\Server\Input\Parser\Limitation\PathStringRouteBasedLimitationParser;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;

class PathStringRouteBasedLimitationParserTest extends BaseTest
{
    public function testParse()
    {
        $inputArray = [
            '_identifier' => 'Subtree',
            'values' => [
                'ref' => [
                    ['_href' => '/content/locations/1/2/3/4/'],
                ],
            ],
        ];

        $result = $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf('stdClass', $result);
        self::assertObjectHasAttribute('limitationValues', $result);
        self::assertArrayHasKey(0, $result->limitationValues);
        self::assertEquals('/1/2/3/4/', $result->limitationValues[0]);
    }

    /**
     * Must return the tested parser object.
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Limitation\RouteBasedLimitationParser
     */
    protected function internalGetParser()
    {
        return new PathStringRouteBasedLimitationParser('pathString', 'stdClass');
    }

    public function getParseHrefExpectationsMap()
    {
        return array(
            array('/content/locations/1/2/3/4/', 'pathString', '1/2/3/4/'),
        );
    }
}
