<?php
/**
 * File containing the Section Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway,
    ezcDbException,
    PDOException;

/**
 * UrlAlias Handler
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
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
        try
        {
            return $this->innerGateway->loadUrlAliasListDataByLocationId( $locationId, $custom );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->isRootEntry( $id );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
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
        try
        {
            $this->innerGateway->downgrade( $action, $languageId, $parentId, $textMD5 );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            $this->innerGateway->relink( $newElementId, $action, $parentId, $newTextMD5, $languageId );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
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
     */
    public function reparent( $newElementId, $action, $parentId, $newTextMD5, $languageId )
    {
        try
        {
            $this->innerGateway->reparent( $newElementId, $action, $parentId, $newTextMD5, $languageId );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
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
        try
        {
            $this->innerGateway->updateRow( $parentId, $textMD5, $values, $languageMaskMatch );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->insertRow( $values );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
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
        try
        {
            return $this->innerGateway->insertNopRow( $parentId, $text, $textMD5 );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->loadRow( $parentId, $textMD5 );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->loadLocationEntryIdByAction( $action );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->loadLocationEntryByParentIdAndAction( $parentId, $action );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
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
        try
        {
            $this->innerGateway->removeByAction( $actionName, $actionValue );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    public function loadGlobalUrlAliasListData( $languageCode, $offset = 0, $limit = -1 )
    {
        try
        {
            return $this->innerGateway->loadGlobalUrlAliasListData( $languageCode, $offset, $limit );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->removeCustomAlias( $parentId, $textMD5 );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->loadUrlAliasData( $urlHashes );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     *
     *
     * @param mixed $id
     *
     * @return array
     */
    public function loadPathData( $id )
    {
        try
        {
            return $this->innerGateway->loadPathData( $id );
        }
        catch ( ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }
}
