<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Events\ContentType\DeleteContentTypeEvent as DeleteContentTypeEventInterface;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class DeleteContentTypeEvent extends AfterEvent implements DeleteContentTypeEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType */
    private $contentType;

    public function __construct(ContentType $contentType)
    {
        $this->contentType = $contentType;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }
}
