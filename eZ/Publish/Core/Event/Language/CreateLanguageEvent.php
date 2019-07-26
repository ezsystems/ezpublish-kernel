<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

use eZ\Publish\API\Repository\Events\Language\CreateLanguageEvent as CreateLanguageEventInterface;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class CreateLanguageEvent extends AfterEvent implements CreateLanguageEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Language */
    private $language;

    /** @var \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct */
    private $languageCreateStruct;

    public function __construct(
        Language $language,
        LanguageCreateStruct $languageCreateStruct
    ) {
        $this->language = $language;
        $this->languageCreateStruct = $languageCreateStruct;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getLanguageCreateStruct(): LanguageCreateStruct
    {
        return $this->languageCreateStruct;
    }
}
