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
    ezcQuery;

/**
 * UrlAlias Gateway
 */
class EzcDatabase extends Gateway
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems
     */
    const MAX_LIMIT = 1073741824;

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
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Creates a new EzcDatabase UrlAlias Gateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct (
        EzcDbHandler $dbHandler,
        LanguageMaskGenerator $languageMaskGenerator )
    {
        $this->dbHandler = $dbHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     *
     *
     * @param mixed $locationId
     * @param boolean $custom
     *
     * @return array
     */
    public function loadUrlAliasListDataByLocationId( $locationId, $custom = false )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "id" ),
            $this->dbHandler->quoteColumn( "link" ),
            $this->dbHandler->quoteColumn( "is_alias" ),
            $this->dbHandler->quoteColumn( "alias_redirects" ),
            $this->dbHandler->quoteColumn( "lang_mask" ),
            $this->dbHandler->quoteColumn( "is_original" ),
            $this->dbHandler->quoteColumn( "parent" ),
            $this->dbHandler->quoteColumn( "text_md5" )
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
                    $query->bindValue( 1, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue(
                        $custom ? 1 : 0,
                        null, \PDO::PARAM_INT
                    )
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * @todo docuemtn
     *
     * @param string|null $languageCode
     * @param int $offset
     * @param int|null $limit
     *
     * @return array
     */
    public function loadGlobalUrlAliasListData( $languageCode, $offset = 0, $limit = -1 )
    {
        $limit = $limit === -1 ? self::MAX_LIMIT : $limit;

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "action" ),
            $this->dbHandler->quoteColumn( "id" ),
            $this->dbHandler->quoteColumn( "link" ),
            $this->dbHandler->quoteColumn( "is_alias" ),
            $this->dbHandler->quoteColumn( "alias_redirects" ),
            $this->dbHandler->quoteColumn( "lang_mask" ),
            $this->dbHandler->quoteColumn( "is_original" ),
            $this->dbHandler->quoteColumn( "parent" ),
            $this->dbHandler->quoteColumn( "text_md5" )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "action_type" ),
                    $query->bindValue( "module", null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_original" ),
                    $query->bindValue( 1, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue( 1, null, \PDO::PARAM_INT )
                )
            )
        )->limit(
            $limit,
            $offset
        );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
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
     * Downgrades entry matched by given $action and $languageId and negatively matched by composite primary key.
     *
     * If language mask of the found entry is composite (meaning it consists of multiple language ids) given
     * $languageId will be removed from mask.
     * Otherwise entry will be marked as history.
     *
     * @param string $action
     * @param mixed $languageId
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return void
     */
    public function downgrade( $action, $languageId, $parentId, $textMD5 )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "parent" ),
            $this->dbHandler->quoteColumn( "text_md5" ),
            $this->dbHandler->quoteColumn( "lang_mask" )
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
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn( "lang_mask" ),
                        $query->bindValue( $languageId, null, \PDO::PARAM_INT )
                    ),
                    0
                ),
                // make sure newly published entry is not loaded
                $query->expr->not(
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
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();
        $row = $statement->fetch( \PDO::FETCH_ASSOC );

        if ( !empty( $row ) )
        {
            // If language mask is composite (consists of multiple languages) then remove given language from entry
            if ( $row["lang_mask"] & ~( $languageId | 1 ) )
            {
                $this->removeTranslation( $row["parent"], $row["text_md5"], $languageId );
            }
            // Otherwise mark entry as history
            else
            {
                $this->markAsHistory( $row["parent"], $row["text_md5"] );
            }
        }
    }

    /**
     * Updates single row data matched by composite primary key.
     *
     * Sets "is_original" to 0 thus marking entry as history.
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return void
     */
    protected function markAsHistory( $parentId, $textMD5 )
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteColumn( "ezurlalias_ml" )
        )->set(
            $this->dbHandler->quoteColumn( "is_original" ),
            $query->bindValue( 0, null, \PDO::PARAM_INT )
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
        $query->prepare()->execute();
    }

    /**
     * Updates single row data matched by composite primary key.
     *
     * Removes given $languageId from entry's language mask
     *
     * @param mixed $parentId
     * @param string $textMD5
     * @param mixed $languageId
     *
     * @return void
     */
    protected function removeTranslation( $parentId, $textMD5, $languageId )
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteColumn( "ezurlalias_ml" )
        )->set(
            $this->dbHandler->quoteColumn( "lang_mask" ),
            $query->expr->bitAnd(
                $this->dbHandler->quoteColumn( "lang_mask" ),
                $query->bindValue( ~$languageId, null, \PDO::PARAM_INT )
            )
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
        $query->prepare()->execute();
    }

    /**
     * Re-links custom location history entries.
     *
     * When new location alias is published we need to check for existing history entries with the same action and
     * language mask, update their "link" column with given $newId and "id" column with next id value.
     *
     * @param string $action
     * @param mixed $languageId
     * @param mixed $newId
     * @param mixed $parentId
     * @param mixed $textMD5
     *
     * @return void
     */
    public function relink( $action, $languageId, $newId, $parentId, $textMD5 )
    {
        // Select all history entries (location and custom alias) that match action and language mask
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( "id" ),
            $this->dbHandler->quoteColumn( "is_alias" ),
            $this->dbHandler->quoteColumn( "text_md5" ),
            $this->dbHandler->quoteColumn( "parent" )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "action" ),
                    $query->bindValue( $action, null, \PDO::PARAM_STR )
                ),
                // Only location history entries ("is_original" = 0 AND "is_alias" = 0).
                // This differs from 4.x where both location and custom entries are selected (no "is_alias" = 0).
                // That seems to be meaningless. @todo check
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_original" ),
                    $query->bindValue( 0, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue( 0, null, \PDO::PARAM_INT )
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn( "lang_mask" ),
                        $query->bindValue( $languageId, null, \PDO::PARAM_STR )
                    ),
                    0
                ),
                // @todo this condition may not be needed
                $query->expr->not(
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
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        foreach ( $rows as $row )
        {
            // Columns "is_alias" and "link" are updated in both cases (for custom alias and location alias entry).
            $values = array(
                "link" => $newId,
                //"is_alias" => 0
            );
            //// Id is changed to next value only for location alias when its id is equal to new entry id.
            //if ( $row["is_alias"] != 0 && $row["id"] == $newId )
            // If publish reused history id entry then move history entry to new id
            if ( $row["id"] == $newId )
            {
                $values["id"] = $this->getNextId();
            }
            $this->updateRow(
                $row["parent"],
                $row["text_md5"],
                $values
            );
        }
    }

    /**
     * Updates parent ids of children entries when location is moved.
     *
     * @param string $action
     * @param mixed $languageId
     * @param mixed $newParentId
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @throws \Exception
     * @return void
     */
    public function reparent( $action, $languageId, $newParentId, $parentId, $textMD5 )
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
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue( 0, null, \PDO::PARAM_INT )
                ),
                // make sure newly published entry is not loaded
                $query->expr->not(
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
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();
        $ids = $statement->fetchAll( \PDO::FETCH_COLUMN, 0 );

        if ( !empty( $ids ) )
        {
            $query = $this->dbHandler->createUpdateQuery();
            $query->update(
                $this->dbHandler->quoteColumn( "ezurlalias_ml" )
            )->set(
                $this->dbHandler->quoteColumn( "parent" ),
                $query->bindValue( $newParentId, null, \PDO::PARAM_INT )
            )->where(
                $query->expr->lAnd(
                    $query->expr->in(
                        $this->dbHandler->quoteColumn( "parent" ),
                        $ids
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
     * Updates single row data matched by composite primary key.
     *
     * Use optional parameter $languageMaskMatch to additionally limit the query match with languages.
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
        if ( !isset( $values["id"] ) ) $values["id"] = $this->getNextId();
        if ( !isset( $values["link"] ) ) $values["link"] = $values["id"];
        if ( !isset( $values["is_original"] ) ) $values["is_original"] = ( $values["id"] == $values["link"] ? 1 : 0 );
        if ( !isset( $values["is_alias"] ) ) $values["is_alias"] = 0;
        if ( !isset( $values["alias_redirects"] ) ) $values["alias_redirects"] = 0;
        if ( !isset( $values["action_type"] ) )
        {
            if ( preg_match( "#^(.+):.*#", $values["action"], $matches ) )
            $values["action_type"] = $matches[1];
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
     * @param mixed $parentId
     * @param string $text
     * @param string $textMD5
     *
     * @return mixed
     */
    public function insertNopRow( $parentId, $text, $textMD5 )
    {
        return $this->insertRow(
            array(
                "lang_mask" => 1,
                "action" => "nop:",
                "parent" => $parentId,
                "text" => $text,
                "text_md5" => $textMD5
            )
        );
    }

    /**
     * Returns next value for "id" column.
     *
     * @return mixed
     */
    protected function getNextId()
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
        $query->select( "*" )->from(
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
     * Loads basic URL alias data
     *
     * @param string[] $urlHashes URL string hashes
     *
     * @return array
     */
    public function loadUrlAliasData( array $urlHashes )
    {
        $query = $this->dbHandler->createSelectQuery();

        foreach ( $urlHashes as $level => $urlPartHash )
        {
            $tableName = "ezurlalias_ml" . $level;

            $query->select(
                $this->dbHandler->aliasedColumn( $query, "id", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "link", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "is_alias", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "alias_redirects", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "is_original", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "action", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "lang_mask", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "text", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "parent", $tableName ),
                $this->dbHandler->aliasedColumn( $query, "text_md5", $tableName )
            )->from(
                $query->alias( "ezurlalias_ml", $tableName )
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "text_md5", $tableName ),
                        $query->bindValue( $urlPartHash, null, \PDO::PARAM_STR )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "parent", $tableName ),
                        // root entry has parent column set to 0
                        isset( $previousTableName )
                            ? $this->dbHandler->quoteColumn( "link", $previousTableName )
                            : $query->bindValue( 0, null, \PDO::PARAM_INT )
                    )
                )
            );

            $previousTableName = $tableName;
        }
        $query->limit( 1 );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetch( \PDO::FETCH_ASSOC );
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
     * Only one row can be returned here
     *
     * @param mixed $parentId
     * @param string $action
     *
     * @return array
     */
    public function loadLocationEntryByParentIdAndAction( $parentId, $action )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->selectDistinct(
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
        // @todo remove after testing
        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        if ( count( $rows ) > 1 )
        {
            throw new \RuntimeException( "more than one row returned in " . __METHOD__ );
        }
        return count( $rows ) ? $rows[0] : array();

        return $statement->fetch( \PDO::FETCH_ASSOC );
    }

    /**
     *
     *
     * @throws \RuntimeException
     *
     * @param mixed $id
     *
     * @return array
     */
    public function loadPathData( $id )
    {
        $pathData = array();

        while ( $id != 0 )
        {
            $query = $this->dbHandler->createSelectQuery();
            $query->select(
                $this->dbHandler->quoteColumn( "id" ),
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

            $id = $rows[0]["parent"];
            array_unshift( $pathData, $rows );
        }

        return $pathData;
    }

    /**
     *
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return boolean
     */
    public function removeCustomAlias( $parentId, $textMD5 )
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update( $this->dbHandler->quoteColumn( "ezurlalias_ml" ) );
        $this->setQueryValues(
            $query,
            array(
                "lang_mask" => 1,
                "action" => "nop:",
                "action_type" => "nop",
                "is_alias" => 0
            )
        );
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "parent" ),
                    $query->bindValue( $parentId, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "text_md5" ),
                    $query->bindValue( $textMD5, null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( "is_alias" ),
                    $query->bindValue( 1, null, \PDO::PARAM_INT )
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->rowCount() === 1 ?: false;
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
