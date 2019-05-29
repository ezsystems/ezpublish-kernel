<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeDeleteTranslationFromDraftEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.translation.delete_from_draft.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    private $versionInfo;

    private $languageCode;

    public function __construct(VersionInfo $versionInfo, $languageCode)
    {
        $this->versionInfo = $versionInfo;
        $this->languageCode = $languageCode;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    public function getLanguageCode()
    {
        return $this->languageCode;
    }
}
