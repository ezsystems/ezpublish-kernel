<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * SPI\Persistence Location with Content Info Value Object.
 * A composite of Location and ContentInfo instances.
 *
 * @property-read \eZ\Publish\SPI\Persistence\Content\Location $location
 * @property-read \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo
 */
class LocationWithContentInfo extends ValueObject
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Location */
    protected $location;

    /** @var \eZ\Publish\SPI\Persistence\Content\ContentInfo */
    protected $contentInfo;

    /**
     * @internal for internal use by Repository Storage abstraction
     */
    public function __construct(Location $location, ContentInfo $contentInfo)
    {
        parent::__construct([]);
        $this->location = $location;
        $this->contentInfo = $contentInfo;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }
}
