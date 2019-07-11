<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Events\Content\BeforeRevealContentEvent as BeforeRevealContentEventInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Symfony\Contracts\EventDispatcher\Event;

final class BeforeRevealContentEvent extends Event implements BeforeRevealContentEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $contentInfo;

    public function __construct(ContentInfo $contentInfo)
    {
        $this->contentInfo = $contentInfo;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }
}
