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
     * @param array $prioritizedLanguageCodes
     *
     * @return array
     */
    abstract public function loadUrlAliasListDataByLocationId( $locationId, $custom = false, array $prioritizedLanguageCodes );

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
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return void
     */
    abstract public function updateToNopRow( $parentId, $textMD5 );

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
     * Updates single row data matched by composite primary key
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return void
     */
    abstract public function deleteRow( $parentId, $textMD5 );

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
     *
     * @param string $action
     * @param bool $original
     * @param bool $alias
     *
     * @return mixed
     */
    abstract public function loadRowByAction( $action, $original = true, $alias = false );

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
     *
     *
     * @param string $action
     * @param mixed $languageId
     * @param mixed $newParentId
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return void
     *
     * @todo not clear why this behaviour is desired
     */
    abstract public function reparent( $action, $languageId, $newParentId, $parentId, $textMD5 );

    /**
     *
     *
     * @param mixed $id
     * @param string[] $prioritizedLanguageCodes
     *
     * @return string
     */
    abstract public function getPath( $id, array $prioritizedLanguageCodes );

    /**
     * Loads basic URL alias data
     *
     * Note: columns for end URL part row are not aliased
     *
     * @param string[] $urlElements URL string broken into array of URL parts
     * @param string[] $languageCodes Languages to match against
     *
     * @return array
     */
    abstract public function loadBasicUrlAliasData( array $urlElements, array $languageCodes );

    abstract public function getLocationUrlAliasLanguageCodes( array $actions, array $prioritizedLanguageCodes );

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
     * @param string $action
     *
     * @return array
     */
    abstract public function loadLocationEntryByAction( $action );

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
     * @param integer $languageId
     *
     * @return void
     */
    abstract public function removeTranslation( $parentId, $textMD5, $languageId );

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
