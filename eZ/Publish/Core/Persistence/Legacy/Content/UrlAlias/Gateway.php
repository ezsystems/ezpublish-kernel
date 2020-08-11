<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

/**
 * UrlAlias Gateway.
 */
abstract class Gateway
{
    /**
     * Default database table.
     */
    public const TABLE = 'ezurlalias_ml';

    public const NOP = 'nop';
    public const NOP_ACTION = self::NOP . ':';

    /**
     * Changes the gateway database table.
     *
     * @internal
     *
     * @param string $name
     */
    abstract public function setTable($name);

    /**
     * Loads all list of aliases by given $locationId.
     */
    abstract public function loadAllLocationEntries(int $locationId): array;

    /**
     * Loads list of aliases by given $locationId.
     *
     * @param mixed $locationId
     * @param bool $custom
     * @param mixed $languageId
     *
     * @return array
     */
    abstract public function loadLocationEntries($locationId, $custom = false, $languageId = false);

    /**
     * Loads paged list of global aliases.
     *
     * @param string|null $languageCode
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    abstract public function listGlobalEntries($languageCode = null, $offset = 0, $limit = -1);

    /**
     * Returns boolean indicating if the row with given $id is special root entry.
     *
     * Special root entry entry will have parentId=0 and text=''.
     * In standard installation this entry will point to location with id=2.
     *
     * @param mixed $id
     *
     * @return bool
     */
    abstract public function isRootEntry($id);

    /**
     * Updates single row data matched by composite primary key.
     *
     * Use optional parameter $languageMaskMatch to additionally limit the query match with languages.
     *
     * @param mixed $parentId
     * @param string $textMD5
     * @param array $values associative array with column names as keys and column values as values
     */
    abstract public function updateRow($parentId, $textMD5, array $values);

    /**
     * Inserts new row in urlalias_ml table.
     *
     * @param array $values
     *
     * @return mixed
     */
    abstract public function insertRow(array $values);

    /**
     * Loads single row matched by composite primary key.
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return array
     */
    abstract public function loadRow($parentId, $textMD5);

    /**
     * Downgrades autogenerated entry matched by given $action and $languageId and negatively matched by
     * composite primary key.
     *
     * If language mask of the found entry is composite (meaning it consists of multiple language ids) given
     * $languageId will be removed from mask. Otherwise entry will be marked as history.
     *
     * @param string $action
     * @param mixed $languageId
     * @param mixed $newId
     * @param mixed $parentId
     * @param string $textMD5
     */
    abstract public function cleanupAfterPublish($action, $languageId, $newId, $parentId, $textMD5);

    /**
     * Historizes entry with $action by $languageMask.
     *
     * Used when swapping Location aliases, this ensures that given $languageMask matches a
     * single entry (database row).
     *
     * @param string $action
     * @param int $languageMask
     *
     * @return mixed
     */
    abstract public function historizeBeforeSwap($action, $languageMask);

    /**
     * Marks all entries with given $id as history entries.
     *
     * This method is used by Handler::locationMoved(). Each row is separately historized
     * because future publishing needs to be able to take over history entries safely.
     *
     * @param mixed $id
     * @param mixed $link
     */
    abstract public function historizeId($id, $link);

    /**
     * Updates parent id of autogenerated entries.
     *
     * Update includes history entries.
     *
     * @param mixed $oldParentId
     * @param mixed $newParentId
     */
    abstract public function reparent($oldParentId, $newParentId);

    /**
     * Loads path data identified by given $id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     *
     * @param mixed $id
     *
     * @return array
     */
    abstract public function loadPathData($id);

    /**
     * Loads path data identified by given ordered array of hierarchy data.
     *
     * The first entry in $hierarchyData corresponds to the top-most path element in the path, the second entry the
     * child of the first path element and so on.
     * This method is faster than self::getPath() since it can fetch all elements using only one query, but can be used
     * only for autogenerated paths.
     *
     * @param array $hierarchyData
     *
     * @return array
     */
    abstract public function loadPathDataByHierarchy(array $hierarchyData);

    /**
     * Loads complete URL alias data by given array of path hashes.
     *
     * @param string[] $urlHashes URL string hashes
     *
     * @return array
     */
    abstract public function loadUrlAliasData(array $urlHashes);

    /**
     * Loads autogenerated entry id by given $action and optionally $parentId.
     *
     * @param string $action
     * @param mixed|null $parentId
     *
     * @return array
     */
    abstract public function loadAutogeneratedEntry($action, $parentId = null);

    /**
     * Deletes single custom alias row matched by composite primary key.
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return bool
     */
    abstract public function removeCustomAlias($parentId, $textMD5);

    /**
     * Deletes all rows with given $action and optionally $id.
     *
     * If $id is set only autogenerated entries will be removed.
     *
     * @param string $action
     * @param mixed|null $id
     */
    abstract public function remove($action, $id = null);

    /**
     * Loads all autogenerated entries with given $parentId with optionally included history entries.
     *
     * @param mixed $parentId
     * @param bool $includeHistory
     *
     * @return array
     */
    abstract public function loadAutogeneratedEntries($parentId, $includeHistory = false);

    /**
     * Returns next value for "id" column.
     *
     * @return mixed
     */
    abstract public function getNextId();

    /**
     * Returns main language ID of the Content on the Location with given $locationId.
     *
     * @param int $locationId
     *
     * @return int
     */
    abstract public function getLocationContentMainLanguageId($locationId);

    /**
     * Removes languageId of removed translation from lang_mask and deletes single language rows for multiple Locations.
     *
     * @param int $languageId Language Id to be removed
     * @param string[] $actions actions for which to perform the update
     */
    abstract public function bulkRemoveTranslation($languageId, $actions);

    /**
     * Archive (remove or historize) URL aliases for removed Translations.
     *
     * @param int $locationId
     * @param int $parentId Parent alias used for linking historized entries
     * @param int[] $languageIds Language IDs of removed Translations
     */
    abstract public function archiveUrlAliasesForDeletedTranslations($locationId, $parentId, array $languageIds);

    /**
     * Delete URL aliases pointing to non-existent Locations.
     *
     * @return int Number of affected rows.
     */
    abstract public function deleteUrlAliasesWithoutLocation();

    /**
     * Delete URL aliases pointing to non-existent parent nodes.
     *
     * @return int Number of affected rows.
     */
    abstract public function deleteUrlAliasesWithoutParent();

    /**
     * Delete URL aliases which do not link to any existing URL alias node.
     *
     * Note: Typically link column value is used to determine original alias for an archived entries.
     */
    abstract public function deleteUrlAliasesWithBrokenLink();

    /**
     * Delete "nop" type actions URL aliases that don't have children.
     */
    abstract public function deleteUrlNopAliasesWithoutChildren(): int;

    /**
     * Return aliases which are connected with provided parentId.
     */
    abstract public function getAllChildrenAliases(int $parentId): array;

    /**
     * Attempt repairing data corruption for broken archived URL aliases for Location,
     * assuming there exists restored original (current) entry.
     *
     * @param int $locationId
     */
    abstract public function repairBrokenUrlAliasesForLocation(int $locationId);
}
