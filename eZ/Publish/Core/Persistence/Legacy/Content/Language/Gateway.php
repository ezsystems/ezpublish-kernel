<?php

/**
 * File containing the Language Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Language Handler.
 */
abstract class Gateway
{
    public const CONTENT_LANGUAGE_TABLE = 'ezcontent_language';

    /**
     * A map of language-related table name to its language column.
     *
     * The first column is considered to be a language bitmask.
     * The second, optional, column is an explicit language id.
     *
     * It depends on the schema defined in
     * <code>eZ/Bundle/EzPublishCoreBundle/Resources/config/storage/legacy/schema.yaml</code>
     */
    public const MULTILINGUAL_TABLES_COLUMNS = [
        'ezcobj_state' => ['language_mask', 'default_language_id'],
        'ezcobj_state_group_language' => ['language_id'],
        'ezcobj_state_group' => ['language_mask', 'default_language_id'],
        'ezcobj_state_language' => ['language_id'],
        'ezcontentclass_attribute_ml' => ['language_id'],
        'ezcontentclass_name' => ['language_id'],
        'ezcontentclass' => ['language_mask', 'initial_language_id'],
        'ezcontentobject_attribute' => ['language_id'],
        'ezcontentobject_name' => ['language_id'],
        'ezcontentobject_version' => ['language_mask', 'initial_language_id'],
        'ezcontentobject' => ['language_mask', 'initial_language_id'],
        'ezurlalias_ml' => ['lang_mask'],
    ];

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
     * Loads data list for the Language with $ids.
     *
     * @param int[] $ids
     *
     * @return string[][]|iterable
     */
    abstract public function loadLanguageListData(array $ids): iterable;

    /**
     * Loads data list for Languages by $languageCodes (eg: eng-GB).
     *
     * @param string[] $languageCodes
     *
     * @return string[][]|iterable
     */
    abstract public function loadLanguageListDataByLanguageCode(array $languageCodes): iterable;

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
