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
use eZ\Publish\Core\Repository\SiteAccessAware\Helper\DomainMapper;
use eZ\Publish\Core\Repository\SiteAccessAware\Helper\LanguageResolver;

/**
 * LanguageService class.
 */
class LanguageService implements LanguageServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $service;

    /**
     * Language resolver
     *
     * @var LanguageResolver
     */
    protected $languageResolver;

    /**
     * Rebuilds existing API domain objects to SiteAccessAware objects
     *
     * @var DomainMapper
     */
    protected $domainMapper;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service
     *
     * @param \eZ\Publish\API\Repository\LanguageService $service
     * @param LanguageResolver $languageResolver
     * @param DomainMapper $domainMapper
     */
    public function __construct(
        LanguageServiceInterface $service,
        LanguageResolver $languageResolver,
        DomainMapper $domainMapper
    ) {
        $this->service = $service;
        $this->languageResolver = $languageResolver;
        $this->domainMapper = $domainMapper;
    }

    /**
     * Creates the a new Language in the content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the languageCode already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct $languageCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function createLanguage(LanguageCreateStruct $languageCreateStruct)
    {
        return $this->service->createLanguage($languageCreateStruct);
    }

    /**
     * Changes the name of the language in the content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     * @param string $newName
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function updateLanguageName(Language $language, $newName)
    {
        return $this->service->updateLanguageName($language, $newName);
    }

    /**
     * Enables a language.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function enableLanguage(Language $language)
    {
        return $this->service->enableLanguage($language);
    }

    /**
     * Disables a language.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function disableLanguage(Language $language)
    {
        return $this->service->disableLanguage($language);
    }

    /**
     * Loads a Language from its language code ($languageCode).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if language could not be found
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function loadLanguage($languageCode)
    {
        return $this->service->loadLanguage($languageCode);
    }

    /**
     * Loads all Languages.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language[]
     */
    public function loadLanguages()
    {
        return $this->service->loadLanguages();
    }

    /**
     * Loads a Language by its id ($languageId).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if language could not be found
     *
     * @param mixed $languageId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function loadLanguageById($languageId)
    {
        return $this->service->loadLanguageById($languageId);
    }

    /**
     * Deletes  a language from content repository.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if language can not be deleted
     *         because it is still assigned to some content / type / (...).
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user is not allowed to delete a language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function deleteLanguage(Language $language)
    {
        return $this->service->deleteLanguage($language);
    }

    /**
     * Returns a configured default language code.
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        return $this->service->getDefaultLanguageCode();
    }

    /**
     * Instantiates an object to be used for creating languages.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    public function newLanguageCreateStruct()
    {
        return $this->service->newLanguageCreateStruct();
    }
}
