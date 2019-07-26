<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Events\Content\BeforeDeleteContentEvent as BeforeDeleteContentEventInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeDeleteContentEvent extends BeforeEvent implements BeforeDeleteContentEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $contentInfo;

    /** @var array|null */
    private $locations;

    public function __construct(ContentInfo $contentInfo)
    {
        $this->contentInfo = $contentInfo;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getLocations(): array
    {
        if (!$this->hasLocations()) {
            throw new UnexpectedValueException('You must set proper event return value of array type by setLocations() method if you use stopPropagation()');
        }

        return $this->locations;
    }

    public function setLocations(?array $locations): void
    {
        $this->locations = $locations;
    }

    public function hasLocations(): bool
    {
        return is_array($this->locations);
    }
}
