<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\TranslationService;
use eZ\Publish\API\Repository\Values\Translation;

abstract class TranslationServiceDecorator implements TranslationService
{
    /** @var eZ\Publish\API\Repository\TranslationService */
    protected $innerService;

    /**
     * @param eZ\Publish\API\Repository\TranslationService
     */
    public function __construct(TranslationService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function translate(Translation $translation, $locale)
    {
        $this->innerService->translate($translation, $locale);
    }

    public function translateString($translation, $locale)
    {
        $this->innerService->translateString($translation, $locale);
    }
}
