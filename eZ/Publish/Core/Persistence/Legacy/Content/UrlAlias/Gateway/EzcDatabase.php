<?php
/**
 * File containing the UrlAlias ezcDatabase Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler as LanguageHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
    eZ\Publish\SPI\Persistence\Content\UrlAlias,
    ezcQuery,
    ezcQueryInsert,
    ezcQueryUpdate;

/**
 * UrlAlias Gateway
 */
class EzcDatabase extends Gateway
{
    /**
     * Columns of database tables.
     *
     * @var array
     * @todo remove after testing
     */
    protected $columns = array(
        "ezurlalias_ml" => array(
            "action",
            "action_type",
            "alias_redirects",
            "id",
            "is_alias",
            "is_original",
            "lang_mask",
            "link",
            "parent",
            "text",
            "text_md5",
        ),
    );

    /**
     * Zeta Components database handler.
     *
     * @var \ezcDbHandler
     */
    protected $dbHandler;

    /**
     * Caching language handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Creates a new EzcDatabase UrlAlias Gateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct (
        EzcDbHandler $dbHandler,
        LanguageHandler $languageHandler,
        LanguageMaskGenerator $languageMaskGenerator )
    {
        $this->dbHandler = $dbHandler;
        $this->languageHandler = $languageHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

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
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn(
                $this->dbHandler->quoteColumn( "id" ),
                $this->dbHandler->quoteColumn( "link" ),
                $this->dbHandler->quoteColumn( "is_alias" ),
                $this->dbHandler->quoteColumn( "alias_redirects" ),
                $this->dbHandler->quoteColumn( "lang_mask" ),
                $this->dbHandler->quoteColumn( "is_original" )
            )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "action" ),
                    $query->bindValue( "eznode:{$locationId}", null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_original" ),
                    $query->bindValue( 1, null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue(
                        $custom ? 1 : 0,
                        null, \PDO::PARAM_STR
                    )
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        foreach ( $rows as $row )
        {
            $row["path"] = $this->getPath( $row["id"], $prioritizedLanguageCodes );
            $row["type"] = $row["is_alias"] ? UrlAlias::VIRTUAL : UrlAlias::LOCATION;
            $row["forward"] = $row["is_alias"] && $row["alias_redirects"];
            $row["destination"] = $locationId;
            $row["always_available"] = (bool)( $row["lang_mask"] & 1 );
            foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $row["lang_mask"] ) as $languageId )
            {
                $row["language_codes"][] = $this->languageHandler->load( $languageId )->languageCode;
            }
        }

        return $rows;
    }

    /**
     * Check if entry is special root entry (nodeId=2)
     *
     * Such entry will have parentId=0 and text=''
     *
     * @param mixed $id
     *
     * @return boolean
     */
    public function isRootEntry( $id )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "text" ),
            $this->dbHandler->quoteColumn( "parent" )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( "id" ),
                $query->bindValue( $id, null, \PDO::PARAM_INT )
            )
        );
        $statement = $query->prepare();
        $statement->execute();
        $row = $statement->fetch( \PDO::FETCH_ASSOC );

        return strlen( $row["text"] ) == 0 && $row["parent"] == 0;
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
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "id" ),
            $this->dbHandler->quoteColumn( "text_md5" ),
            $this->dbHandler->quoteColumn( "parent" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "action" ),
                    $query->bindValue( $action, null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_original" ),
                    $query->bindValue( 0, null, \PDO::PARAM_INT )
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn( "lang_mask" ),
                        $query->bindValue( $languageId, null, \PDO::PARAM_STR )
                    ),
                    0
                ),
                $query->expr->lOr(
                    $query->expr->neq(
                        $this->dbHandler->quoteColumn( "parent" ),
                        $query->bindValue( $parentId, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->neq(
                        $this->dbHandler->quoteColumn( "text_md5" ),
                        $query->bindValue( $newTextMD5, null, \PDO::PARAM_STR )
                    )
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        foreach ( $rows as $row )
        {
            $values = array(
                "link" => $newElementId,
                "is_alias" => 0
            );
            if ( $row["id"] == $newElementId )
                $values["id"] = $this->getNewId();
            $this->updateRow( $row["parent"], $row["text_md5"], $values );
        }
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
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "id" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "action" ),
                    $query->bindValue( $action, null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue( 0, null, \PDO::PARAM_INT )
                ),
                $query->expr->lOr(
                    $query->expr->neq(
                        $this->dbHandler->quoteColumn( "parent" ),
                        $query->bindValue( $parentId, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->neq(
                        $this->dbHandler->quoteColumn( "text_md5" ),
                        $query->bindValue( $newTextMD5, null, \PDO::PARAM_STR )
                    )
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        foreach ( $rows as $row )
        {
            $query = $this->dbHandler->createUpdateQuery();
            $query->update(
                $this->dbHandler->quoteColumn( "ezurlalias_ml" )
            )->set(
                $this->dbHandler->quoteColumn( "parent" ),
                $query->bindValue( $newElementId, null, \PDO::PARAM_INT )
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "parent" ),
                        $query->bindValue( $row["id"], null, \PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "lang_mask" ),
                        $query->expr->bitAnd(
                            $this->dbHandler->quoteColumn( "lang_mask" ),
                            $query->bindValue( $languageId, null, \PDO::PARAM_STR )
                        )
                    )
                )
            );
            $query->prepare()->execute();
        }
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
        $query = $this->dbHandler->createUpdateQuery();
        $query->update( $this->dbHandler->quoteColumn( "ezurlalias_ml" ) );
        $this->setQueryValues( $query, $values );
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "parent" ),
                    $query->bindValue( $parentId, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "text_md5" ),
                    $query->bindValue( $textMD5, null, \PDO::PARAM_STR )
                )
            )
        );
        $query->prepare()->execute();
    }

    public function updateReusableRow( $parentId, $textMD5 )
    {
        $values = array();
        $this->updateRow(
            $parentId,
            $textMD5,
            $values
        );
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
        $this->updateRow(
            $parentId,
            $textMD5,
            array(
                "lang_mask" => 1,
                "action" => "nop:",
                "action_type" => "nop",
                "is_alias" => 0
            )
        );
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
        // @todo remove after testing
        if (
            !isset( $values["text"] ) ||
            !isset( $values["text_md5"] ) ||
            !isset( $values["action"] ) ||
            !isset( $values["parent"] ) ||
            !isset( $values["lang_mask"] ) )
        {
            throw new \Exception( "value set is incomplete, can't execute insert" );
        }
        if ( !isset( $values["id"] ) ) $values["id"] = $this->getNewId();
        if ( !isset( $values["link"] ) ) $values["link"] = $values["id"];
        if ( !isset( $values["is_original"] ) ) $values["is_original"] = ( $values["id"] == $values["link"] ? 1 : 0 );
        if ( !isset( $values["is_alias"] ) ) $values["is_alias"] = 0;
        if ( !isset( $values["alias_redirects"] ) ) $values["alias_redirects"] = 0;
        if ( !isset( $values["action_type"] ) )
        {
            if ( preg_match( "#^(.+):#", $values["action"], $matches ) )
            {
                $values["action_type"] = $matches[1];
            }
            else
            {
                $values["action_type"] = "nop";
            }
        }
        if ( $values["is_alias"] ) $values["is_original"] = 1;
        if ( $values["action"] === "nop:" ) $values["is_original"] = 0;

        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto( $this->dbHandler->quoteTable( "ezurlalias_ml" ) );
        $this->setQueryValues( $query, $values );
        $query->prepare()->execute();

        return $values["id"];
    }

    /**
     *
     *
     * @param \ezcQuery|\ezcQueryInsert|\ezcQueryUpdate $query
     * @param array $values
     *
     * @throws \Exception
     * @return void
     */
    protected function setQueryValues( ezcQuery $query, $values )
    {
        foreach ( $values as $column => $value )
        {
            // @todo remove after testing
            if ( !in_array( $column, $this->columns["ezurlalias_ml"] ) )
            {
                throw new \Exception( "unknown column '$column' for table 'ezurlalias_ml'" );
            }
            switch ( $column )
            {
                case "text":
                case "action":
                case "text_md5":
                case "action_type":
                    $pdoDataType = \PDO::PARAM_STR;
                    break;
                default:
                    $pdoDataType = \PDO::PARAM_INT;
            }
            $query->set(
                $this->dbHandler->quoteColumn( $column ),
                $query->bindValue( $value, null, $pdoDataType )
            );
        }
    }

    /**
     * @param $parentId
     * @param $pathElement
     *
     * @return mixed
     */
    public function insertNopRow( $parentId, $pathElement )
    {
        return $this->insertRow(
            array(
                "lang_mask" => 1,
                "action" => "nop:",
                "parent" => $parentId,
                "text" => $pathElement
            )
        );
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
     *
     *
     * @return mixed
     */
    protected function getNewId()
    {
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable( "ezurlalias_ml_incr" )
        )->set(
            $this->dbHandler->quoteColumn( "id" ),
            $query->bindValue( null, null, \PDO::PARAM_NULL )
        )->prepare()->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( "ezurlalias_ml_incr", "id" )
        );
    }

    /**
     * @param $action
     * @return array
     */
    protected function loadSystemByAction( $action )
    {
        unset($action);
        return array();
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
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "*" )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "parent" ),
                    $query->bindValue( $parentId, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "text_md5" ),
                    $query->bindValue( $textMD5, null, \PDO::PARAM_STR )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetch( \PDO::FETCH_ASSOC );
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
        return md5( strtolower( $text ) );
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
        $query = $this->dbHandler->createSelectQuery();
        $lastTableName = "ezurlalias_ml" . ( count( $urlElements ) - 1 );
        $languageMask = $this->generateLanguageMask( $languageCodes, true );

        $query->select(
            $this->dbHandler->quoteColumn( "id", $lastTableName ),
            $this->dbHandler->quoteColumn( "link", $lastTableName ),
            $this->dbHandler->quoteColumn( "is_alias", $lastTableName ),
            $this->dbHandler->quoteColumn( "alias_redirects", $lastTableName ),
            $this->dbHandler->quoteColumn( "action", $lastTableName ),
            $this->dbHandler->quoteColumn( "is_original", $lastTableName ),
            $this->dbHandler->quoteColumn( "lang_mask", $lastTableName ),
            $this->dbHandler->quoteColumn( "parent", $lastTableName ),
            $this->dbHandler->quoteColumn( "text_md5", $lastTableName )
        );
        foreach ( $urlElements as $index => $urlElement )
        {
            $tableName = "ezurlalias_ml{$index}";

            $query->select(
                $this->dbHandler->aliasedColumn( $query, "text", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "action", $tableName )
            )->from(
                $query->alias( "ezurlalias_ml", $tableName )
            )->where(
                $query->expr->lAnd(
                    $query->expr->gt(
                        $query->expr->bitAnd(
                            $this->dbHandler->quoteColumn( "lang_mask", $tableName ),
                            $languageMask
                        ),
                        $query->bindValue( 0, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "text_md5", $tableName ),
                        $query->bindValue( $this->getHash( $urlElement ), null, \PDO::PARAM_STR )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "parent", $tableName ),
                        // root entry has parent column set to 0
                        isset( $previousTableName )
                            ? $this->dbHandler->quoteColumn( "id", $previousTableName )
                            : $query->bindValue( 0, null, \PDO::PARAM_INT )
                    )
                )
            );

            $previousTableName = $tableName;
        }
        $query->limit( 1 );

        $statement = $query->prepare();
        $statement->execute();
        $row = $statement->fetch( \PDO::FETCH_ASSOC );

        if ( !empty( $row ) )
        {
            // Note: this will only be sufficient for UrlAlias::LOCATION and UrlAlias::RESOURCE type URLs, as these
            // can have only one language per alias. If URL alias is of type UrlAlias::LOCATION additional query will
            // be needed to determine all the languages that it is available in. This is done from Handler.
            // @todo maybe add always available language indicator
            $row["language_codes"] = array();
            foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $row["lang_mask"] ) as $languageId )
            {
                $row["language_codes"][] = $this->languageHandler->load( $languageId )->languageCode;
            }
        }

        return $row;
    }

    /**
     * This method is used when URL alias is of type UrlAlias::LOCATION in order to determine all the languages
     * that it is available in.
     *
     * @throws \RuntimeException If path is incomplete or broken
     *
     * @param array $actions
     * @param array $languageCodes
     *
     * @return array
     */
    public function getLocationUrlAliasLanguageCodes( array $actions, array $languageCodes )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "action" ),
            $this->dbHandler->quoteColumn( "lang_mask" )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        )->where(
            $query->expr->lAnd(
                $query->expr->in(
                    $this->dbHandler->quoteColumn( "action" ),
                    $actions
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_original" ),
                    $query->bindValue( 1, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue( 0, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );

        $actionMap = array();
        foreach ( $rows as $row )
        {
            if ( !isset( $actionMap[$row["action"]] ) )
            {
                $actionMap[$row["action"]] = array();
            }
            $actionMap[$row["action"]][] = $row["lang_mask"] & ~1;
        }

        if ( count( $actionMap ) !== count( $actions ) )
        {
            throw new \RuntimeException( "Path is incomplete or broken, can not determine languages for UrlAlias: " . __METHOD__ );
        }

        $languageMaskSum = array_sum( array_unique( array_pop( $actionMap ) ) );
        $languageCodes = array();
        foreach ( $this->languageMaskGenerator->extractLanguageIdsFromMask( $languageMaskSum ) as $languageId )
        {
            $languageCodes[] = $this->languageHandler->load( $languageId )->languageCode;
        }

        return $languageCodes;
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
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "id" )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "action" ),
                    $query->bindValue( $action, null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_original" ),
                    $query->bindValue( 1, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue( 0, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchColumn();
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
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "id" ),
            $this->dbHandler->quoteColumn( "parent" )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "action" ),
                    $query->bindValue( $action, null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_original" ),
                    $query->bindValue( 1, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue( 0, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetch( \PDO::FETCH_ASSOC );
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
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "id" ),
            $this->dbHandler->quoteColumn( "parent" )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "action" ),
                    $query->bindValue( $action, null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "parent" ),
                    $query->bindValue( $parentId, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_original" ),
                    $query->bindValue( 1, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue( 0, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetch( \PDO::FETCH_ASSOC );
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
        $languages = array();

        foreach ( $languageCodes as $languageCode )
        {
            $languages[$languageCode] = true;
        }

        if ( $alwaysAvailable )
        {
            $languages['always-available'] = true;
        }

        return $this->languageMaskGenerator->generateLanguageMask( $languages );
    }

    /**
     *
     *
     * @param array $rows
     * @param \eZ\Publish\SPI\Persistence\Content\Language[] $prioritizedLanguages
     *
     * @return array
     */
    protected function choosePrioritizedRow( array $rows, $prioritizedLanguages )
    {
        $result = false;
        $score = 0;
        foreach ( $rows as $row )
        {
            if ( $result )
            {
                $newScore = $this->languageScore( $row['lang_mask'], $prioritizedLanguages );
                if ( $newScore > $score )
                {
                    $result = $row;
                    $score = $newScore;
                }
            }
            else
            {
                $result = $row;
                $score = $this->languageScore( $row['lang_mask'], $prioritizedLanguages );
            }
        }

        // If score is still 0, this means that the objects languages don't
        // match the INI settings, and these should be fix according to the doc.
        if ( $score == 0 )
        {
            // @todo: notice
            // None of the available languages are prioritized in the SiteLanguageList setting.
            // An arbitrary language will be used.
        }

        return $result;
    }

    /**
     * @param $mask
     * @param \eZ\Publish\SPI\Persistence\Content\Language[] $prioritizedLanguages
     *
     * @return int|mixed
     */
    protected function languageScore( $mask, $prioritizedLanguages )
    {
        $scores = array();
        $score = 1;
        $mask   = (int)$mask;
        krsort( $prioritizedLanguages );

        foreach ( $prioritizedLanguages as $language )
        {
            $id = (int)$language->id;
            if ( $id & $mask )
            {
                $scores[] = $score;
            }
            ++$score;
        }

        if ( count( $scores ) > 0 )
        {
            return max( $scores );
        }

        return 0;
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
        $pathData = array();
        $prioritizedLanguages = array();
        foreach ( $prioritizedLanguageCodes as $languageCode )
        {
            $prioritizedLanguages[] = $this->languageHandler->loadByLanguageCode( $languageCode );
        }

        while ( $id != 0 )
        {
            $query = $this->dbHandler->createSelectQuery();
            $query->select(
                $this->dbHandler->quoteColumn( "parent" ),
                $this->dbHandler->quoteColumn( "lang_mask" ),
                $this->dbHandler->quoteColumn( "text" )
            )->from(
                $this->dbHandler->quoteTable( "ezurlalias_ml" )
            )->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "id" ),
                    $query->bindValue( $id, null, \PDO::PARAM_INT )
                )
            );

            $statement = $query->prepare();
            $statement->execute();

            $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
            if ( count( $rows ) == 0 )
            {
                // Normally this should never happen
                // @todo remove throw when tested
                $path = join( "/", $pathData );
                throw new \RuntimeException( "Path ({$path}...) is broken, last id is '{$id}': " . __METHOD__ );
                //break;
            }
            $row = $this->choosePrioritizedRow( $rows, $prioritizedLanguages );
            if ( !$row )
            {
                $row = $rows[0];
            }
            $id = (int)$row["parent"];
            array_unshift( $pathData, $row["text"] );
        }

        return join( "/", $pathData );
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
        $row = $this->loadRow( $parentId, $textMD5, $languageId );
        if ( !empty( $row ) )
        {
            if ( (int)$row["lang_mask"] & ( $languageId | 1 ) )
            {
                $childRows = array();
                foreach ( $childRows as $childRow )
                {
                    $this->updateToNopRow( $childRow["parent"], $childRow["text_md5"] );
                }
            }

            $this->updateRow(
                $parentId,
                $textMD5,
                array( "lang_mask" => (int)$row["lang_mask"] & ~1 )
            );

            $this->deleteRow( $parentId, $textMD5 );
        }
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
