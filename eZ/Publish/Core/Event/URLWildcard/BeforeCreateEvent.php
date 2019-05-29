<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLWildcard;

use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCreateEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.url_wildcard.create.before';

    private $sourceUrl;

    private $destinationUrl;

    private $forward;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\URLWildcard|null
     */
    private $urlWildcard;

    public function __construct($sourceUrl, $destinationUrl, $forward)
    {
        $this->sourceUrl = $sourceUrl;
        $this->destinationUrl = $destinationUrl;
        $this->forward = $forward;
    }

    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    public function getDestinationUrl()
    {
        return $this->destinationUrl;
    }

    public function getForward()
    {
        return $this->forward;
    }

    public function getUrlWildcard(): ?URLWildcard
    {
        return $this->urlWildcard;
    }

    public function setUrlWildcard(?URLWildcard $urlWildcard): void
    {
        $this->urlWildcard = $urlWildcard;
    }

    public function hasUrlWildcard(): bool
    {
        return $this->urlWildcard instanceof URLWildcard;
    }
}
