<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeDisableLanguageEvent extends BeforeEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Language
     */
    private $language;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Language|null
     */
    private $disabledLanguage;

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getDisabledLanguage(): ?Language
    {
        return $this->disabledLanguage;
    }

    public function setDisabledLanguage(?Language $disabledLanguage): void
    {
        $this->disabledLanguage = $disabledLanguage;
    }

    public function hasDisabledLanguage(): bool
    {
        return $this->disabledLanguage instanceof Language;
    }
}
