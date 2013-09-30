<?php
/**
 * File containing the ContentHelper class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Helper;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Helper class for translation
 */
class TranslationHelper
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    public function __construct( ConfigResolverInterface $configResolver )
    {
        $this->configResolver = $configResolver;
    }

    /**
     * Returns content name, translated.
     * By default this method returns the name in current language, unless $forcedLanguage is provided.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @return string
     */
    public function getTranslatedName( Content $content, $forcedLanguage = null )
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
}
