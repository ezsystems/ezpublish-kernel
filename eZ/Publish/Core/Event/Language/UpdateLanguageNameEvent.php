<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

use eZ\Publish\API\Repository\Events\Language\UpdateLanguageNameEvent as UpdateLanguageNameEventInterface;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdateLanguageNameEvent extends AfterEvent implements UpdateLanguageNameEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Language */
    private $updatedLanguage;

    /** @var \eZ\Publish\API\Repository\Values\Content\Language */
    private $language;

    /** @var string */
    private $newName;

    public function __construct(
        Language $updatedLanguage,
        Language $language,
        string $newName
    ) {
        $this->updatedLanguage = $updatedLanguage;
        $this->language = $language;
        $this->newName = $newName;
    }

    public function getUpdatedLanguage(): Language
    {
        return $this->updatedLanguage;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getNewName(): string
    {
        return $this->newName;
    }
}
