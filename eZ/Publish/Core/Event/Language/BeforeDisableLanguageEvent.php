<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

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

    public function getDisabledLanguage(): Language
    {
        if (!$this->hasDisabledLanguage()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasDisabledLanguage() or set it by setDisabledLanguage() before you call getter.', Language::class));
        }

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
