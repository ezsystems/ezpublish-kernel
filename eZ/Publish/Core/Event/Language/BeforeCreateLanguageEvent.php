<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

use eZ\Publish\API\Repository\Events\Language\BeforeCreateLanguageEvent as BeforeCreateLanguageEventInterface;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeCreateLanguageEvent extends BeforeEvent implements BeforeCreateLanguageEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct */
    private $languageCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\Content\Language|null */
    private $language;

    public function __construct(LanguageCreateStruct $languageCreateStruct)
    {
        $this->languageCreateStruct = $languageCreateStruct;
    }

    public function getLanguageCreateStruct(): LanguageCreateStruct
    {
        return $this->languageCreateStruct;
    }

    public function getLanguage(): Language
    {
        if (!$this->hasLanguage()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasLanguage() or set it by setLanguage() before you call getter.', Language::class));
        }

        return $this->language;
    }

    public function setLanguage(?Language $language): void
    {
        $this->language = $language;
    }

    public function hasLanguage(): bool
    {
        return $this->language instanceof Language;
    }
}
