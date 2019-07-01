<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\Core\Event\AfterEvent;

final class RemoveContentTypeTranslationEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft */
    private $newContentTypeDraft;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft */
    private $contentTypeDraft;

    /** @var string */
    private $languageCode;

    public function __construct(
        ContentTypeDraft $newContentTypeDraft,
        ContentTypeDraft $contentTypeDraft,
        string $languageCode
    ) {
        $this->newContentTypeDraft = $newContentTypeDraft;
        $this->contentTypeDraft = $contentTypeDraft;
        $this->languageCode = $languageCode;
    }

    public function getNewContentTypeDraft(): ContentTypeDraft
    {
        return $this->newContentTypeDraft;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }
}
