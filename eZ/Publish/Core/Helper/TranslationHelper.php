<?php
/**
 * File containing the ContentHelper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Helper;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Helper class for translation
 */
class TranslationHelper
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var array
     */
    private $siteAccessesByLanguage;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct( ConfigResolverInterface $configResolver, ContentService $contentService, array $siteAccessesByLanguage, LoggerInterface $logger = null )
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
    public function getTranslatedContentName( Content $content, $forcedLanguage = null )
    {
        if ( $forcedLanguage !== null )
        {
            $languages = array( $forcedLanguage );
        }
        else
        {
            $languages = $this->configResolver->getParameter( 'languages' );
        }

        // Always add null to ensure we will pass it to VersionInfo::getName() if no valid translated name is found.
        // So we have at least content name in main language.
        $languages[] = null;

        foreach ( $languages as $lang )
        {
            $translatedName = $content->getVersionInfo()->getName( $lang );
            if ( $translatedName !== null )
            {
                return $translatedName;
            }
        }
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
    public function getTranslatedContentNameByContentInfo( ContentInfo $contentInfo, $forcedLanguage = null )
    {
        if ( isset( $forcedLanguage ) && $forcedLanguage === $contentInfo->mainLanguageCode )
        {
            return $contentInfo->name;
        }

        return $this->getTranslatedContentName(
            $this->contentService->loadContentByContentInfo( $contentInfo ),
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
    public function getTranslatedField( Content $content, $fieldDefIdentifier, $forcedLanguage = null )
    {
        if ( $forcedLanguage !== null )
        {
            $languages = array( $forcedLanguage );
        }
        else
        {
            $languages = $this->configResolver->getParameter( 'languages' );
        }
        // Always add null as last entry so that we can use it pass it as languageCode $content->getField(),
        // forcing to use the main language if all others fail.
        $languages[] = null;

        // Loop over prioritized languages to get the appropriate translated field.
        foreach ( $languages as $lang )
        {
            $field = $content->getField( $fieldDefIdentifier, $lang );
            if ( $field instanceof Field )
            {
                return $field;
            }
        }
    }

    /**
     * Returns Field definition name in the appropriate language for a given content.
     * By default, this method will return the field definition name in current language if translation is present. If not, main language will be used.
     * If $forcedLanguage is provided, will return the field definition name in this language, if translation is present.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldTypeIdentifier Field definition identifier.
     * @param string $forcedLanguage Locale we want the field definition name translated in in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @return string
     */
    public function getTranslatedFieldDefinitionName( ContentType $contentType, $fieldTypeIdentifier, $forcedLanguage = null )
    {
        if ( $forcedLanguage !== null )
        {
            $languages = array( $forcedLanguage );
        }
        else
        {
            $languages = $this->configResolver->getParameter( 'languages' );
        }

        // Loop over prioritized languages to get the appropriate translated field definition name .
        foreach ( $languages as $lang )
        {
            $fieldDefinition = $contentType->getFieldDefinition( $fieldTypeIdentifier );
            if ( $fieldDefinition instanceof FieldDefinition && $fieldDefinition->getName( $lang ) )
            {
                return $fieldDefinition->getName( $lang );
            }
        }

        return null;
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
    public function getTranslationSiteAccess( $languageCode )
    {
        $translationSiteAccesses = $this->configResolver->getParameter( 'translation_siteaccesses' );
        $relatedSiteAccesses = $this->configResolver->getParameter( 'related_siteaccesses' );

        if ( !isset( $this->siteAccessesByLanguage[$languageCode] ) )
        {
            if ( $this->logger )
            {
                $this->logger->error( "Couldn't find any SiteAccess with '$languageCode' as main language." );
            }

            return null;
        }

        $relatedSiteAccesses = $translationSiteAccesses ?: $relatedSiteAccesses;
        $translationSiteAccesses = array_intersect( $this->siteAccessesByLanguage[$languageCode], $relatedSiteAccesses );
        return array_shift( $translationSiteAccesses );
    }

    /**
     * Returns the list of all available languages, including the ones configured in related SiteAccesses.
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        $translationSiteAccesses = $this->configResolver->getParameter( 'translation_siteaccesses' );
        $relatedSiteAccesses = $translationSiteAccesses ?: $this->configResolver->getParameter( 'related_siteaccesses' );
        $availableLanguages = array();
        $currentLanguages = $this->configResolver->getParameter( 'languages' );
        $availableLanguages[] = array_shift( $currentLanguages );

        foreach ( $relatedSiteAccesses as $sa )
        {
            $languages = $this->configResolver->getParameter( 'languages', null, $sa );
            $availableLanguages[] = array_shift( $languages );
        }

        sort( $availableLanguages );
        return array_unique( $availableLanguages );
    }
}
