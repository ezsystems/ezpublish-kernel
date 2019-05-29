<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCreateLanguageEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.language.create.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    private $languageCreateStruct;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Language|null
     */
    private $language;

    public function __construct(LanguageCreateStruct $languageCreateStruct)
    {
        $this->languageCreateStruct = $languageCreateStruct;
    }

    public function getLanguageCreateStruct(): LanguageCreateStruct
    {
        return $this->languageCreateStruct;
    }

    public function getLanguage(): ?Language
    {
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
