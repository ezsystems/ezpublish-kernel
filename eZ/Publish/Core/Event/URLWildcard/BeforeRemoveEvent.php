<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLWildcard;

use eZ\Publish\API\Repository\Events\URLWildcard\BeforeRemoveEvent as BeforeRemoveEventInterface;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use Symfony\Contracts\EventDispatcher\Event;

final class BeforeRemoveEvent extends Event implements BeforeRemoveEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\URLWildcard */
    private $urlWildcard;

    public function __construct(URLWildcard $urlWildcard)
    {
        $this->urlWildcard = $urlWildcard;
    }

    public function getUrlWildcard(): URLWildcard
    {
        return $this->urlWildcard;
    }
}
