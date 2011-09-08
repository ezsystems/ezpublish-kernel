<?php
/**
 * File containing the Language MaskGenerator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Language;

/**
 * Language MaskGenerator
 */
class MaskGenerator
{
    /**
     * Language cache
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Cache
     */
    protected $languageCache;

    /**
     * Creates a new Language MaskGenerator
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Language\Cache $languageCache
     */
    public function __construct( Cache $languageCache )
    {
        $this->languageCache = $languageCache;
    }

    /**
     * Generates a language mask from the keys of $languages
     *
     * @param array $languages
     * @return int
     */
    public function generateLanguageMask( array $languages )
    {
        $mask = 0;
        if ( isset( $languages['always-available'] ) )
        {
            $mask |= $languages['always-available'] ? 1 : 0;
            unset( $languages['always-available'] );
        }

        foreach ( $languages as $language => $value )
        {
            $mask |= $this->languageCache->getByLocale( $language )->id;
        }

        return $mask;
    }

    /**
     * Generates a language indicator from $locale and $alwaysAvailable
     *
     * @param string $locale
     * @param bool $alwaysAvailable
     * @return int
     */
    public function generateLanguageIndicator( $locale, $alwaysAvailable )
    {
        return $this->languageCache->getByLocale( $locale )->id
            | ( $alwaysAvailable ? 1 : 0 );
    }

    /**
     * Checks if $language is always available in $languages;
     *
     * @param string $language
     * @param array $languages
     * @return bool
     */
    public function isLanguageAlwaysAvailable( $language, array $languages )
    {
        return ( isset( $languages['always-available'] )
           && ( $languages['always-available'] == $language )
        );
    }

}
