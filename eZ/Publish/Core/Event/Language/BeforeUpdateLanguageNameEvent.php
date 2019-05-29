<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeUpdateLanguageNameEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.language.name_update.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Language
     */
    private $language;

    /**
     * @var string
     */
    private $newName;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Language|null
     */
    private $updatedLanguage;

    public function __construct(Language $language, string $newName)
    {
        $this->language = $language;
        $this->newName = $newName;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getNewName(): string
    {
        return $this->newName;
    }

    public function getUpdatedLanguage(): ?Language
    {
        return $this->updatedLanguage;
    }

    public function setUpdatedLanguage(?Language $updatedLanguage): void
    {
        $this->updatedLanguage = $updatedLanguage;
    }

    public function hasUpdatedLanguage(): bool
    {
        return $this->updatedLanguage instanceof Language;
    }
}
