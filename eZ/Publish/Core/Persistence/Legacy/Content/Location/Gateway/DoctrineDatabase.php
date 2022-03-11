<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Database\Query as DatabaseQuery;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use RuntimeException;
use PDO;

/**
 * Location gateway implementation using the Doctrine database.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems.
     */
    const MAX_LIMIT = 1073741824;

    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $handler;

    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Construct from database handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(DatabaseHandler $handler, MaskGenerator $languageMaskGenerator)
    {
        $this->handler = $handler;
        $this->connection = $handler->getConnection();
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicNodeData($nodeId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        $q = $this->createNodeQueryBuilder($translations, $useAlwaysAvailable);
        $q->andWhere(
            $q->expr()->eq('t.node_id', $q->createNamedParameter($nodeId, PDO::PARAM_INT))
        );

        if ($row = $q->execute()->fetch(FetchMode::ASSOCIATIVE)) {
            return $row;
        }

        throw new NotFound('location', $nodeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeDataList(array $locationIds, array $translations = null, bool $useAlwaysAvailable = true): iterable
    {
        $q = $this->createNodeQueryBuilder($translations, $useAlwaysAvailable);
        $q->andWhere(
            $q->expr()->in(
                't.node_id',
                $q->createNamedParameter($locationIds, Connection::PARAM_INT_ARRAY)
            )
        );

        return $q->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicNodeDataByRemoteId($remoteId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        $q = $this->createNodeQueryBuilder($translations, $useAlwaysAvailable);
        $q->andWhere(
            $q->expr()->eq('t.remote_id', $q->createNamedParameter($remoteId, PDO::PARAM_STR))
        );

        if ($row = $q->execute()->fetch(FetchMode::ASSOCIATIVE)) {
            return $row;
        }

        throw new NotFound('location', $remoteId);
    }

    /**
     * Loads data for all Locations for $contentId, optionally only in the
     * subtree starting at $rootLocationId.
     *
     * @param int $contentId
     * @param int $rootLocationId
     *
     * @return array
     */
    public function loadLocationDataByContent($contentId, $rootLocationId = null)
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select('*')
            ->from($this->handler->quoteTable('ezcontentobject_tree'))
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('contentobject_id'),
                    $query->bindValue($contentId)
                )
            );
        if ($rootLocationId !== null) {
            $this->applySubtreeLimitation($query, $rootLocationId);
        }
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocationDataByTrashContent(int $contentId, ?int $rootLocationId = null): array
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select('*')
            ->from($this->connection->quoteIdentifier('ezcontentobject_trash'))
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('contentobject_id'),
                    $query->bindValue($contentId)
                )
            );

        if ($rootLocationId !== null) {
            $this->applySubtreeLimitation($query, $rootLocationId, 'ezcontentobject_trash');
        }
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * @see \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway::loadParentLocationsDataForDraftContent
     */
    public function loadParentLocationsDataForDraftContent($contentId, $drafts = null)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->handler->createSelectQuery();
        $query->selectDistinct(
            'ezcontentobject_tree.*'
        )->from(
            $this->handler->quoteTable('ezcontentobject_tree')
        )->innerJoin(
            $this->handler->quoteTable('eznode_assignment'),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn('node_id', 'ezcontentobject_tree'),
                    $this->handler->quoteColumn('parent_node', 'eznode_assignment')
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn('contentobject_id', 'eznode_assignment'),
                    $query->bindValue($contentId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn('op_code', 'eznode_assignment'),
                    $query->bindValue(self::NODE_ASSIGNMENT_OP_CODE_CREATE, null, \PDO::PARAM_INT)
                )
            )
        )->innerJoin(
            $this->handler->quoteTable('ezcontentobject'),
            $query->expr->lAnd(
                $query->expr->lOr(
                    $query->expr->eq(
                        $this->handler->quoteColumn('contentobject_id', 'eznode_assignment'),
                        $this->handler->quoteColumn('id', 'ezcontentobject')
                    )
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn('status', 'ezcontentobject'),
                    $query->bindValue(ContentInfo::STATUS_DRAFT, null, \PDO::PARAM_INT)
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Find all content in the given subtree.
     *
     * @param mixed $sourceId
     * @param bool $onlyIds
     *
     * @return array
     */
    public function getSubtreeContent($sourceId, $onlyIds = false)
    {
        $query = $this->handler->createSelectQuery();
        $query->select($onlyIds ? 'node_id, contentobject_id, depth' : '*')->from(
            $this->handler->quoteTable('ezcontentobject_tree')
        );
        $this->applySubtreeLimitation($query, $sourceId);
        $query->orderBy(
            $this->handler->quoteColumn('depth', 'ezcontentobject_tree')
        )->orderBy(
            $this->handler->quoteColumn('node_id', 'ezcontentobject_tree')
        );
        $statement = $query->prepare();
        $statement->execute();

        $results = $statement->fetchAll($onlyIds ? (PDO::FETCH_COLUMN | PDO::FETCH_GROUP) : PDO::FETCH_ASSOC);
        // array_map() is used to to map all elements stored as $results[$i][0] to $results[$i]
        return $onlyIds ? array_map('reset', $results) : $results;
    }

    /**
     * Limits the given $query to the subtree starting at $rootLocationId.
     *
     * @param \eZ\Publish\Core\Persistence\Database\Query $query
     * @param string $rootLocationId
     * @param string $tableName
     */
    protected function applySubtreeLimitation(
        DatabaseQuery $query,
        $rootLocationId,
        string $tableName = 'ezcontentobject_tree'
    ): void {
        $query->where(
            $query->expr->like(
                $this->handler->quoteColumn('path_string', $tableName),
                $query->bindValue('%/' . $rootLocationId . '/%')
            )
        );
    }

    /**
     * Returns data for the first level children of the location identified by given $locationId.
     *
     * @param mixed $locationId
     *
     * @return array
     */
    public function getChildren($locationId)
    {
        $query = $this->handler->createSelectQuery();
        $query->select('*')->from(
            $this->handler->quoteTable('ezcontentobject_tree')
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('parent_node_id', 'ezcontentobject_tree'),
                $query->bindValue($locationId, null, \PDO::PARAM_INT)
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Update path strings to move nodes in the ezcontentobject_tree table.
     *
     * This query can likely be optimized to use some more advanced string
     * operations, which then depend on the respective database.
     *
     * @todo optimize
     *
     * @param array $sourceNodeData
     * @param array $destinationNodeData
     */
    public function moveSubtreeNodes(array $sourceNodeData, array $destinationNodeData)
    {
        $fromPathString = $sourceNodeData['path_string'];
        $contentObjectId = $sourceNodeData['contentobject_id'];

        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->handler->createSelectQuery();
        $query
            ->select(
                $this->handler->quoteColumn('node_id'),
                $this->handler->quoteColumn('parent_node_id'),
                $this->handler->quoteColumn('path_string'),
                $this->handler->quoteColumn('path_identification_string'),
                $this->handler->quoteColumn('is_hidden')
            )
            ->from($this->handler->quoteTable('ezcontentobject_tree'))
            ->where(
                $query->expr->like(
                    $this->handler->quoteColumn('path_string'),
                    $query->bindValue($fromPathString . '%')
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll();
        $oldParentPathString = implode('/', array_slice(explode('/', $fromPathString), 0, -2)) . '/';
        $oldParentPathIdentificationString = implode(
            '/',
            array_slice(explode('/', $sourceNodeData['path_identification_string']), 0, -1)
        );

        $hiddenNodeIds = $this->getHiddenNodeIds($contentObjectId);
        foreach ($rows as $row) {
            // Prefixing ensures correct replacement when old parent is root node
            $newPathString = str_replace(
                'prefix' . $oldParentPathString,
                $destinationNodeData['path_string'],
                'prefix' . $row['path_string']
            );
            $replace = rtrim($destinationNodeData['path_identification_string'], '/');
            if (empty($oldParentPathIdentificationString)) {
                $replace .= '/';
            }
            $newPathIdentificationString = str_replace(
                'prefix' . $oldParentPathIdentificationString,
                $replace,
                'prefix' . $row['path_identification_string']
            );
            $newParentId = $row['parent_node_id'];
            if ($row['path_string'] === $fromPathString) {
                $newParentId = (int)implode('', array_slice(explode('/', $newPathString), -3, 1));
            }

            /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
            $query = $this->handler->createUpdateQuery();
            $query
                ->update($this->handler->quoteTable('ezcontentobject_tree'))
                ->set(
                    $this->handler->quoteColumn('path_string'),
                    $query->bindValue($newPathString)
                )
                ->set(
                    $this->handler->quoteColumn('path_identification_string'),
                    $query->bindValue($newPathIdentificationString)
                )
                ->set(
                    $this->handler->quoteColumn('depth'),
                    $query->bindValue(substr_count($newPathString, '/') - 2)
                )
                ->set(
                    $this->handler->quoteColumn('parent_node_id'),
                    $query->bindValue($newParentId)
                );

            if ($destinationNodeData['is_hidden'] || $destinationNodeData['is_invisible']) {
                // CASE 1: Mark whole tree as invisible if destination is invisible and/or hidden
                $query->set(
                    $this->handler->quoteColumn('is_invisible'),
                    $query->bindValue(1)
                );
            } elseif (!$sourceNodeData['is_hidden'] && $sourceNodeData['is_invisible']) {
                // CASE 2: source is only invisible, we will need to re-calculate whole moved tree visibility
                $query->set(
                    $this->handler->quoteColumn('is_invisible'),
                    $query->bindValue($this->isHiddenByParentOrSelf($newPathString, $hiddenNodeIds) ? 1 : 0)
                );
            } else {
                // CASE 3: keep invisible flags as is (source is either hidden or not hidden/invisible at all)
            }

            $query->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('node_id'),
                    $query->bindValue($row['node_id'])
                )
            );
            $query->prepare()->execute();
        }
    }

    /**
     * @return int[]
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getHiddenNodeIds(int $contentObjectId)
    {
        $query = $this->buildHiddenSubtreeQuery();
        $expr = $query->expr();
        $query
            ->andWhere(
                $expr->eq(
                    'id',
                    $query->createPositionalParameter(
                        $contentObjectId,
                        ParameterType::INTEGER
                    )
                )
            );
        $statement = $query->execute();

        $result = $statement->fetchAll(FetchMode::COLUMN);

        return array_map('intval', $result);
    }

    /**
     * Calculates if given node is hidden by location (self or any parent) or on Content level
     *
     * @param string $pathString
     * @param int[] $hiddenNodeIds
     */
    private function isHiddenByParentOrSelf(string $pathString, array $hiddenNodeIds): bool
    {
        $parentNodeIds = array_map('intval', explode('/', trim($pathString, '/')));
        foreach ($parentNodeIds as $parentNodeId) {
            if (in_array($parentNodeId, $hiddenNodeIds, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Updated subtree modification time for all nodes on path.
     *
     * @param string $pathString
     * @param int|null $timestamp
     */
    public function updateSubtreeModificationTime($pathString, $timestamp = null)
    {
        $nodes = array_filter(explode('/', $pathString));
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('ezcontentobject_tree'))
            ->set(
                $this->handler->quoteColumn('modified_subnode'),
                $query->bindValue(
                    $timestamp ?: time()
                )
            )
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn('node_id'),
                    $nodes
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Sets a location to be hidden, and it self + all children to invisible.
     *
     * @param string $pathString
     */
    public function hideSubtree($pathString)
    {
        $this->setNodeWithChildrenInvisible($pathString);
        $this->setNodeHidden($pathString);
    }

    /**
     * @param string $pathString
     **/
    public function setNodeWithChildrenInvisible(string $pathString): void
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('ezcontentobject_tree'))
            ->set(
                $this->handler->quoteColumn('is_invisible'),
                $query->bindValue(1)
            )
            ->set(
                $this->handler->quoteColumn('modified_subnode'),
                $query->bindValue(time())
            )
            ->where(
                $query->expr->like(
                    $this->handler->quoteColumn('path_string'),
                    $query->bindValue($pathString . '%')
                )
            );

        $query->prepare()->execute();
    }

    /**
     * @param string $pathString
     **/
    public function setNodeHidden(string $pathString): void
    {
        $this->setNodeHiddenStatus($pathString, true);
    }

    /**
     * @param string $pathString
     * @param bool $isHidden
     */
    private function setNodeHiddenStatus(string $pathString, bool $isHidden): void
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('ezcontentobject_tree'))
            ->set(
                $this->handler->quoteColumn('is_hidden'),
                $query->bindValue((int) $isHidden)
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('path_string'),
                    $query->bindValue($pathString)
                )
            );

        $query->prepare()->execute();
    }

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hiding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param string $pathString
     */
    public function unHideSubtree($pathString)
    {
        $this->setNodeUnhidden($pathString);
        $this->setNodeWithChildrenVisible($pathString);
    }

    /**
     * Sets a location + children to visible unless a parent is hiding the tree.
     *
     * @param string $pathString
     **/
    public function setNodeWithChildrenVisible(string $pathString): void
    {
        // Check if any parent nodes are explicitly hidden
        $query = $this->handler->createSelectQuery();
        $query
            ->select($this->handler->quoteColumn('path_string'))
            ->from($this->handler->quoteTable('ezcontentobject_tree'))
            ->leftJoin('ezcontentobject', 'ezcontentobject_tree.contentobject_id', 'ezcontentobject.id')
            ->where(
                $query->expr->lAnd(
                    $query->expr->lOr(
                        $query->expr->eq(
                            $this->handler->quoteColumn('is_hidden', 'ezcontentobject_tree'),
                            $query->bindValue(1)
                        ),
                        $query->expr->eq(
                            $this->handler->quoteColumn('is_hidden', 'ezcontentobject'),
                            $query->bindValue(1)
                        )
                    ),
                    $query->expr->in(
                        $this->handler->quoteColumn('node_id'),
                        array_filter(explode('/', $pathString))
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
        if (count($statement->fetchAll(\PDO::FETCH_COLUMN))) {
            // There are parent nodes set hidden, so that we can skip marking
            // something visible again.
            return;
        }

        // Find nodes of explicitly hidden subtrees in the subtree which
        // should be unhidden
        $query = $this->handler->createSelectQuery();
        $query
            ->select($this->handler->quoteColumn('path_string'))
            ->from($this->handler->quoteTable('ezcontentobject_tree'))
            ->leftJoin('ezcontentobject', 'ezcontentobject_tree.contentobject_id', 'ezcontentobject.id')
            ->where(
                $query->expr->lAnd(
                    $query->expr->lOr(
                        $query->expr->eq(
                            $this->handler->quoteColumn('is_hidden', 'ezcontentobject_tree'),
                            $query->bindValue(1)
                        ),
                        $query->expr->eq(
                            $this->handler->quoteColumn('is_hidden', 'ezcontentobject'),
                            $query->bindValue(1)
                        )
                    ),
                    $query->expr->like(
                        $this->handler->quoteColumn('path_string'),
                        $query->bindValue($pathString . '%')
                    )
                )
            );
        $statement = $query->prepare();
        $statement->execute();
        $hiddenSubtrees = $statement->fetchAll(\PDO::FETCH_COLUMN);

        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('ezcontentobject_tree'))
            ->set(
                $this->handler->quoteColumn('is_invisible'),
                $query->bindValue(0)
            )
            ->set(
                $this->handler->quoteColumn('modified_subnode'),
                $query->bindValue(time())
            );

        // Build where expression selecting the nodes, which should be made
        // visible again
        $where = $query->expr->like(
            $this->handler->quoteColumn('path_string'),
            $query->bindValue($pathString . '%')
        );
        if (count($hiddenSubtrees)) {
            $handler = $this->handler;
            $where = $query->expr->lAnd(
                $where,
                $query->expr->lAnd(
                    array_map(
                        function ($pathString) use ($query, $handler) {
                            return $query->expr->not(
                                $query->expr->like(
                                    $handler->quoteColumn('path_string'),
                                    $query->bindValue($pathString . '%')
                                )
                            );
                        },
                        $hiddenSubtrees
                    )
                )
            );
        }
        $query->where($where);
        $query->prepare()->execute();
    }

    private function buildHiddenSubtreeQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select('node_id')
            ->from('ezcontentobject_tree', 't')
            ->join('t', 'ezcontentobject', 'c', 't.contentobject_id = c.id')
            ->where(
                $expr->or(
                    $expr->eq(
                        't.is_hidden',
                        $query->createPositionalParameter(1, ParameterType::BOOLEAN)
                    ),
                    $expr->eq(
                        'c.is_hidden',
                        $query->createPositionalParameter(1, ParameterType::BOOLEAN)
                    )
                )
            );

        return $query;
    }

    /**
     * Sets location to be unhidden.
     *
     * @param string $pathString
     **/
    public function setNodeUnhidden(string $pathString): void
    {
        $this->setNodeHiddenStatus($pathString, false);
    }

    /**
     * Swaps the content object being pointed to by a location object.
     *
     * Make the location identified by $locationId1 refer to the Content
     * referred to by $locationId2 and vice versa.
     *
     * @param int $locationId1
     * @param int $locationId2
     *
     * @return bool
     */
    public function swap(int $locationId1, int $locationId2): bool
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr = $queryBuilder->expr();
        $queryBuilder
            ->select('node_id', 'main_node_id', 'contentobject_id', 'contentobject_version')
            ->from('ezcontentobject_tree')
            ->where(
                $expr->in(
                    'node_id',
                    ':locationIds'
                )
            )
            ->setParameter('locationIds', [$locationId1, $locationId2], Connection::PARAM_INT_ARRAY)
        ;
        $statement = $queryBuilder->execute();
        $contentObjects = [];
        foreach ($statement->fetchAll(FetchMode::ASSOCIATIVE) as $row) {
            $row['is_main_node'] = (int)$row['main_node_id'] === (int)$row['node_id'];
            $contentObjects[$row['node_id']] = $row;
        }

        if (!isset($contentObjects[$locationId1], $contentObjects[$locationId2])) {
            throw new RuntimeException(
                sprintf(
                    '%s: failed to fetch either Location %d or Location %d',
                    __METHOD__,
                    $locationId1,
                    $locationId2
                )
            );
        }
        $content1data = $contentObjects[$locationId1];
        $content2data = $contentObjects[$locationId2];

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->update('ezcontentobject_tree')
            ->set('contentobject_id', ':contentId')
            ->set('contentobject_version', ':versionNo')
            ->set('main_node_id', ':mainNodeId')
            ->where(
                $expr->eq('node_id', ':locationId')
            );

        $queryBuilder
            ->setParameter(':contentId', $content2data['contentobject_id'])
            ->setParameter(':versionNo', $content2data['contentobject_version'])
            ->setParameter(
                ':mainNodeId',
                // make main Location main again, preserve main Location id of non-main one
                $content2data['is_main_node']
                    ? $content1data['node_id']
                    : $content2data['main_node_id']
            )
            ->setParameter('locationId', $locationId1);

        // update Location 1 entry
        $queryBuilder->execute();

        $queryBuilder
            ->setParameter(':contentId', $content1data['contentobject_id'])
            ->setParameter(':versionNo', $content1data['contentobject_version'])
            ->setParameter(
                ':mainNodeId',
                $content1data['is_main_node']
                    // make main Location main again, preserve main Location id of non-main one
                    ? $content2data['node_id']
                    : $content1data['main_node_id']
            )
            ->setParameter('locationId', $locationId2);

        // update Location 2 entry
        $queryBuilder->execute();

        return true;
    }

    /**
     * Creates a new location in given $parentNode.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param array $parentNode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function create(CreateStruct $createStruct, array $parentNode)
    {
        $location = new Location();
        /** @var $query \eZ\Publish\Core\Persistence\Database\InsertQuery */
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto($this->handler->quoteTable('ezcontentobject_tree'))
            ->set(
                $this->handler->quoteColumn('contentobject_id'),
                $query->bindValue($location->contentId = $createStruct->contentId, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('contentobject_is_published'),
                $query->bindValue(1, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('contentobject_version'),
                $query->bindValue($createStruct->contentVersion, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('depth'),
                $query->bindValue($location->depth = $parentNode['depth'] + 1, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('is_hidden'),
                $query->bindValue($location->hidden = $createStruct->hidden, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('is_invisible'),
                $query->bindValue($location->invisible = $createStruct->invisible, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('modified_subnode'),
                $query->bindValue(time(), null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('node_id'),
                $this->handler->getAutoIncrementValue('ezcontentobject_tree', 'node_id')
            )->set(
                $this->handler->quoteColumn('parent_node_id'),
                $query->bindValue($location->parentId = $parentNode['node_id'], null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('path_identification_string'),
                $query->bindValue($location->pathIdentificationString = $createStruct->pathIdentificationString, null, \PDO::PARAM_STR)
            )->set(
                $this->handler->quoteColumn('path_string'),
                $query->bindValue('dummy') // Set later
            )->set(
                $this->handler->quoteColumn('priority'),
                $query->bindValue($location->priority = $createStruct->priority, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('remote_id'),
                $query->bindValue($location->remoteId = $createStruct->remoteId, null, \PDO::PARAM_STR)
            )->set(
                $this->handler->quoteColumn('sort_field'),
                $query->bindValue($location->sortField = $createStruct->sortField, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('sort_order'),
                $query->bindValue($location->sortOrder = $createStruct->sortOrder, null, \PDO::PARAM_INT)
            );
        $query->prepare()->execute();

        $location->id = (int)$this->handler->lastInsertId($this->handler->getSequenceName('ezcontentobject_tree', 'node_id'));

        $mainLocationId = $createStruct->mainLocationId === true ? $location->id : $createStruct->mainLocationId;
        $location->pathString = $parentNode['path_string'] . $location->id . '/';
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('ezcontentobject_tree'))
            ->set(
                $this->handler->quoteColumn('path_string'),
                $query->bindValue($location->pathString)
            )
            ->set(
                $this->handler->quoteColumn('main_node_id'),
                $query->bindValue($mainLocationId, null, \PDO::PARAM_INT)
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('node_id'),
                    $query->bindValue($location->id, null, \PDO::PARAM_INT)
                )
            );
        $query->prepare()->execute();

        return $location;
    }

    /**
     * Create an entry in the node assignment table.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param mixed $parentNodeId
     * @param int $type
     */
    public function createNodeAssignment(CreateStruct $createStruct, $parentNodeId, $type = self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP)
    {
        $isMain = ($createStruct->mainLocationId === true ? 1 : 0);

        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto($this->handler->quoteTable('eznode_assignment'))
            ->set(
                $this->handler->quoteColumn('contentobject_id'),
                $query->bindValue($createStruct->contentId, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('contentobject_version'),
                $query->bindValue($createStruct->contentVersion, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('from_node_id'),
                $query->bindValue(0, null, \PDO::PARAM_INT) // unused field
            )->set(
                $this->handler->quoteColumn('id'),
                $this->handler->getAutoIncrementValue('eznode_assignment', 'id')
            )->set(
                $this->handler->quoteColumn('is_main'),
                $query->bindValue($isMain, null, \PDO::PARAM_INT) // Changed by the business layer, later
            )->set(
                $this->handler->quoteColumn('op_code'),
                $query->bindValue($type, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('parent_node'),
                $query->bindValue($parentNodeId, null, \PDO::PARAM_INT)
            )->set(
            // parent_remote_id column should contain the remote id of the corresponding Location
                $this->handler->quoteColumn('parent_remote_id'),
                $query->bindValue($createStruct->remoteId, null, \PDO::PARAM_STR)
            )->set(
            // remote_id column should contain the remote id of the node assignment itself,
            // however this was never implemented completely in Legacy Stack, so we just set
            // it to default value '0'
                $this->handler->quoteColumn('remote_id'),
                $query->bindValue('0', null, \PDO::PARAM_STR)
            )->set(
                $this->handler->quoteColumn('sort_field'),
                $query->bindValue($createStruct->sortField, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('sort_order'),
                $query->bindValue($createStruct->sortOrder, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('priority'),
                $query->bindValue($createStruct->priority, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('is_hidden'),
                $query->bindValue($createStruct->hidden, null, \PDO::PARAM_INT)
            );
        $query->prepare()->execute();
    }

    /**
     * Deletes node assignment for given $contentId and $versionNo.
     *
     * If $versionNo is not passed all node assignments for given $contentId are deleted
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function deleteNodeAssignment($contentId, $versionNo = null)
    {
        $query = $this->handler->createDeleteQuery();
        $query->deleteFrom(
            'eznode_assignment'
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('contentobject_id'),
                $query->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );
        if (isset($versionNo)) {
            $query->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('contentobject_version'),
                    $query->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            );
        }
        $query->prepare()->execute();
    }

    /**
     * Update node assignment table.
     *
     * @param int $contentObjectId
     * @param int $oldParent
     * @param int $newParent
     * @param int $opcode
     */
    public function updateNodeAssignment($contentObjectId, $oldParent, $newParent, $opcode)
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('eznode_assignment'))
            ->set(
                $this->handler->quoteColumn('parent_node'),
                $query->bindValue($newParent, null, \PDO::PARAM_INT)
            )
            ->set(
                $this->handler->quoteColumn('op_code'),
                $query->bindValue($opcode, null, \PDO::PARAM_INT)
            )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn('contentobject_id'),
                        $query->bindValue($contentObjectId, null, \PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn('parent_node'),
                        $query->bindValue($oldParent, null, \PDO::PARAM_INT)
                    )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Create locations from node assignments.
     *
     * Convert existing node assignments into real locations.
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     */
    public function createLocationsFromNodeAssignments($contentId, $versionNo)
    {
        // select all node assignments with OP_CODE_CREATE (3) for this content
        $query = $this->handler->createSelectQuery();
        $query
            ->select('*')
            ->from($this->handler->quoteTable('eznode_assignment'))
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn('contentobject_id'),
                        $query->bindValue($contentId, null, \PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn('contentobject_version'),
                        $query->bindValue($versionNo, null, \PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn('op_code'),
                        $query->bindValue(self::NODE_ASSIGNMENT_OP_CODE_CREATE, null, \PDO::PARAM_INT)
                    )
                )
            )->orderBy('id');
        $statement = $query->prepare();
        $statement->execute();

        // convert all these assignments to nodes

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            if ((bool)$row['is_main'] === true) {
                $mainLocationId = true;
            } else {
                $mainLocationId = $this->getMainNodeId($contentId);
            }

            $parentLocationData = $this->getBasicNodeData($row['parent_node']);
            $isInvisible = $row['is_hidden'] || $parentLocationData['is_hidden'] || $parentLocationData['is_invisible'];
            $this->create(
                new CreateStruct(
                    [
                        'contentId' => $row['contentobject_id'],
                        'contentVersion' => $row['contentobject_version'],
                        'mainLocationId' => $mainLocationId,
                        'remoteId' => $row['parent_remote_id'],
                        'sortField' => $row['sort_field'],
                        'sortOrder' => $row['sort_order'],
                        'priority' => $row['priority'],
                        'hidden' => $row['is_hidden'],
                        'invisible' => $isInvisible,
                    ]
                ),
                $parentLocationData
            );

            $this->updateNodeAssignment(
                $row['contentobject_id'],
                $row['parent_node'],
                $row['parent_node'],
                self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP
            );
        }
    }

    /**
     * Updates all Locations of content identified with $contentId with $versionNo.
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     */
    public function updateLocationsContentVersionNo($contentId, $versionNo)
    {
        $query = $this->handler->createUpdateQuery();
        $query->update(
            $this->handler->quoteTable('ezcontentobject_tree')
        )->set(
            $this->handler->quoteColumn('contentobject_version'),
            $query->bindValue($versionNo, null, \PDO::PARAM_INT)
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('contentobject_id'),
                $contentId
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Searches for the main nodeId of $contentId in $versionId.
     *
     * @param int $contentId
     *
     * @return int|bool
     */
    private function getMainNodeId($contentId)
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select('node_id')
            ->from($this->handler->quoteTable('ezcontentobject_tree'))
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn('contentobject_id'),
                        $query->bindValue($contentId, null, \PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn('node_id'),
                        $this->handler->quoteColumn('main_node_id')
                    )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if (count($result) === 1) {
            return (int)$result[0]['node_id'];
        } else {
            return false;
        }
    }

    /**
     * Updates an existing location.
     *
     * Will not throw anything if location id is invalid or no entries are affected.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct $location
     * @param int $locationId
     */
    public function update(UpdateStruct $location, $locationId)
    {
        $query = $this->handler->createUpdateQuery();

        $query
            ->update($this->handler->quoteTable('ezcontentobject_tree'))
            ->set(
                $this->handler->quoteColumn('priority'),
                $query->bindValue($location->priority)
            )
            ->set(
                $this->handler->quoteColumn('remote_id'),
                $query->bindValue($location->remoteId)
            )
            ->set(
                $this->handler->quoteColumn('sort_order'),
                $query->bindValue($location->sortOrder)
            )
            ->set(
                $this->handler->quoteColumn('sort_field'),
                $query->bindValue($location->sortField)
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('node_id'),
                    $locationId
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        // Commented due to EZP-23302: Update Location fails if no change is performed with the update
        // Should be fixed with PDO::MYSQL_ATTR_FOUND_ROWS instead
        /*if ( $statement->rowCount() < 1 )
        {
            throw new NotFound( 'location', $locationId );
        }*/
    }

    /**
     * Updates path identification string for given $locationId.
     *
     * @param mixed $locationId
     * @param mixed $parentLocationId
     * @param string $text
     */
    public function updatePathIdentificationString($locationId, $parentLocationId, $text)
    {
        $parentData = $this->getBasicNodeData($parentLocationId);

        $newPathIdentificationString = empty($parentData['path_identification_string']) ?
            $text :
            $parentData['path_identification_string'] . '/' . $text;

        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->handler->createUpdateQuery();
        $query->update(
            'ezcontentobject_tree'
        )->set(
            $this->handler->quoteColumn('path_identification_string'),
            $query->bindValue($newPathIdentificationString, null, \PDO::PARAM_STR)
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('node_id'),
                $query->bindValue($locationId, null, \PDO::PARAM_INT)
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Deletes ezcontentobject_tree row for given $locationId (node_id).
     *
     * @param mixed $locationId
     */
    public function removeLocation($locationId)
    {
        $query = $this->handler->createDeleteQuery();
        $query->deleteFrom(
            'ezcontentobject_tree'
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('node_id'),
                $query->bindValue($locationId, null, \PDO::PARAM_INT)
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Returns id of the next in line node to be set as a new main node.
     *
     * This returns lowest node id for content identified by $contentId, and not of
     * the node identified by given $locationId (current main node).
     * Assumes that content has more than one location.
     *
     * @param mixed $contentId
     * @param mixed $locationId
     *
     * @return array
     */
    public function getFallbackMainNodeData($contentId, $locationId)
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn('node_id'),
            $this->handler->quoteColumn('contentobject_version'),
            $this->handler->quoteColumn('parent_node_id')
        )->from(
            $this->handler->quoteTable('ezcontentobject_tree')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn('contentobject_id'),
                    $query->bindValue($contentId, null, \PDO::PARAM_INT)
                ),
                $query->expr->neq(
                    $this->handler->quoteColumn('node_id'),
                    $query->bindValue($locationId, null, \PDO::PARAM_INT)
                )
            )
        )->orderBy('node_id', SelectQuery::ASC)->limit(1);
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Sends a single location identified by given $locationId to the trash.
     *
     * The associated content object is left untouched.
     *
     * @param mixed $locationId
     *
     * @return bool
     */
    public function trashLocation($locationId)
    {
        $locationRow = $this->getBasicNodeData($locationId);

        /** @var $query \eZ\Publish\Core\Persistence\Database\InsertQuery */
        $query = $this->handler->createInsertQuery();
        $query->insertInto($this->handler->quoteTable('ezcontentobject_trash'));

        unset($locationRow['contentobject_is_published']);
        $locationRow['trashed'] = time();
        foreach ($locationRow as $key => $value) {
            $query->set($key, $query->bindValue($value));
        }

        $query->prepare()->execute();

        $this->removeLocation($locationRow['node_id']);
        $this->setContentStatus($locationRow['contentobject_id'], ContentInfo::STATUS_TRASHED);
    }

    /**
     * Returns a trashed location to normal state.
     *
     * Recreates the originally trashed location in the new position. If no new
     * position has been specified, it will be tried to re-create the location
     * at the old position. If this is not possible ( because the old location
     * does not exist any more) and exception is thrown.
     *
     * @param mixed $locationId
     * @param mixed|null $newParentId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function untrashLocation($locationId, $newParentId = null)
    {
        $row = $this->loadTrashByLocation($locationId);

        $newLocation = $this->create(
            new CreateStruct(
                [
                    'priority' => $row['priority'],
                    'hidden' => $row['is_hidden'],
                    'invisible' => $row['is_invisible'],
                    'remoteId' => $row['remote_id'],
                    'contentId' => $row['contentobject_id'],
                    'contentVersion' => $row['contentobject_version'],
                    'mainLocationId' => true, // Restored location is always main location
                    'sortField' => $row['sort_field'],
                    'sortOrder' => $row['sort_order'],
                ]
            ),
            $this->getBasicNodeData($newParentId ?: $row['parent_node_id'])
        );

        $this->removeElementFromTrash($locationId);
        $this->setContentStatus($row['contentobject_id'], ContentInfo::STATUS_PUBLISHED);

        return $newLocation;
    }

    /**
     * @param mixed $contentId
     * @param int $status
     */
    protected function setContentStatus($contentId, $status)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->handler->createUpdateQuery();
        $query->update(
            'ezcontentobject'
        )->set(
            $this->handler->quoteColumn('status'),
            $query->bindValue($status, null, \PDO::PARAM_INT)
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('id'),
                $query->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Loads trash data specified by location ID.
     *
     * @param mixed $locationId
     *
     * @return array
     */
    public function loadTrashByLocation($locationId)
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select('*')
            ->from($this->handler->quoteTable('ezcontentobject_trash'))
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('node_id'),
                    $query->bindValue($locationId)
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        if ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return $row;
        }

        throw new NotFound('trash', $locationId);
    }

    /**
     * List trashed items.
     *
     * @param int $offset
     * @param int $limit
     * @param array $sort
     *
     * @return array
     */
    public function listTrashed($offset, $limit, array $sort = null)
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select('*')
            ->from($this->handler->quoteTable('ezcontentobject_trash'));

        $sort = $sort ?: [];
        foreach ($sort as $condition) {
            $sortDirection = $condition->direction === Query::SORT_ASC ? SelectQuery::ASC : SelectQuery::DESC;
            switch (true) {
                case $condition instanceof SortClause\Location\Depth:
                    $query->orderBy('depth', $sortDirection);
                    break;

                case $condition instanceof SortClause\Location\Path:
                    $query->orderBy('path_string', $sortDirection);
                    break;

                case $condition instanceof SortClause\Location\Priority:
                    $query->orderBy('priority', $sortDirection);
                    break;

                case $condition instanceof SortClause\Trash\DateTrashed:
                    $query->orderBy('trashed', $sortDirection);
                    break;

                default:
                    // Only handle location related sort clauses. The others
                    // require data aggregation which is not sensible here.
                    // Since also criteria are yet ignored, because they are
                    // simply not used yet in eZ Publish, we skip that for now.
                    throw new RuntimeException('Unhandled sort clause: ' . get_class($condition));
            }
        }

        if ($limit !== null) {
            $query->limit($limit, $offset);
        }

        $statement = $query->prepare();
        $statement->execute();

        $rows = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function countTrashed(): int
    {
        $dbPlatform = $this->connection->getDatabasePlatform();
        $query = $this->connection->createQueryBuilder()
            ->select($dbPlatform->getCountExpression('node_id'))
            ->from('ezcontentobject_trash');

        return $query->execute()->fetchColumn();
    }

    /**
     * Removes every entries in the trash.
     * Will NOT remove associated content objects nor attributes.
     *
     * Basically truncates ezcontentobject_trash table.
     */
    public function cleanupTrash()
    {
        $query = $this->handler->createDeleteQuery();
        $query->deleteFrom('ezcontentobject_trash');
        $query->prepare()->execute();
    }

    /**
     * Removes trashed element identified by $id from trash.
     * Will NOT remove associated content object nor attributes.
     *
     * @param int $id The trashed location Id
     */
    public function removeElementFromTrash($id)
    {
        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom('ezcontentobject_trash')
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('node_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Set section on all content objects in the subtree.
     *
     * @param string $pathString
     * @param int $sectionId
     *
     * @return bool
     */
    public function setSectionForSubtree($pathString, $sectionId)
    {
        $selectContentIdsQuery = $this->connection->createQueryBuilder();
        $selectContentIdsQuery
            ->select('t.contentobject_id')
            ->from('ezcontentobject_tree', 't')
            ->where(
                $selectContentIdsQuery->expr()->like(
                    't.path_string',
                    $selectContentIdsQuery->createPositionalParameter("{$pathString}%")
                )
            );

        $contentIds = array_map(
            'intval',
            $selectContentIdsQuery->execute()->fetchAll(PDO::FETCH_COLUMN)
        );

        if (empty($contentIds)) {
            return false;
        }

        $updateSectionQuery = $this->connection->createQueryBuilder();
        $updateSectionQuery
            ->update('ezcontentobject')
            ->set(
                'section_id',
                $updateSectionQuery->createPositionalParameter($sectionId, PDO::PARAM_INT)
            )
            ->where(
                $updateSectionQuery->expr()->in(
                    'id',
                    $contentIds
                )
            );
        $affectedRows = $updateSectionQuery->execute();

        return $affectedRows > 0;
    }

    /**
     * Returns how many locations given content object identified by $contentId has.
     *
     * @param int $contentId
     *
     * @return int
     */
    public function countLocationsByContentId($contentId)
    {
        $q = $this->handler->createSelectQuery();
        $q
            ->select(
                $q->alias($q->expr->count('*'), 'count')
            )
            ->from($this->handler->quoteTable('ezcontentobject_tree'))
            ->where(
                $q->expr->eq(
                    $this->handler->quoteColumn('contentobject_id'),
                    $q->bindValue($contentId, null, \PDO::PARAM_INT)
                )
            );
        $stmt = $q->prepare();
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return (int)$res[0]['count'];
    }

    /**
     * Changes main location of content identified by given $contentId to location identified by given $locationId.
     *
     * Updates ezcontentobject_tree table for the given $contentId and eznode_assignment table for the given
     * $contentId, $parentLocationId and $versionNo
     *
     * @param mixed $contentId
     * @param mixed $locationId
     * @param mixed $versionNo version number, needed to update eznode_assignment table
     * @param mixed $parentLocationId parent location of location identified by $locationId, needed to update
     *        eznode_assignment table
     */
    public function changeMainLocation($contentId, $locationId, $versionNo, $parentLocationId)
    {
        // Update ezcontentobject_tree table
        $q = $this->handler->createUpdateQuery();
        $q->update(
            $this->handler->quoteTable('ezcontentobject_tree')
        )->set(
            $this->handler->quoteColumn('main_node_id'),
            $q->bindValue($locationId, null, \PDO::PARAM_INT)
        )->where(
            $q->expr->eq(
                $this->handler->quoteColumn('contentobject_id'),
                $q->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );
        $q->prepare()->execute();

        // Erase is_main in eznode_assignment table
        $q = $this->handler->createUpdateQuery();
        $q->update(
            $this->handler->quoteTable('eznode_assignment')
        )->set(
            $this->handler->quoteColumn('is_main'),
            $q->bindValue(0, null, \PDO::PARAM_INT)
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->handler->quoteColumn('contentobject_id'),
                    $q->bindValue($contentId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->handler->quoteColumn('contentobject_version'),
                    $q->bindValue($versionNo, null, \PDO::PARAM_INT)
                ),
                $q->expr->neq(
                    $this->handler->quoteColumn('parent_node'),
                    $q->bindValue($parentLocationId, null, \PDO::PARAM_INT)
                )
            )
        );
        $q->prepare()->execute();

        // Set new is_main in eznode_assignment table
        $q = $this->handler->createUpdateQuery();
        $q->update(
            $this->handler->quoteTable('eznode_assignment')
        )->set(
            $this->handler->quoteColumn('is_main'),
            $q->bindValue(1, null, \PDO::PARAM_INT)
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->handler->quoteColumn('contentobject_id'),
                    $q->bindValue($contentId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->handler->quoteColumn('contentobject_version'),
                    $q->bindValue($versionNo, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->handler->quoteColumn('parent_node'),
                    $q->bindValue($parentLocationId, null, \PDO::PARAM_INT)
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Get the total number of all Locations, except the Root node.
     *
     * @see loadAllLocationsData
     *
     * @return int
     */
    public function countAllLocations()
    {
        $query = $this->getAllLocationsQueryBuilder(['count(node_id)']);

        $statement = $query->execute();

        return (int) $statement->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Load data of every Location, except the Root node.
     *
     * @param int $offset Paginator offset
     * @param int $limit Paginator limit
     *
     * @return array
     */
    public function loadAllLocationsData($offset, $limit)
    {
        $query = $this
            ->getAllLocationsQueryBuilder(
                [
                    'node_id',
                    'priority',
                    'is_hidden',
                    'is_invisible',
                    'remote_id',
                    'contentobject_id',
                    'parent_node_id',
                    'path_identification_string',
                    'path_string',
                    'depth',
                    'sort_field',
                    'sort_order',
                ]
            )
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('depth', 'ASC')
            ->addOrderBy('node_id', 'ASC')
        ;

        $statement = $query->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Query Builder for fetching data of all Locations except the Root node.
     *
     * @todo Align with createNodeQueryBuilder, removing the need for both(?)
     *
     * @param array $columns list of columns to fetch
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getAllLocationsQueryBuilder(array $columns)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($columns)
            ->from('ezcontentobject_tree')
            ->where($query->expr()->neq('node_id', 'parent_node_id'))
        ;

        return $query;
    }

    /**
     * Create QueryBuilder for selecting node data.
     *
     * @param array|null $translations Filters on language mask of content if provided.
     * @param bool $useAlwaysAvailable Respect always available flag on content when filtering on $translations.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createNodeQueryBuilder(array $translations = null, bool $useAlwaysAvailable = true): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('t.*')
            ->from('ezcontentobject_tree', 't');

        if (!empty($translations)) {
            $dbPlatform = $this->connection->getDatabasePlatform();
            $expr = $queryBuilder->expr();
            $mask = $this->languageMaskGenerator->generateLanguageMaskFromLanguageCodes(
                $translations,
                $useAlwaysAvailable
            );

            $queryBuilder->leftJoin(
                't',
                'ezcontentobject',
                'c',
                $expr->eq('t.contentobject_id', 'c.id')
            );

            $queryBuilder->where(
                $expr->orX(
                    $expr->gt(
                        $dbPlatform->getBitAndComparisonExpression('c.language_mask', $mask),
                        0
                    ),
                    // Root location doesn't have language mask
                    $expr->eq(
                        't.node_id', 't.parent_node_id'
                    )
                )
            );
        }

        return $queryBuilder;
    }
}
