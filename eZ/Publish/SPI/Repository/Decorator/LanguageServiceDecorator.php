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
    /** @var \eZ\Publish\API\Repository\LanguageService */
    protected $innerService;

    public function __construct(LanguageService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createLanguage(LanguageCreateStruct $languageCreateStruct): Language
    {
        return $this->innerService->createLanguage($languageCreateStruct);
    }

    public function updateLanguageName(
        Language $language,
        string $newName
    ): Language {
        return $this->innerService->updateLanguageName($language, $newName);
    }

    public function enableLanguage(Language $language): Language
    {
        return $this->innerService->enableLanguage($language);
    }

    public function disableLanguage(Language $language): Language
    {
        return $this->innerService->disableLanguage($language);
    }

    public function loadLanguage(string $languageCode): Language
    {
        return $this->innerService->loadLanguage($languageCode);
    }

    public function loadLanguages(): iterable
    {
        return $this->innerService->loadLanguages();
    }

    public function loadLanguageById(int $languageId): Language
    {
        return $this->innerService->loadLanguageById($languageId);
    }

    public function loadLanguageListByCode(array $languageCodes): iterable
    {
        return $this->innerService->loadLanguageListByCode($languageCodes);
    }

    public function loadLanguageListById(array $languageIds): iterable
    {
        return $this->innerService->loadLanguageListById($languageIds);
    }

    public function deleteLanguage(Language $language): void
    {
        $this->innerService->deleteLanguage($language);
    }

    public function getDefaultLanguageCode(): string
    {
        return $this->innerService->getDefaultLanguageCode();
    }

    public function newLanguageCreateStruct(): LanguageCreateStruct
    {
        return $this->innerService->newLanguageCreateStruct();
    }
}
