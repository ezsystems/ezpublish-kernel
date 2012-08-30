<?php
/**
 * File containing the Section Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;

/**
 * Section Handler
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     *
     *
     * @param mixed $locationId
     * @param boolean $custom
     * @param array $prioritizedLanguageCodes
     *
     * @return array
     */
    public function loadUrlAliasListDataByLocationId( $locationId, $custom = false, array $prioritizedLanguageCodes )
    {
    }

    /**
     *
     *
     * @param mixed $id
     *
     * @return boolean
     */
    public function isRootEntry( $id )
    {
    }

    /**
     *
     * @param mixed $newElementId
     * @param string $action
     * @param mixed $parentId
     * @param $newTextMD5
     * @param mixed $languageId
     *
     * @internal param string $textMD5
     * @return mixed|void
     */
    public function downgrade( $newElementId, $action, $parentId, $newTextMD5, $languageId )
    {

    }

    /**
     *
     * @param mixed $newElementId
     * @param string $action
     * @param mixed $parentId
     * @param string $newTextMD5
     * @param mixed $languageId
     *
     * @return void
     */
    public function relink( $newElementId, $action, $parentId, $newTextMD5, $languageId )
    {
    }

    /**
     *
     *
     * @param mixed $newElementId
     * @param string $action
     * @param mixed $parentId
     * @param string $newTextMD5
     * @param mixed $languageId
     *
     * @return void
     *
     * @todo not clear why this behaviour is desired
     */
    public function reparent( $newElementId, $action, $parentId, $newTextMD5, $languageId )
    {
    }

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
    public function updateRow( $parentId, $textMD5, array $values, $languageMaskMatch = null )
    {
    }

    /**
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return void
     */
    public function updateToNopRow( $parentId, $textMD5 )
    {
    }

    /**
     *
     *
     * @param array $values
     *
     * @throws \Exception
     * @return mixed
     */
    public function insertRow( array $values )
    {
    }

    /**
     * @param $parentId
     * @param $pathElement
     *
     * @return mixed
     */
    public function insertNopRow( $parentId, $pathElement )
    {
    }

    /**
     * Deletes single row data matched by composite primary key
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return void
     */
    public function deleteRow( $parentId, $textMD5 )
    {

    }

    /**
     * Loads single row data matched by composite primary key
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return array
     */
    public function loadRow( $parentId, $textMD5 )
    {
    }

    /**
     * @param string $action
     * @param bool $original
     * @param bool $alias
     *
     * @return mixed
     */
    public function loadRowByAction( $action, $original = true, $alias = false )
    {

    }

    /**
     * @param string $text
     *
     * @return string
     *
     * @todo use utility method to downcase
     */
    protected function getHash( $text )
    {
    }

    /**
     * Loads basic URL alias data
     *
     * Note that columns for end URL part row are not aliased
     *
     * @param string[] $urlElements URL string broken into array of URL parts
     * @param string[] $languageCodes Languages to match against
     *
     * @return array
     */
    public function loadBasicUrlAliasData( array $urlElements, array $languageCodes )
    {
    }

    /**
     *
     *
     * @param string $action
     *
     * @return int
     */
    public function getDestinationIdByAction( $action )
    {
    }

    /**
     * Generates a language mask from array of language objects
     *
     * @param string[] $languageCodes
     * @param boolean $alwaysAvailable
     *
     * @return int
     *
     * @todo move to lang mask generator
     */
    protected function generateLanguageMask( array $languageCodes, $alwaysAvailable )
    {
    }

    /**
     *
     *
     * @param mixed $id
     * @param string[] $prioritizedLanguageCodes
     *
     * @return string|null path found or null if path is not found
     */
    public function getPath( $id, array $prioritizedLanguageCodes )
    {
    }

    public function getLocationUrlAliasLanguageCodes( array $actions, array $prioritizedLanguageCodes )
    {
    }

    /**
     *
     *
     * @param string $action
     *
     * @return int
     */
    public function loadLocationEntryIdByAction( $action )
    {
    }

    /**
     *
     *
     * @param string $action
     *
     * @return array
     */
    public function loadLocationEntryByAction( $action )
    {
    }

    /**
     *
     *
     * @param mixed $parentId
     * @param string $action
     *
     * @return array
     */
    public function loadLocationEntryByParentIdAndAction( $parentId, $action )
    {
    }

    /**
     *
     *
     * @param mixed $parentId
     * @param string $textMD5
     * @param integer $languageId
     *
     * @return void
     */
    public function removeTranslation( $parentId, $textMD5, $languageId )
    {
    }

    /**
     *
     *
     * @param string $actionName
     * @param string $actionValue
     *
     * @return void
     */
    public function removeByAction( $actionName, $actionValue )
    {

    }
}
