<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URL;

use eZ\Publish\API\Repository\Events\URL\UpdateUrlEvent as UpdateUrlEventInterface;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdateUrlEvent extends AfterEvent implements UpdateUrlEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\URL\URL */
    private $url;

    /** @var \eZ\Publish\API\Repository\Values\URL\URLUpdateStruct */
    private $struct;

    /** @var \eZ\Publish\API\Repository\Values\URL\URL */
    private $updatedUrl;

    public function __construct(
        URL $updatedUrl,
        URL $url,
        URLUpdateStruct $struct
    ) {
        $this->url = $url;
        $this->struct = $struct;
        $this->updatedUrl = $updatedUrl;
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
        return $this->updatedUrl;
    }
}
