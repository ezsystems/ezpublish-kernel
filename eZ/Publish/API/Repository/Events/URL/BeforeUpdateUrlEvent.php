<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\URL;

use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeUpdateUrlEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\URL\URL */
    private $url;

    /** @var \eZ\Publish\API\Repository\Values\URL\URLUpdateStruct */
    private $struct;

    /** @var \eZ\Publish\API\Repository\Values\URL\URL|null */
    private $updatedUrl;

    public function __construct(URL $url, URLUpdateStruct $struct)
    {
        $this->url = $url;
        $this->struct = $struct;
    }

    public function getUrl(): URL
    {
        return $this->url;
    }

    public function getStruct(): URLUpdateStruct
    {
        return $this->struct;
    }

    public function getUpdatedUrl(): URL
    {
        if (!$this->hasUpdatedUrl()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedUrl() or set it by setUpdatedUrl() before you call getter.', URL::class));
        }

        return $this->updatedUrl;
    }

    public function setUpdatedUrl(?URL $updatedUrl): void
    {
        $this->updatedUrl = $updatedUrl;
    }

    public function hasUpdatedUrl(): bool
    {
        return $this->updatedUrl instanceof URL;
    }
}
