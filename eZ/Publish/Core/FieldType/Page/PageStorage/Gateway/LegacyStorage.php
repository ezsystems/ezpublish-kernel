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
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
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
            ->select( 'object_id, node_id, priority, ts_publication, ts_visible, rotation_until, moved_to' )
            ->from( $dbHandler->quoteTable( 'ezm_pool' ) )
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
            $items[] = $this->buildBlockItem(
                $row + array(
                    'block_id'  => $block->id,
                    'ts_hidden' => 0
                )
            );
        }

        return $items;
    }

    /**
     * Returns the block item having a highest visible date, for given block.
     * Will return null if no block item is registered for block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item|null
     */
    public function getLastValidBlockItem( Block $block )
    {
        $dbHandler = $this->getConnection();
        /** @var $q \ezcQuerySelect */
        $q = $dbHandler->createSelectQuery();
        $q
            ->select( 'object_id, node_id, priority, ts_publication, ts_visible, rotation_until, moved_to' )
            ->from( $dbHandler->quoteTable( 'ezm_pool' ) )
            ->where(
                $q->expr->eq( 'block_id', $q->bindValue( $block->id ) ),
                $q->expr->gt( 'ts_visible', $q->bindValue( 0, null, \PDO::PARAM_INT ) ),
                $q->expr->eq( 'ts_hidden', $q->bindValue( 0, null, \PDO::PARAM_INT ) )
            )
            ->orderBy( 'ts_visible', ezcQuerySelect::DESC )
            ->limit( 1 );

        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        if ( empty( $rows ) )
            return;

        return $this->buildBlockItem(
            $rows[0] + array(
                'block_id'  => $block->id,
                'ts_hidden' => 0
            )
        );
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
            ->select( 'object_id, node_id, priority, ts_publication, rotation_until, moved_to' )
            ->from( $dbHandler->quoteTable( 'ezm_pool' ) )
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
            $items[] = $this->buildBlockItem(
                $row + array(
                    'block_id'      => $block->id,
                    'ts_visible'    => 0,
                    'ts_hidden'     => 0
                )
            );
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
            ->select( 'object_id, node_id, priority, ts_publication, ts_visible, ts_hidden, rotation_until, moved_to' )
            ->from( $dbHandler->quoteTable( 'ezm_pool' ) )
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
            $items[] = $this->buildBlockItem(
                $row + array(
                    'block_id' => $block->id
                )
            );
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
