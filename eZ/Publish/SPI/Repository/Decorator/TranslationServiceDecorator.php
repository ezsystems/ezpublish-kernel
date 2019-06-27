<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\TranslationService;
use eZ\Publish\API\Repository\Values\Translation;

abstract class TranslationServiceDecorator implements TranslationService
{
    /** @var \eZ\Publish\API\Repository\TranslationService */
    protected $innerService;

    public function __construct(TranslationService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function translate(
        Translation $translation,
        $locale
    ) {
        return $this->innerService->translate($translation, $locale);
    }

    public function translateString(
        $translation,
        $locale
    ) {
        return $this->innerService->translateString($translation, $locale);
    }
}
