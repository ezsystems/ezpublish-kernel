<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;

abstract class LanguageServiceDecorator implements LanguageService
{
    /**
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $innerService;

    public function __construct(LanguageService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createLanguage(LanguageCreateStruct $languageCreateStruct)
    {
        $this->innerService->createLanguage($languageCreateStruct);
    }

    public function updateLanguageName(
        Language $language,
        $newName
    ) {
        $this->innerService->updateLanguageName($language, $newName);
    }

    public function enableLanguage(Language $language)
    {
        $this->innerService->enableLanguage($language);
    }

    public function disableLanguage(Language $language)
    {
        $this->innerService->disableLanguage($language);
    }

    public function loadLanguage($languageCode)
    {
        $this->innerService->loadLanguage($languageCode);
    }

    public function loadLanguages()
    {
        $this->innerService->loadLanguages();
    }

    public function loadLanguageById($languageId)
    {
        $this->innerService->loadLanguageById($languageId);
    }

    public function loadLanguageListByCode(array $languageCodes): iterable
    {
        return $this->innerService->loadLanguageListByCode($languageCodes);
    }

    public function loadLanguageListById(array $languageIds): iterable
    {
        return $this->innerService->loadLanguageListById($languageIds);
    }

    public function deleteLanguage(Language $language)
    {
        $this->innerService->deleteLanguage($language);
    }

    public function getDefaultLanguageCode()
    {
        $this->innerService->getDefaultLanguageCode();
    }

    public function newLanguageCreateStruct()
    {
        $this->innerService->newLanguageCreateStruct();
    }
}
