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
use Traversable;

final class ThumbnailChainStrategy implements ThumbnailStrategy
{
    /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy[]|Traversable */
    private $strategies;

    public function __construct(Traversable $strategies)
    {
        $this->strategies = $strategies;
    }

    public function getThumbnail(ContentType $contentType, array $fields): ?Thumbnail
    {
        /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy $strategy */
        foreach ($this->strategies as $strategy) {
            $thumbnail = $strategy->getThumbnail($contentType, $fields);

            if ($thumbnail !== null) {
                return $thumbnail;
            }
        }

        return null;
    }
}
