<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

use eZ\Publish\API\Repository\Events\Language\BeforeUpdateLanguageNameEvent as BeforeUpdateLanguageNameEventInterface;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeUpdateLanguageNameEvent extends BeforeEvent implements BeforeUpdateLanguageNameEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Language */
    private $language;

    /** @var string */
    private $newName;

    /** @var \eZ\Publish\API\Repository\Values\Content\Language|null */
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

    public function getUpdatedLanguage(): Language
    {
        if (!$this->hasUpdatedLanguage()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedLanguage() or set it by setUpdatedLanguage() before you call getter.', Language::class));
        }

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
