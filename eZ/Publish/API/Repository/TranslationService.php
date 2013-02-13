<?php
/**
 * File containing the eZ\Publish\API\Repository\ContentService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Translation;

/**
 * Interface for a translation service.
 *
 * Implement this to use translation backends like Symfony2 Translate, gettext
 * or ezcTranslation.
 *
 * Call the translation method with the current target locale from your
 * templates, for example.
 *
 * @package eZ\Publish\API\Repository
 */
interface TranslationService
{
    /**
     * Translate
     *
     * Translate a Translation value object.
     *
     * @param Translation $translation
     * @param string $locale
     *
     * @return string
     */
    public function translate( Translation $translation, $locale );

    /**
     * Translate string
     *
     * Translate a string. Strings could be useful for the simplest cases.
     * Usually you will always use Translation value objects for this.
     *
     * @param string $translation
     * @param string $locale
     *
     * @return string
     */
    public function translateString( $translation, $locale );
}
