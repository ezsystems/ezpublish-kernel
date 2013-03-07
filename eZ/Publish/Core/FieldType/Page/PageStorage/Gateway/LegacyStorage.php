<?php
/**
 * File containing the LegacyStorage gateway class for Page field type.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\PageStorage\Gateway;

use eZ\Publish\Core\FieldType\Page\PageStorage\Gateway;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\Parts\Item;
use RuntimeException;
use DateTime;
use ezcQuerySelect;

class LegacyStorage extends Gateway
{
    const POOL_TABLE = 'ezm_pool';

    protected $dbHandler;

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
     *
     * @return void
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof EzcDbHandler )
        {
            throw new RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected function getConnection()
    {
        if ( $this->dbHandler === null )
        {
            throw new RuntimeException( "Missing database connection." );
        }
        return $this->dbHandler;
    }

    /**
     * Returns valid items (that are to be displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getValidBlockItems( Block $block )
    {
        $dbHandler = $this->getConnection();
        /** @var $q \ezcQuerySelect */
        $q = $dbHandler->createSelectQuery();
        $q
            ->select( '*' )
            ->from( $dbHandler->quoteTable( self::POOL_TABLE ) )
            ->where(
                $q->expr->eq( 'block_id', $q->bindValue( $block->id ) ),
                $q->expr->gt( 'ts_visible', $q->bindValue( 0, null, \PDO::PARAM_INT ) ),
                $q->expr->eq( 'ts_hidden', $q->bindValue( 0, null, \PDO::PARAM_INT ) )
            )
            ->orderBy( 'priority', ezcQuerySelect::DESC );

        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        $items = array();
        foreach ( $rows as $row )
        {
            $items[] = $this->buildBlockItem( $row );
        }

        return $items;
    }

    /**
     * Returns queued items (the next to be displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getWaitingBlockItems( Block $block )
    {
        $dbHandler = $this->getConnection();
        /** @var $q \ezcQuerySelect */
        $q = $dbHandler->createSelectQuery();
        $q
            ->select( '*' )
            ->from( $dbHandler->quoteTable( self::POOL_TABLE ) )
            ->where(
                $q->expr->eq( 'block_id', $q->bindValue( $block->id ) ),
                $q->expr->eq( 'ts_visible', $q->bindValue( 0, null, \PDO::PARAM_INT ) ),
                $q->expr->eq( 'ts_hidden', $q->bindValue( 0, null, \PDO::PARAM_INT ) )
            )
            ->orderBy( 'ts_publication' )
            ->orderBy( 'priority' );

        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        $items = array();
        foreach ( $rows as $row )
        {
            $items[] = $this->buildBlockItem( $row );
        }

        return $items;
    }

    /**
     * Returns archived items (that were previously displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getArchivedBlockItems( Block $block )
    {
        $dbHandler = $this->getConnection();
        /** @var $q \ezcQuerySelect */
        $q = $dbHandler->createSelectQuery();
        $q
            ->select( '*' )
            ->from( $dbHandler->quoteTable( self::POOL_TABLE ) )
            ->where(
                $q->expr->eq( 'block_id', $q->bindValue( $block->id ) ),
                $q->expr->gt( 'ts_hidden', $q->bindValue( 0, null, \PDO::PARAM_INT ) )
            )
            ->orderBy( 'ts_hidden' );

        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        $items = array();
        foreach ( $rows as $row )
        {
            $items[] = $this->buildBlockItem( $row );
        }

        return $items;
    }

    /**
     * Builds a Page\Parts\Item object from a row returned from ezm_pool table.
     *
     * @param array $row Hash representing a block item as stored in ezm_pool table.
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item
     */
    protected function buildBlockItem( array $row )
    {
        return new Item(
            array(
                'blockId'           => $row['block_id'],
                'contentId'         => (int)$row['object_id'],
                'locationId'        => (int)$row['node_id'],
                'priority'          => (int)$row['priority'],
                'publicationDate'   => new DateTime( "@{$row['ts_publication']}" ),
                'visibilityDate'    => $row['ts_visible'] ? new DateTime( "@{$row['ts_visible']}" ) : null,
                'hiddenDate'        => $row['ts_hidden'] ? new DateTime( "@{$row['ts_hidden']}" ) : null,
                'rotationUntilDate' => $row['rotation_until'] ? new DateTime( "@{$row['rotation_until']}" ) : null,
                'movedTo'           => $row['moved_to']
            )
        );
    }
}
