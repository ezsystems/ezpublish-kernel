<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

use eZ\Publish\API\Repository\Events\Language\DisableLanguageEvent as DisableLanguageEventInterface;
use eZ\Publish\API\Repository\Values\Content\Language;
use Symfony\Contracts\EventDispatcher\Event;

final class DisableLanguageEvent extends Event implements DisableLanguageEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Language */
    private $disabledLanguage;

    /** @var \eZ\Publish\API\Repository\Values\Content\Language */
    private $language;

    public function __construct(
        Language $disabledLanguage,
        Language $language
    ) {
        $this->disabledLanguage = $disabledLanguage;
        $this->language = $language;
    }

    public function getDisabledLanguage(): Language
    {
        return $this->disabledLanguage;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }
}
