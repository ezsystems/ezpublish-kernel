<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Page\PageStorage\Gateway;

use DateTime;
use Doctrine\DBAL\Connection;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\FieldType\Page\PageStorage\Gateway;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\Parts\Item;
use PDO;

class DoctrineStorage extends Gateway
{
    const EZM_POOL_TABLE = 'ezm_pool';
    const EZM_BLOCK_TABLE = 'ezm_block';

    /** @var \Doctrine\DBAL\Connection */
    protected $connection = self::EZM_POOL_TABLE;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return valid items (that are to be displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getValidBlockItems(Block $block)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->quoteIdentifier('p.object_id'),
                $this->connection->quoteIdentifier('p.node_id'),
                $this->connection->quoteIdentifier('p.priority'),
                $this->connection->quoteIdentifier('p.ts_publication'),
                $this->connection->quoteIdentifier('p.ts_visible'),
                $this->connection->quoteIdentifier('p.ts_hidden'),
                $this->connection->quoteIdentifier('p.rotation_until'),
                $this->connection->quoteIdentifier('p.moved_to')
            )
            ->from($this->connection->quoteIdentifier(self::EZM_POOL_TABLE), 'p')
            ->innerJoin(
                'p',
                $this->connection->quoteIdentifier('ezcontentobject_tree'),
                't',
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('t.node_id'),
                    $this->connection->quoteIdentifier('p.node_id')
                )
            )
            ->where(
                $query->expr()->eq('p.block_id', ':blockId'),
                $query->expr()->gt('p.ts_visible', ':tsVisible'),
                $query->expr()->eq('p.ts_hidden', ':tsHidden')
            )
            ->setParameter(':blockId', $block->id)
            ->setParameter(':tsVisible', 0, PDO::PARAM_INT)
            ->setParameter(':tsHidden', 0, PDO::PARAM_INT)
            ->orderBy('p.priority', 'DESC')
        ;

        $statement = $query->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->buildBlockItem(
                $row + [
                    'block_id' => $block->id,
                    'ts_hidden' => 0,
                ]
            );
        }

        return $items;
    }

    /**
     * Return the block item having a highest visible date, for given block.
     * Return null if no block item is registered for block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item|null
     */
    public function getLastValidBlockItem(Block $block)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->quoteIdentifier('p.object_id'),
                $this->connection->quoteIdentifier('p.node_id'),
                $this->connection->quoteIdentifier('p.priority'),
                $this->connection->quoteIdentifier('p.ts_publication'),
                $this->connection->quoteIdentifier('p.ts_visible'),
                $this->connection->quoteIdentifier('p.ts_hidden'),
                $this->connection->quoteIdentifier('p.rotation_until'),
                $this->connection->quoteIdentifier('p.moved_to')
            )
            ->from($this->connection->quoteIdentifier(self::EZM_POOL_TABLE), 'p')
            ->where(
                $query->expr()->eq('p.block_id', ':blockId'),
                $query->expr()->gt('p.ts_visible', ':tsVisible'),
                $query->expr()->eq('p.ts_hidden', ':tsHidden')
            )
            ->setParameter(':blockId', $block->id)
            ->setParameter(':tsVisible', 0, PDO::PARAM_INT)
            ->setParameter(':tsHidden', 0, PDO::PARAM_INT)
            ->orderBy('p.ts_visible', 'DESC')
            ->setMaxResults(1)
        ;

        $statement = $query->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return null;
        }

        return $this->buildBlockItem(
            $rows[0] + [
                'block_id' => $block->id,
                'ts_hidden' => 0,
            ]
        );
    }

    /**
     * Return queued items (the next to be displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getWaitingBlockItems(Block $block)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->quoteIdentifier('p.object_id'),
                $this->connection->quoteIdentifier('p.node_id'),
                $this->connection->quoteIdentifier('p.priority'),
                $this->connection->quoteIdentifier('p.ts_publication'),
                $this->connection->quoteIdentifier('p.ts_hidden'),
                $this->connection->quoteIdentifier('p.ts_visible'),
                $this->connection->quoteIdentifier('p.rotation_until'),
                $this->connection->quoteIdentifier('p.moved_to')
            )
            ->from($this->connection->quoteIdentifier(self::EZM_POOL_TABLE), 'p')
            ->where(
                $query->expr()->eq('p.block_id', ':blockId'),
                $query->expr()->eq('p.ts_visible', ':tsVisible'),
                $query->expr()->eq('p.ts_hidden', ':tsHidden')
            )
            ->setParameter(':blockId', $block->id)
            ->setParameter(':tsVisible', 0, PDO::PARAM_INT)
            ->setParameter(':tsHidden', 0, PDO::PARAM_INT)
            ->orderBy('p.ts_publication')
            ->orderBy('p.priority')
        ;

        $statement = $query->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->buildBlockItem(
                $row + [
                    'block_id' => $block->id,
                    'ts_visible' => 0,
                    'ts_hidden' => 0,
                ]
            );
        }

        return $items;
    }

    /**
     * Return archived items (that were previously displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getArchivedBlockItems(Block $block)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->quoteIdentifier('p.object_id'),
                $this->connection->quoteIdentifier('p.node_id'),
                $this->connection->quoteIdentifier('p.priority'),
                $this->connection->quoteIdentifier('p.ts_publication'),
                $this->connection->quoteIdentifier('p.ts_visible'),
                $this->connection->quoteIdentifier('p.ts_hidden'),
                $this->connection->quoteIdentifier('p.rotation_until'),
                $this->connection->quoteIdentifier('p.moved_to')
            )
            ->from($this->connection->quoteIdentifier(self::EZM_POOL_TABLE), 'p')
            ->where(
                $query->expr()->eq('p.block_id', ':blockId'),
                $query->expr()->gt('p.ts_hidden', ':tsHidden')
            )
            ->setParameter(':blockId', $block->id)
            ->setParameter(':tsHidden', 0, PDO::PARAM_INT)
            ->orderBy('p.ts_hidden')
        ;

        $statement = $query->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->buildBlockItem(
                $row + [
                    'block_id' => $block->id,
                ]
            );
        }

        return $items;
    }

    /**
     * Return Content id for the given Block $id or false if Block could not be found.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If block could not be found.
     *
     * @param string $id
     *
     * @return int
     */
    public function getContentIdByBlockId($id)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->connection->quoteIdentifier('contentobject_id'))
            ->from($this->connection->quoteIdentifier('ezcontentobject_tree'), 't')
            ->innerJoin(
                't',
                $this->connection->quoteIdentifier(self::EZM_BLOCK_TABLE),
                'b',
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('b.node_id'),
                    $this->connection->quoteIdentifier('t.node_id')
                )
            )
            ->where(
                $query->expr()->eq($this->connection->quoteIdentifier('b.id'), ':id')
            )
            ->setParameter(':id', $id, PDO::PARAM_STR)
        ;

        $statement = $query->execute();

        $contentId = $statement->fetchColumn();
        if ($contentId === false) {
            throw new NotFoundException('Block', $id);
        }

        return $contentId;
    }

    /**
     * Build a Page\Parts\Item object from a row returned from ezm_pool table.
     *
     * @param array $row Hash representing a block item as stored in ezm_pool table.
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item
     */
    protected function buildBlockItem(array $row)
    {
        return new Item(
            [
                'blockId' => $row['block_id'],
                'contentId' => (int)$row['object_id'],
                'locationId' => (int)$row['node_id'],
                'priority' => (int)$row['priority'],
                'publicationDate' => new DateTime("@{$row['ts_publication']}"),
                'visibilityDate' => $row['ts_visible'] ? new DateTime(
                    "@{$row['ts_visible']}"
                ) : null,
                'hiddenDate' => $row['ts_hidden'] ? new DateTime("@{$row['ts_hidden']}") : null,
                'rotationUntilDate' => $row['rotation_until'] ? new DateTime(
                    "@{$row['rotation_until']}"
                ) : null,
                'movedTo' => $row['moved_to'],
            ]
        );
    }
}
