<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Strategy\ContentThumbnail;

use eZ\Publish\API\Repository\Values\Content\Thumbnail;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;

final class StaticStrategy implements ThumbnailStrategy
{
    /** @var string */
    private $staticThumbnail;

    public function __construct(string $staticThumbnail)
    {
        $this->staticThumbnail = $staticThumbnail;
    }

    public function getThumbnail(ContentType $contentType, array $fields): Thumbnail
    {
        return new Thumbnail([
            'resource' => $this->staticThumbnail,
        ]);
    }
}
