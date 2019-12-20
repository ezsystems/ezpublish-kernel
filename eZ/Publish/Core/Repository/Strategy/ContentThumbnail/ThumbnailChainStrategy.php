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

final class ThumbnailChainStrategy implements ThumbnailStrategy
{
    /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy[] */
    private $strategies;

    /**
     * @param \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy[] $strategies
     */
    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    public function getThumbnail(ContentType $contentType, array $fields): ?Thumbnail
    {
        foreach ($this->strategies as $strategy) {
            $thumbnail = $strategy->getThumbnail($contentType, $fields);

            if ($thumbnail !== null) {
                return $thumbnail;
            }
        }

        return null;
    }
}
