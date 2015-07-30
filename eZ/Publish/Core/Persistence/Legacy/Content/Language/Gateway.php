<?php

/**
 * File containing the Language Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Language Handler.
 */
abstract class Gateway
{
    /**
     * Inserts the given $language.
     *
     * @param Language $language
     *
     * @return int ID of the new language
     */
    abstract public function insertLanguage(Language $language);

    /**
     * Updates the data of the given $language.
     *
     * @param Language $language
     */
    abstract public function updateLanguage(Language $language);

    /**
     * Loads data for the Language with $id.
     *
     * @param int $id
     *
     * @return string[][]
     */
    abstract public function loadLanguageData($id);

    /**
     * Loads data for the Language with Language Code (eg: eng-GB).
     *
     * @param string $languageCode
     *
     * @return string[][]
     */
    abstract public function loadLanguageDataByLanguageCode($languageCode);

    /**
     * Loads the data for all languages.
     *
     * @return string[][]
     */
    abstract public function loadAllLanguagesData();

    /**
     * Deletes the language with $id.
     *
     * @param int $id
     */
    abstract public function deleteLanguage($id);

    /**
     * Check whether a language may be deleted.
     *
     * @param int $id
     *
     * @return bool
     */
    abstract public function canDeleteLanguage($id);
}
