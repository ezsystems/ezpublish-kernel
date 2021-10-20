<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Core\Persistence\Cache;

use Ibexa\Core\Persistence\Cache\LocationPathConverter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class LocationPathConverterTest extends TestCase
{
    /** @var \Ibexa\Core\Persistence\Cache\LocationPathConverter */
    private $locationPathConverter;

    public function setUp(): void
    {
        $this->locationPathConverter = new LocationPathConverter();
    }

    public function providerForTestConvertToPathIds(): array
    {
        return [
            [[''], []],
            [['/1/'], []],
            [['/1/2/3/4/'], [2, 3, 4]],
            [['1/2/3/4'], [2, 3, 4]],
        ];
    }

    /**
     * @dataProvider providerForTestConvertToPathIds
     */
    public function testConvertToPathIds(array $arguments, array $resultArray): void
    {
        $this->assertEquals(
            $resultArray,
            $this->locationPathConverter->convertToPathIds(...$arguments)
        );
    }
}
