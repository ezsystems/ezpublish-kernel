<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Content Model language gateway.
 *
 * @internal For internal use by Persistence Handlers.
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
     * Insert the given $language.
     */
    abstract public function insertLanguage(Language $language): int;

    /**
     * Update the data of the given $language.
     */
    abstract public function updateLanguage(Language $language): void;

    /**
     * Load data list for the Language with $ids.
     *
     * @param int[] $ids
     *
     * @return string[][]|iterable
     */
    abstract public function loadLanguageListData(array $ids): iterable;

    /**
     * Load data list for Languages by $languageCodes (eg: eng-GB).
     *
     * @param string[] $languageCodes
     *
     * @return string[][]|iterable
     */
    abstract public function loadLanguageListDataByLanguageCode(array $languageCodes): iterable;

    /**
     * Load the data for all languages.
     */
    abstract public function loadAllLanguagesData(): array;

    /**
     * Delete the language with $id.
     */
    abstract public function deleteLanguage(int $id): void;

    /**
     * Check whether a language may be deleted.
     */
    abstract public function canDeleteLanguage(int $id): bool;
}
