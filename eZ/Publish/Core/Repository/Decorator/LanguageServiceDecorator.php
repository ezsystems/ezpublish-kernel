<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;

abstract class LanguageServiceDecorator implements LanguageService
{
    /**
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\LanguageService $service
     */
    public function __construct(LanguageService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function createLanguage(LanguageCreateStruct $languageCreateStruct)
    {
        return $this->service->createLanguage($languageCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function updateLanguageName(Language $language, $newName)
    {
        return $this->service->updateLanguageName($language, $newName);
    }

    /**
     * {@inheritdoc}
     */
    public function enableLanguage(Language $language)
    {
        return $this->service->enableLanguage($language);
    }

    /**
     * {@inheritdoc}
     */
    public function disableLanguage(Language $language)
    {
        return $this->service->disableLanguage($language);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLanguage($languageCode)
    {
        return $this->service->loadLanguage($languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLanguages()
    {
        return $this->service->loadLanguages();
    }

    /**
     * {@inheritdoc}
     */
    public function loadLanguageById($languageId)
    {
        return $this->service->loadLanguageById($languageId);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteLanguage(Language $language)
    {
        return $this->service->deleteLanguage($language);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLanguageCode()
    {
        return $this->service->getDefaultLanguageCode();
    }

    /**
     * {@inheritdoc}
     */
    public function newLanguageCreateStruct()
    {
        return $this->service->newLanguageCreateStruct();
    }
}
