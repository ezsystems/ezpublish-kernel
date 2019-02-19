<?php

/**
 * LanguageService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;

/**
 * LanguageService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class LanguageService implements LanguageServiceInterface
{
    /** @var \eZ\Publish\API\Repository\LanguageService */
    protected $service;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\LanguageService $service
     */
    public function __construct(
        LanguageServiceInterface $service
    ) {
        $this->service = $service;
    }

    public function createLanguage(LanguageCreateStruct $languageCreateStruct)
    {
        return $this->service->createLanguage($languageCreateStruct);
    }

    public function updateLanguageName(Language $language, $newName)
    {
        return $this->service->updateLanguageName($language, $newName);
    }

    public function enableLanguage(Language $language)
    {
        return $this->service->enableLanguage($language);
    }

    public function disableLanguage(Language $language)
    {
        return $this->service->disableLanguage($language);
    }

    public function loadLanguage($languageCode)
    {
        return $this->service->loadLanguage($languageCode);
    }

    public function loadLanguages()
    {
        return $this->service->loadLanguages();
    }

    public function loadLanguageById($languageId)
    {
        return $this->service->loadLanguageById($languageId);
    }

    public function loadLanguageListByCode(array $languageCodes): iterable
    {
        return $this->service->loadLanguageListByCode($languageCodes);
    }

    public function loadLanguageListById(array $languageIds): iterable
    {
        return $this->service->loadLanguageListById($languageIds);
    }

    public function deleteLanguage(Language $language)
    {
        return $this->service->deleteLanguage($language);
    }

    public function getDefaultLanguageCode()
    {
        return $this->service->getDefaultLanguageCode();
    }

    public function newLanguageCreateStruct()
    {
        return $this->service->newLanguageCreateStruct();
    }
}
