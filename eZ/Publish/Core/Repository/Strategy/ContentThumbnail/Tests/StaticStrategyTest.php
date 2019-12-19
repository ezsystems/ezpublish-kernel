<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Strategy\ContentThumbnail\Tests;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Thumbnail;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Strategy\ContentThumbnail\StaticStrategy;
use PHPUnit\Framework\TestCase;

class StaticStrategyTest extends TestCase
{
    public function testStaticStrategy()
    {
        $resource = 'static-test-resource';

        $staticStrategy = new StaticStrategy($resource);

        $contentTypeMock = $this->createMock(ContentType::class);
        $fieldMocks = [
            $this->createMock(Field::class),
            $this->createMock(Field::class),
            $this->createMock(Field::class),
        ];

        $result = $staticStrategy->getThumbnail(
            $contentTypeMock,
            $fieldMocks,
        );

        $this->assertEquals(
            new Thumbnail([
                'resource' => $resource
            ]),
            $result
        );
    }
}
