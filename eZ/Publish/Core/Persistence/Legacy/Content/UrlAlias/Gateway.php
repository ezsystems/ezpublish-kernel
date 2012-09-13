<?php
/**
 * File containing the UrlAlias Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

/**
 * UrlAlias Gateway
 */
abstract class Gateway
{
    /**
     * Loads data for list of UrlAliases by given $locationId
     *
     * @param mixed $locationId
     * @param boolean $custom
     *
     * @return array
     */
    abstract public function loadUrlAliasListDataByLocationId( $locationId, $custom = false );

    /**
     * @todo docuemtn
     *
     * @param string|null $languageCode
     * @param int $offset
     * @param int|null $limit
     *
     * @return array
     */
    abstract public function loadGlobalUrlAliasListData( $languageCode, $offset = 0, $limit = -1 );

    /**
     *
     *
     * @param mixed $id
     *
     * @return boolean
     */
    abstract public function isRootEntry( $id );

    /**
     * Updates single row data matched by composite primary key
     *
     * Use optional parameter $languageMaskMatch to additionally limit the query match with languages
     *
     * @param mixed $parentId
     * @param string $textMD5
     * @param array $values associative array with column names as keys and column values as values
     * @param int|null $languageMaskMatch bit mask of language id's @todo check
     *
     * @return void
     */
    abstract public function updateRow( $parentId, $textMD5, array $values, $languageMaskMatch = null );

    /**
     *
     *
     * @param array $values
     *
     * @return mixed
     */
    abstract public function insertRow( array $values );

    /**
     * @param mixed $parentId
     * @param string $text
     * @param string $textMD5
     *
     * @return mixed
     */
    abstract public function insertNopRow( $parentId, $text, $textMD5 );

    /**
     * Loads single row data matched by composite primary key
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return array
     */
    abstract public function loadRow( $parentId, $textMD5 );

    /**
     *
     * @param string $action
     * @param mixed $languageId
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return void
     */
    abstract public function downgrade( $action, $languageId, $parentId, $textMD5 );

    /**
     * Re-links aliases and location history entries
     *
     *
     * @param string $action
     * @param mixed $languageId
     * @param mixed $newId
     * @param mixed $parentId
     * @param mixed $textMD5
     *
     * @return void
     */
    abstract public function relink( $action, $languageId, $newId, $parentId, $textMD5 );

    /**
     * Updates parent ids of children entries when location is moved.
     *
     * @param string $action
     * @param mixed $languageId
     * @param mixed $newParentId
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return void
     */
    abstract public function reparent( $action, $languageId, $newParentId, $parentId, $textMD5 );

    /**
     *
     *
     * @param mixed $id
     *
     * @return array
     */
    abstract public function loadPathData( $id );

    /**
     * Loads basic URL alias data
     *
     * @param string[] $urlHashes URL string hashes
     *
     * @return array
     */
    abstract public function loadUrlAliasData( array $urlHashes );

    /**
     *
     *
     * @param string $action
     *
     * @return int
     */
    abstract public function loadLocationEntryIdByAction( $action );

    /**
     *
     *
     * @param mixed $parentId
     * @param string $action
     *
     * @return array
     */
    abstract public function loadLocationEntryByParentIdAndAction( $parentId, $action );

    /**
     *
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return boolean
     */
    abstract public function removeCustomAlias( $parentId, $textMD5 );

    /**
     *
     *
     * @param string $actionName
     * @param string $actionValue
     *
     * @return void
     */
    abstract public function removeByAction( $actionName, $actionValue );
}
