<?php

/**
 * File containing the ContentHelper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Helper class for translation.
 */
class TranslationHelper
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    /** @var array */
    private $siteAccessesByLanguage;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(ConfigResolverInterface $configResolver, ContentService $contentService, array $siteAccessesByLanguage, LoggerInterface $logger = null)
    {
        $this->configResolver = $configResolver;
        $this->contentService = $contentService;
        $this->siteAccessesByLanguage = $siteAccessesByLanguage;
        $this->logger = $logger;
    }

    /**
     * Returns content name, translated.
     * By default this method uses prioritized languages, unless $forcedLanguage is provided.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @return string
     */
    public function getTranslatedContentName(Content $content, $forcedLanguage = null)
    {
        return $this->getTranslatedContentNameByVersionInfo(
            $content->getVersionInfo(),
            $forcedLanguage
        );
    }

    /**
     * Returns content name, translated, from a VersionInfo object.
     * By default this method uses prioritized languages, unless $forcedLanguage is provided.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $forcedLanguage
     *
     * @return string
     */
    private function getTranslatedContentNameByVersionInfo(VersionInfo $versionInfo, $forcedLanguage = null)
    {
        foreach ($this->getLanguages($forcedLanguage) as $lang) {
            $translatedName = $versionInfo->getName($lang);
            if ($translatedName !== null) {
                return $translatedName;
            }
        }

        return '';
    }

    /**
     * Returns content name, translated, from a ContentInfo object.
     * By default this method uses prioritized languages, unless $forcedLanguage is provided.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @todo Remove ContentService usage when translated names are available in ContentInfo (see https://jira.ez.no/browse/EZP-21755)
     *
     * @return string
     */
    public function getTranslatedContentNameByContentInfo(ContentInfo $contentInfo, $forcedLanguage = null)
    {
        if (isset($forcedLanguage) && $forcedLanguage === $contentInfo->mainLanguageCode) {
            return $contentInfo->name;
        }

        return $this->getTranslatedContentNameByVersionInfo(
            $this->contentService->loadVersionInfo($contentInfo),
            $forcedLanguage
        );
    }

    /**
     * Returns Field object in the appropriate language for a given content.
     * By default, this method will return the field in current language if translation is present. If not, main language will be used.
     * If $forcedLanguage is provided, will return the field in this language, if translation is present.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefIdentifier Field definition identifier.
     * @param string $forcedLanguage Locale we want the field translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field|null
     */
    public function getTranslatedField(Content $content, $fieldDefIdentifier, $forcedLanguage = null)
    {
        // Loop over prioritized languages to get the appropriate translated field.
        foreach ($this->getLanguages($forcedLanguage) as $lang) {
            $field = $content->getField($fieldDefIdentifier, $lang);
            if ($field instanceof Field) {
                return $field;
            }
        }
    }

    /**
     * Returns Field definition name in the appropriate language for a given content.
     *
     * By default, this method will return the field definition name in current language if translation is present. If not, main language will be used.
     * If $forcedLanguage is provided, will return the field definition name in this language, if translation is present.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $fieldDefIdentifier Field Definition identifier
     * @param string $property Specifies if 'name' or 'description' should be used
     * @param string $forcedLanguage Locale we want the field definition name translated in in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws InvalidArgumentException
     *
     * @return string|null
     */
    public function getTranslatedFieldDefinitionProperty(
        ContentType $contentType,
        $fieldDefIdentifier,
        $property = 'name',
        $forcedLanguage = null
    ) {
        $fieldDefinition = $contentType->getFieldDefinition($fieldDefIdentifier);
        if (!$fieldDefinition instanceof FieldDefinition) {
            throw new InvalidArgumentException(
                '$fieldDefIdentifier',
                "Field '{$fieldDefIdentifier}' not found on {$contentType->identifier}"
            );
        }

        $method = 'get' . $property;
        if (!method_exists($fieldDefinition, $method)) {
            throw new InvalidArgumentException('$property', "Method get'{$property}'() not found on FieldDefinition");
        }

        // Loop over prioritized languages to get the appropriate translated field definition name
        // Should ideally have used array_unique, but in that case the loop should ideally never reach last item
        foreach ($this->getLanguages($forcedLanguage, $contentType->mainLanguageCode) as $lang) {
            if ($name = $fieldDefinition->$method($lang)) {
                return $name;
            }
        }
    }

    /**
     * Gets translated property generic helper.
     *
     * For generic use, expects array property as-is on value object, typically $object->$property[$language]
     *
     * Languages will consist of either forced language or current languages list, in addition helper will check if for
     * mainLanguage property and append that to languages if alwaysAvailable property is true or non-existing.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object  Can be any kid of Value object which directly holds the translated property
     * @param string $property Property name, example 'names', 'descriptions'
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws InvalidArgumentException
     *
     * @return string|null
     */
    public function getTranslatedByProperty(ValueObject $object, $property, $forcedLanguage = null)
    {
        if (!isset($object->$property)) {
            throw new InvalidArgumentException('$property', "Property '{$property}' not found on " . get_class($object));
        }

        // Always force main language as fallback, if defined and if either alwaysAvailable is true or not defined
        // if language is already is set on array we still do this as ideally the loop will never
        if (isset($object->mainLanguageCode) && (!isset($object->alwaysAvailable) || $object->alwaysAvailable)) {
            $languages = $this->getLanguages($forcedLanguage, $object->mainLanguageCode);
        } else {
            $languages = $this->getLanguages($forcedLanguage);
        }

        // Get property value first in case it is magic (__isset and __get) property
        $propertyValue = $object->$property;
        foreach ($languages as $lang) {
            if (isset($propertyValue[$lang])) {
                return $propertyValue[$lang];
            }
        }
    }

    /**
     * Gets translated method generic helper.
     *
     * For generic use, expects method exposing translated property as-is on value object, typically $object->$method($language)
     *
     * Languages will consist of either forced language or current languages list, in addition helper will append null
     * to list of languages so method may fallback to main/initial language if supported by domain.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object  Can be any kind of Value object which directly holds the methods that provides translated value.
     * @param string $method Method name, example 'getName', 'description'
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws InvalidArgumentException
     *
     * @return string|null
     */
    public function getTranslatedByMethod(ValueObject $object, $method, $forcedLanguage = null)
    {
        if (!method_exists($object, $method)) {
            throw new InvalidArgumentException('$method', "Method '{$method}' not found on " . get_class($object));
        }

        foreach ($this->getLanguages($forcedLanguage) as $lang) {
            if ($value = $object->$method($lang)) {
                return $value;
            }
        }
    }

    /**
     * Returns a SiteAccess name for translation in $languageCode.
     * This is used for LanguageSwitcher feature (generate links for current content in a different language if available).
     * Will use configured translation_siteaccesses if any. Otherwise will use related siteaccesses (e.g. same repository, same rootLocationId).
     *
     * Will return null if no translation SiteAccess can be found.
     *
     * @param string $languageCode Translation language code.
     *
     * @return string|null
     */
    public function getTranslationSiteAccess($languageCode)
    {
        $translationSiteAccesses = $this->configResolver->getParameter('translation_siteaccesses');
        $relatedSiteAccesses = $this->configResolver->getParameter('related_siteaccesses');

        if (!isset($this->siteAccessesByLanguage[$languageCode])) {
            if ($this->logger) {
                $this->logger->error("Couldn't find any SiteAccess with '$languageCode' as main language.");
            }

            return null;
        }

        $relatedSiteAccesses = $translationSiteAccesses ?: $relatedSiteAccesses;
        $translationSiteAccesses = array_intersect($this->siteAccessesByLanguage[$languageCode], $relatedSiteAccesses);

        return array_shift($translationSiteAccesses);
    }

    /**
     * Returns the list of all available languages, including the ones configured in related SiteAccesses.
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        $translationSiteAccesses = $this->configResolver->getParameter('translation_siteaccesses');
        $relatedSiteAccesses = $translationSiteAccesses ?: $this->configResolver->getParameter('related_siteaccesses');
        $availableLanguages = [];
        $currentLanguages = $this->configResolver->getParameter('languages');
        $availableLanguages[] = array_shift($currentLanguages);

        foreach ($relatedSiteAccesses as $sa) {
            $languages = $this->configResolver->getParameter('languages', null, $sa);
            $availableLanguages[] = array_shift($languages);
        }

        sort($availableLanguages);

        return array_unique($availableLanguages);
    }

    /**
     * @param string|null $forcedLanguage
     * @param string|null $fallbackLanguage
     *
     * @return array|mixed
     */
    private function getLanguages($forcedLanguage = null, $fallbackLanguage = null)
    {
        if ($forcedLanguage !== null) {
            $languages = [$forcedLanguage];
        } else {
            $languages = $this->configResolver->getParameter('languages');
        }

        // Always add $fallbackLanguage, even if null, as last entry so that we can fallback to
        // main/initial language if domain supports it.
        $languages[] = $fallbackLanguage;

        return $languages;
    }
}
