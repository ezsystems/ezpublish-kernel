<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use RuntimeException;
use PDO;
use function time;

/**
 * Location gateway implementation using the Doctrine database.
 *
 * @internal Gateway implementation is considered internal. Use Persistence Location Handler instead.
 *
 * @see \eZ\Publish\SPI\Persistence\Content\Location\Handler
 */
final class DoctrineDatabase extends Gateway
{
    private const SORT_CLAUSE_TARGET_MAP = [
        'location_depth' => 'depth',
        'location_priority' => 'priority',
        'location_path' => 'path_string',
    ];

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator */
    private $languageMaskGenerator;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(Connection $connection, MaskGenerator $languageMaskGenerator)
    {
        $this->connection = $connection;
        $this->dbPlatform = $this->connection->getDatabasePlatform();
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    public function getBasicNodeData(
        int $nodeId,
        array $translations = null,
        bool $useAlwaysAvailable = true
    ): array {
        $query = $this->createNodeQueryBuilder(['t.*'], $translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->eq('t.node_id', $query->createNamedParameter($nodeId, ParameterType::INTEGER))
        );

        if ($row = $query->execute()->fetch(FetchMode::ASSOCIATIVE)) {
            return $row;
        }

        throw new NotFound('location', $nodeId);
    }

    public function getNodeDataList(array $locationIds, array $translations = null, bool $useAlwaysAvailable = true): iterable
    {
        $query = $this->createNodeQueryBuilder(['t.*'], $translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->in(
                't.node_id',
                $query->createNamedParameter($locationIds, Connection::PARAM_INT_ARRAY)
            )
        );

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getBasicNodeDataByRemoteId(
        string $remoteId,
        array $translations = null,
        bool $useAlwaysAvailable = true
    ): array {
        $query = $this->createNodeQueryBuilder(['t.*'], $translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->eq('t.remote_id', $query->createNamedParameter($remoteId, ParameterType::STRING))
        );

        if ($row = $query->execute()->fetch(FetchMode::ASSOCIATIVE)) {
            return $row;
        }

        throw new NotFound('location', $remoteId);
    }

    public function loadLocationDataByContent(int $contentId, ?int $rootLocationId = null): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from(self::CONTENT_TREE_TABLE, 't')
            ->where(
                $query->expr()->eq(
                    't.contentobject_id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            );

        if ($rootLocationId !== null) {
            $query
                ->andWhere(
                    $this->getSubtreeLimitationExpression($query, $rootLocationId)
                )
            ;
        }

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function loadParentLocationsDataForDraftContent(int $contentId): array
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select('DISTINCT t.*')
            ->from(self::CONTENT_TREE_TABLE, 't')
            ->innerJoin(
                't',
                'eznode_assignment',
                'a',
                $expr->andX(
                    $expr->eq(
                        't.node_id',
                        'a.parent_node'
                    ),
                    $expr->eq(
                        'a.contentobject_id',
                        $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                    ),
                    $expr->eq(
                        'a.op_code',
                        $query->createPositionalParameter(
                            self::NODE_ASSIGNMENT_OP_CODE_CREATE,
                            ParameterType::INTEGER
                        )
                    )
                )
            )
            ->innerJoin(
                'a',
                'ezcontentobject',
                'c',
                $expr->andX(
                    $expr->eq(
                        'a.contentobject_id',
                        'c.id'
                    ),
                    $expr->eq(
                        'c.status',
                        $query->createPositionalParameter(
                            ContentInfo::STATUS_DRAFT,
                            ParameterType::INTEGER
                        )
                    )
                )
            );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getSubtreeContent(int $sourceId, bool $onlyIds = false): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($onlyIds ? 'node_id, contentobject_id, depth' : '*')
            ->from(self::CONTENT_TREE_TABLE, 't')
            ->where($this->getSubtreeLimitationExpression($query, $sourceId))
            ->orderBy('t.depth')
            ->addOrderBy('t.node_id');
        $statement = $query->execute();

        $results = $statement->fetchAll($onlyIds ? (FetchMode::COLUMN | PDO::FETCH_GROUP) : FetchMode::ASSOCIATIVE);
        // array_map() is used to to map all elements stored as $results[$i][0] to $results[$i]
        return $onlyIds ? array_map('reset', $results) : $results;
    }

    /**
     * Return constraint which limits the given $query to the subtree starting at $rootLocationId.
     */
    private function getSubtreeLimitationExpression(
        QueryBuilder $query,
        int $rootLocationId
    ): string {
        return $query->expr()->like(
            't.path_string',
            $query->createPositionalParameter(
                '%/' . ((string)$rootLocationId) . '/%',
                ParameterType::STRING
            )
        );
    }

    public function getChildren(int $locationId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('*')->from(
            self::CONTENT_TREE_TABLE
        )->where(
            $query->expr()->eq(
                'ezcontentobject_tree.parent_node_id',
                $query->createPositionalParameter($locationId, ParameterType::INTEGER)
            )
        );
        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    private function getSubtreeNodesData(string $pathString): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                'node_id',
                'parent_node_id',
                'path_string',
                'path_identification_string',
                'is_hidden'
            )
            ->from(self::CONTENT_TREE_TABLE)
            ->where(
                $query->expr()->like(
                    'path_string',
                    $query->createPositionalParameter($pathString . '%', ParameterType::STRING)
                )
            );
        $statement = $query->execute();

        return $statement->fetchAll();
    }

    public function moveSubtreeNodes(array $sourceNodeData, array $destinationNodeData): void
    {
        $fromPathString = $sourceNodeData['path_string'];

        $rows = $this->getSubtreeNodesData($fromPathString);

        $oldParentPathString = implode('/', array_slice(explode('/', $fromPathString), 0, -2)) . '/';
        $oldParentPathIdentificationString = implode(
            '/',
            array_slice(explode('/', $sourceNodeData['path_identification_string']), 0, -1)
        );

        $hiddenNodeIds = $this->getHiddenNodeIds($rows);
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

            $this->moveSingleSubtreeNode(
                (int)$row['node_id'],
                $sourceNodeData,
                $destinationNodeData,
                $newPathString,
                $newPathIdentificationString,
                $newParentId,
                $hiddenNodeIds
            );
        }
    }

    private function getHiddenNodeIds(array $rows): array
    {
        return array_map(
            static function (array $row) {
                return (int)$row['node_id'];
            },
            array_filter(
                $rows,
                static function (array $row) {
                    return !empty($row['is_hidden']);
                }
            )
        );
    }

    /**
     * @param int[] $hiddenNodeIds
     */
    private function isHiddenByParent(string $pathString, array $hiddenNodeIds): bool
    {
        $parentNodeIds = array_map('intval', explode('/', trim($pathString, '/')));
        array_pop($parentNodeIds); // remove self
        foreach ($parentNodeIds as $parentNodeId) {
            if (in_array($parentNodeId, $hiddenNodeIds, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $sourceNodeData
     * @param array $destinationNodeData
     * @param int[] $hiddenNodeIds
     */
    private function moveSingleSubtreeNode(
        int $nodeId,
        array $sourceNodeData,
        array $destinationNodeData,
        string $newPathString,
        string $newPathIdentificationString,
        int $newParentId,
        array $hiddenNodeIds
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_TREE_TABLE)
            ->set(
                'path_string',
                $query->createPositionalParameter($newPathString, ParameterType::STRING)
            )
            ->set(
                'path_identification_string',
                $query->createPositionalParameter(
                    $newPathIdentificationString,
                    ParameterType::STRING
                )
            )
            ->set(
                'depth',
                $query->createPositionalParameter(
                    substr_count($newPathString, '/') - 2,
                    ParameterType::INTEGER
                )
            )
            ->set(
                'parent_node_id',
                $query->createPositionalParameter($newParentId, ParameterType::INTEGER)
            );

        if ($destinationNodeData['is_hidden'] || $destinationNodeData['is_invisible']) {
            // CASE 1: Mark whole tree as invisible if destination is invisible and/or hidden
            $query->set(
                'is_invisible',
                $query->createPositionalParameter(1, ParameterType::INTEGER)
            );
        } elseif (!$sourceNodeData['is_hidden'] && $sourceNodeData['is_invisible']) {
            // CASE 2: source is only invisible, we will need to re-calculate whole moved tree visibility
            $query->set(
                'is_invisible',
                $query->createPositionalParameter(
                    $this->isHiddenByParent($newPathString, $hiddenNodeIds) ? 1 : 0,
                    ParameterType::INTEGER
                )
            );
        }

        $query->where(
            $query->expr()->eq(
                'node_id',
                $query->createPositionalParameter($nodeId, ParameterType::INTEGER)
            )
        );
        $query->execute();
    }

    public function updateSubtreeModificationTime(string $pathString, ?int $timestamp = null): void
    {
        $nodes = array_filter(explode('/', $pathString));
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_TREE_TABLE)
            ->set(
                'modified_subnode',
                $query->createPositionalParameter(
                    $timestamp ?: time(), ParameterType::INTEGER
                )
            )
            ->where(
                $query->expr()->in(
                    'node_id',
                    $nodes
                )
            );
        $query->execute();
    }

    public function hideSubtree(string $pathString): void
    {
        $this->setNodeWithChildrenInvisible($pathString);
        $this->setNodeHidden($pathString);
    }

    public function setNodeWithChildrenInvisible(string $pathString): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_TREE_TABLE)
            ->set(
                'is_invisible',
                $query->createPositionalParameter(1, ParameterType::INTEGER)
            )
            ->set(
                'modified_subnode',
                $query->createPositionalParameter(time(), ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->like(
                    'path_string',
                    $query->createPositionalParameter($pathString . '%', ParameterType::STRING)
                )
            );

        $query->execute();
    }

    public function setNodeHidden(string $pathString): void
    {
        $this->setNodeHiddenStatus($pathString, true);
    }

    private function setNodeHiddenStatus(string $pathString, bool $isHidden): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_TREE_TABLE)
            ->set(
                'is_hidden',
                $query->createPositionalParameter((int) $isHidden, ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->eq(
                    'path_string',
                    $query->createPositionalParameter($pathString, ParameterType::STRING)
                )
            );

        $query->execute();
    }

    public function unHideSubtree(string $pathString): void
    {
        $this->setNodeUnhidden($pathString);
        $this->setNodeWithChildrenVisible($pathString);
    }

    public function setNodeWithChildrenVisible(string $pathString): void
    {
        // Check if any parent nodes are explicitly hidden
        if ($this->isAnyNodeInPathExplicitlyHidden($pathString)) {
            // There are parent nodes set hidden, so that we can skip marking
            // something visible again.
            return;
        }

        // Find nodes of explicitly hidden subtrees in the subtree which
        // should remain unhidden
        $hiddenSubtrees = $this->loadHiddenSubtreesByPath($pathString);

        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->update(self::CONTENT_TREE_TABLE)
            ->set(
                'is_invisible',
                $query->createPositionalParameter(0, ParameterType::INTEGER)
            )
            ->set(
                'modified_subnode',
                $query->createPositionalParameter(time(), ParameterType::INTEGER)
            );

        // Build where expression selecting the nodes, which should not be made hidden
        $query
            ->where(
                $expr->like(
                    'path_string',
                    $query->createPositionalParameter($pathString . '%', ParameterType::STRING)
                )
            );
        if (count($hiddenSubtrees) > 0) {
            foreach ($hiddenSubtrees as $subtreePathString) {
                $query
                    ->andWhere(
                        $expr->notLike(
                            'path_string',
                            $query->createPositionalParameter(
                                $subtreePathString . '%',
                                ParameterType::STRING
                            )
                        )
                    );
            }
        }

        $query->execute();
    }

    private function isAnyNodeInPathExplicitlyHidden(string $pathString): bool
    {
        $query = $this->buildHiddenSubtreeQuery(
            $this->dbPlatform->getCountExpression('path_string')
        );
        $expr = $query->expr();
        $query
            ->andWhere(
                $expr->in(
                    't.node_id',
                    $query->createPositionalParameter(
                        array_filter(explode('/', $pathString)),
                        Connection::PARAM_INT_ARRAY
                    )
                )
            );
        $count = (int)$query->execute()->fetchColumn();

        return $count > 0;
    }

    /**
     * @return array list of path strings
     */
    private function loadHiddenSubtreesByPath(string $pathString): array
    {
        $query = $this->buildHiddenSubtreeQuery('path_string');
        $expr = $query->expr();
        $query
            ->andWhere(
                $expr->like(
                    'path_string',
                    $query->createPositionalParameter(
                        $pathString . '%',
                        ParameterType::STRING
                    )
                )
            );
        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::COLUMN);
    }

    private function buildHiddenSubtreeQuery(string $selectExpr): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select($selectExpr)
            ->from(self::CONTENT_TREE_TABLE, 't')
            ->leftJoin('t', 'ezcontentobject', 'c', 't.contentobject_id = c.id')
            ->where(
                $expr->orX(
                    $expr->eq(
                        't.is_hidden',
                        $query->createPositionalParameter(1, ParameterType::INTEGER)
                    ),
                    $expr->eq(
                        'c.is_hidden',
                        $query->createPositionalParameter(1, ParameterType::INTEGER)
                    )
                )
            );

        return $query;
    }

    public function setNodeUnhidden(string $pathString): void
    {
        $this->setNodeHiddenStatus($pathString, false);
    }

    public function swap(int $locationId1, int $locationId2): bool
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr = $queryBuilder->expr();
        $queryBuilder
            ->select('node_id', 'main_node_id', 'contentobject_id', 'contentobject_version')
            ->from(self::CONTENT_TREE_TABLE)
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
            ->update(self::CONTENT_TREE_TABLE)
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

    public function create(CreateStruct $createStruct, array $parentNode): Location
    {
        $location = $this->insertLocationIntoContentTree($createStruct, $parentNode);

        $mainLocationId = $createStruct->mainLocationId === true ? $location->id : $createStruct->mainLocationId;
        $location->pathString = $parentNode['path_string'] . $location->id . '/';
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_TREE_TABLE)
            ->set(
                'path_string',
                $query->createPositionalParameter($location->pathString, ParameterType::STRING)
            )
            ->set(
                'main_node_id',
                $query->createPositionalParameter($mainLocationId, ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->eq(
                    'node_id',
                    $query->createPositionalParameter($location->id, ParameterType::INTEGER)
                )
            );

        $query->execute();

        return $location;
    }

    public function createNodeAssignment(
        CreateStruct $createStruct,
        int $parentNodeId,
        int $type = self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP
    ): void {
        $isMain = ($createStruct->mainLocationId === true ? 1 : 0);

        $query = $this->connection->createQueryBuilder();
        $query
            ->insert('eznode_assignment')
            ->values(
                [
                    'contentobject_id' => ':contentobject_id',
                    'contentobject_version' => ':contentobject_version',
                    'from_node_id' => ':from_node_id',
                    'is_main' => ':is_main',
                    'op_code' => ':op_code',
                    'parent_node' => ':parent_node',
                    'parent_remote_id' => ':parent_remote_id',
                    'remote_id' => ':remote_id',
                    'sort_field' => ':sort_field',
                    'sort_order' => ':sort_order',
                    'priority' => ':priority',
                    'is_hidden' => ':is_hidden',
                ]
            )
            ->setParameters(
                [
                    'contentobject_id' => $createStruct->contentId,
                    'contentobject_version' => $createStruct->contentVersion,
                    // from_node_id: unused field
                    'from_node_id' => 0,
                    // is_main: changed by the business layer, later
                    'is_main' => $isMain,
                    'op_code' => $type,
                    'parent_node' => $parentNodeId,
                    // parent_remote_id column should contain the remote id of the corresponding Location
                    'parent_remote_id' => $createStruct->remoteId,
                    // remote_id column should contain the remote id of the node assignment itself,
                    // however this was never implemented completely in Legacy Stack, so we just set
                    // it to default value '0'
                    'remote_id' => '0',
                    'sort_field' => $createStruct->sortField,
                    'sort_order' => $createStruct->sortOrder,
                    'priority' => $createStruct->priority,
                    'is_hidden' => $createStruct->hidden,
                ],
                [
                    'contentobject_id' => ParameterType::INTEGER,
                    'contentobject_version' => ParameterType::INTEGER,
                    'from_node_id' => ParameterType::INTEGER,
                    'is_main' => ParameterType::INTEGER,
                    'op_code' => ParameterType::INTEGER,
                    'parent_node' => ParameterType::INTEGER,
                    'parent_remote_id' => ParameterType::STRING,
                    'remote_id' => ParameterType::STRING,
                    'sort_field' => ParameterType::INTEGER,
                    'sort_order' => ParameterType::INTEGER,
                    'priority' => ParameterType::INTEGER,
                    'is_hidden' => ParameterType::INTEGER,
                ]
            );
        $query->execute();
    }

    public function deleteNodeAssignment(int $contentId, ?int $versionNo = null): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->delete(
            'eznode_assignment'
        )->where(
            $query->expr()->eq(
                'contentobject_id',
                $query->createPositionalParameter($contentId, ParameterType::INTEGER)
            )
        );
        if (isset($versionNo)) {
            $query->andWhere(
                $query->expr()->eq(
                    'contentobject_version',
                    $query->createPositionalParameter($versionNo, ParameterType::INTEGER)
                )
            );
        }
        $query->execute();
    }

    public function updateNodeAssignment(
        int $contentObjectId,
        int $oldParent,
        int $newParent,
        int $opcode
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eznode_assignment')
            ->set(
                'parent_node',
                $query->createPositionalParameter($newParent, ParameterType::INTEGER)
            )
            ->set(
                'op_code',
                $query->createPositionalParameter($opcode, ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->eq(
                    'contentobject_id',
                    $query->createPositionalParameter(
                        $contentObjectId,
                        ParameterType::INTEGER
                    )
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'parent_node',
                    $query->createPositionalParameter(
                        $oldParent,
                        ParameterType::INTEGER
                    )
                )
            );
        $query->execute();
    }

    public function createLocationsFromNodeAssignments(int $contentId, int $versionNo): void
    {
        // select all node assignments with OP_CODE_CREATE (3) for this content
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('eznode_assignment')
            ->where(
                $query->expr()->eq(
                    'contentobject_id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'contentobject_version',
                    $query->createPositionalParameter($versionNo, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'op_code',
                    $query->createPositionalParameter(
                        self::NODE_ASSIGNMENT_OP_CODE_CREATE,
                        ParameterType::INTEGER
                    )
                )
            )
            ->orderBy('id');
        $statement = $query->execute();

        // convert all these assignments to nodes

        while ($row = $statement->fetch(FetchMode::ASSOCIATIVE)) {
            $isMain = (bool)$row['is_main'];
            // set null for main to indicate that new Location ID is required
            $mainLocationId = $isMain ? null : $this->getMainNodeId($contentId);

            $parentLocationData = $this->getBasicNodeData((int)$row['parent_node']);
            $isInvisible = $row['is_hidden'] || $parentLocationData['is_hidden'] || $parentLocationData['is_invisible'];
            $this->create(
                new CreateStruct(
                    [
                        'contentId' => $row['contentobject_id'],
                        'contentVersion' => $row['contentobject_version'],
                        // BC layer: for CreateStruct "true" means that a main Location should be created
                        'mainLocationId' => $mainLocationId ?? true,
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
                (int)$row['contentobject_id'],
                (int)$row['parent_node'],
                (int)$row['parent_node'],
                self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP
            );
        }
    }

    public function updateLocationsContentVersionNo(int $contentId, int $versionNo): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->update(
            self::CONTENT_TREE_TABLE
        )->set(
            'contentobject_version',
            $query->createPositionalParameter($versionNo, ParameterType::INTEGER)
        )->where(
            $query->expr()->eq(
                'contentobject_id',
                $contentId
            )
        );
        $query->execute();
    }

    /**
     * Search for the main nodeId of $contentId.
     */
    private function getMainNodeId(int $contentId): ?int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('node_id')
            ->from(self::CONTENT_TREE_TABLE)
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq(
                        'contentobject_id',
                        $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                    ),
                    $query->expr()->eq(
                        'node_id',
                        'main_node_id'
                    )
                )
            );
        $statement = $query->execute();

        $result = $statement->fetchColumn();

        return false !== $result ? (int)$result : null;
    }

    /**
     * Updates an existing location.
     *
     * Will not throw anything if location id is invalid or no entries are affected.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct $location
     * @param int $locationId
     */
    public function update(UpdateStruct $location, $locationId): void
    {
        $query = $this->connection->createQueryBuilder();

        $query
            ->update(self::CONTENT_TREE_TABLE)
            ->set(
                'priority',
                $query->createPositionalParameter($location->priority, ParameterType::INTEGER)
            )
            ->set(
                'remote_id',
                $query->createPositionalParameter($location->remoteId, ParameterType::STRING)
            )
            ->set(
                'sort_order',
                $query->createPositionalParameter($location->sortOrder, ParameterType::INTEGER)
            )
            ->set(
                'sort_field',
                $query->createPositionalParameter($location->sortField, ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->eq(
                    'node_id',
                    $locationId
                )
            );
        $query->execute();
    }

    public function updatePathIdentificationString($locationId, $parentLocationId, $text): void
    {
        $parentData = $this->getBasicNodeData($parentLocationId);

        $newPathIdentificationString = empty($parentData['path_identification_string']) ?
            $text :
            $parentData['path_identification_string'] . '/' . $text;

        $query = $this->connection->createQueryBuilder();
        $query->update(
            self::CONTENT_TREE_TABLE
        )->set(
            'path_identification_string',
            $query->createPositionalParameter($newPathIdentificationString, ParameterType::STRING)
        )->where(
            $query->expr()->eq(
                'node_id',
                $query->createPositionalParameter($locationId, ParameterType::INTEGER)
            )
        );
        $query->execute();
    }

    /**
     * Deletes ezcontentobject_tree row for given $locationId (node_id).
     *
     * @param mixed $locationId
     */
    public function removeLocation($locationId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->delete(
            self::CONTENT_TREE_TABLE
        )->where(
            $query->expr()->eq(
                'node_id',
                $query->createPositionalParameter($locationId, ParameterType::INTEGER)
            )
        );
        $query->execute();
    }

    /**
     * Return data of the next in line node to be set as a new main node.
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
    public function getFallbackMainNodeData($contentId, $locationId): array
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select(
                'node_id',
                'contentobject_version',
                'parent_node_id'
            )
            ->from(self::CONTENT_TREE_TABLE)
            ->where(
                $expr->eq(
                    'contentobject_id',
                    $query->createPositionalParameter(
                        $contentId,
                        ParameterType::INTEGER
                    )
                )
            )
            ->andWhere(
                $expr->neq(
                    'node_id',
                    $query->createPositionalParameter(
                        $locationId,
                        ParameterType::INTEGER
                    )
                )
            )
            ->orderBy('node_id', 'ASC')
            ->setMaxResults(1);

        $statement = $query->execute();

        return $statement->fetch(FetchMode::ASSOCIATIVE);
    }

    public function trashLocation(int $locationId): void
    {
        $locationRow = $this->getBasicNodeData($locationId);

        $query = $this->connection->createQueryBuilder();
        $query->insert('ezcontentobject_trash');

        unset($locationRow['contentobject_is_published']);
        $locationRow['trashed'] = time();
        foreach ($locationRow as $key => $value) {
            $query->setValue($key, $query->createPositionalParameter($value));
        }

        $query->execute();

        $this->removeLocation($locationRow['node_id']);
        $this->setContentStatus((int)$locationRow['contentobject_id'], ContentInfo::STATUS_TRASHED);
    }

    public function untrashLocation(int $locationId, ?int $newParentId = null): Location
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
            $this->getBasicNodeData($newParentId ?? (int)$row['parent_node_id'])
        );

        $this->removeElementFromTrash($locationId);
        $this->setContentStatus((int)$row['contentobject_id'], ContentInfo::STATUS_PUBLISHED);

        return $newLocation;
    }

    private function setContentStatus(int $contentId, int $status): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->update(
            'ezcontentobject'
        )->set(
            'status',
            $query->createPositionalParameter($status, ParameterType::INTEGER)
        )->where(
            $query->expr()->eq(
                'id',
                $query->createPositionalParameter($contentId, ParameterType::INTEGER)
            )
        );
        $query->execute();
    }

    public function loadTrashByLocation(int $locationId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('ezcontentobject_trash')
            ->where(
                $query->expr()->eq(
                    'node_id',
                    $query->createPositionalParameter($locationId, ParameterType::INTEGER)
                )
            );
        $statement = $query->execute();

        if ($row = $statement->fetch(FetchMode::ASSOCIATIVE)) {
            return $row;
        }

        throw new NotFound('trash', $locationId);
    }

    public function listTrashed(int $offset, ?int $limit, array $sort = null): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('ezcontentobject_trash');

        $sort = $sort ?: [];
        foreach ($sort as $condition) {
            if (!isset(self::SORT_CLAUSE_TARGET_MAP[$condition->target])) {
                // Only handle location related sort clause targets. The others
                // require data aggregation which is not sensible here.
                // Since also criteria are yet ignored, because they are
                // simply not used yet in eZ Platform, we skip that for now.
                throw new RuntimeException('Unhandled sort clause: ' . get_class($condition));
            }
            $query->addOrderBy(
                self::SORT_CLAUSE_TARGET_MAP[$condition->target],
                $condition->direction === Query::SORT_ASC ? 'ASC' : 'DESC'
            );
        }

        if ($limit !== null) {
            $query->setMaxResults($limit);
            $query->setFirstResult($offset);
        }

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function countTrashed(): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select($this->dbPlatform->getCountExpression('node_id'))
            ->from('ezcontentobject_trash');

        return (int)$query->execute()->fetchColumn();
    }

    /**
     * Removes every entries in the trash.
     * Will NOT remove associated content objects nor attributes.
     *
     * Basically truncates ezcontentobject_trash table.
     */
    public function cleanupTrash(): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->delete('ezcontentobject_trash');
        $query->execute();
    }

    public function removeElementFromTrash(int $id): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('ezcontentobject_trash')
            ->where(
                $query->expr()->eq(
                    'node_id',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    public function setSectionForSubtree(string $pathString, int $sectionId): bool
    {
        $selectContentIdsQuery = $this->connection->createQueryBuilder();
        $selectContentIdsQuery
            ->select('t.contentobject_id')
            ->from(self::CONTENT_TREE_TABLE, 't')
            ->where(
                $selectContentIdsQuery->expr()->like(
                    't.path_string',
                    $selectContentIdsQuery->createPositionalParameter("{$pathString}%")
                )
            );

        $contentIds = array_map(
            'intval',
            $selectContentIdsQuery->execute()->fetchAll(FetchMode::COLUMN)
        );

        if (empty($contentIds)) {
            return false;
        }

        $updateSectionQuery = $this->connection->createQueryBuilder();
        $updateSectionQuery
            ->update('ezcontentobject')
            ->set(
                'section_id',
                $updateSectionQuery->createPositionalParameter($sectionId, ParameterType::INTEGER)
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

    public function countLocationsByContentId(int $contentId): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->dbPlatform->getCountExpression('*')
            )
            ->from(self::CONTENT_TREE_TABLE)
            ->where(
                $query->expr()->eq(
                    'contentobject_id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            );
        $stmt = $query->execute();

        return (int)$stmt->fetchColumn();
    }

    public function changeMainLocation(
        int $contentId,
        int $locationId,
        int $versionNo,
        int $parentLocationId
    ): void {
        // Update ezcontentobject_tree table
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_TREE_TABLE)
            ->set(
                'main_node_id',
                $query->createPositionalParameter($locationId, ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->eq(
                    'contentobject_id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            )
        ;
        $query->execute();

        // Update is_main in eznode_assignment table
        $this->setIsMainForContentVersionParentNodeAssignment(
            $contentId,
            $versionNo,
            $parentLocationId
        );
    }

    public function countAllLocations(): int
    {
        $query = $this->createNodeQueryBuilder(['count(node_id)']);
        // exclude absolute Root Location (not to be confused with SiteAccess Tree Root)
        $query->where($query->expr()->neq('node_id', 'parent_node_id'));

        $statement = $query->execute();

        return (int) $statement->fetch(FetchMode::COLUMN);
    }

    public function loadAllLocationsData(int $offset, int $limit): array
    {
        $query = $this
            ->createNodeQueryBuilder(
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
            );
        $query
            // exclude absolute Root Location (not to be confused with SiteAccess Tree Root)
            ->where($query->expr()->neq('node_id', 'parent_node_id'))
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('depth', 'ASC')
            ->addOrderBy('node_id', 'ASC')
        ;

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Create QueryBuilder for selecting Location (node) data.
     *
     * @param array $columns column or expression list
     * @param array|null $translations Filters on language mask of content if provided.
     * @param bool $useAlwaysAvailable Respect always available flag on content when filtering on $translations.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createNodeQueryBuilder(
        array $columns,
        array $translations = null,
        bool $useAlwaysAvailable = true
    ): QueryBuilder {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select($columns)
            ->from(self::CONTENT_TREE_TABLE, 't')
        ;

        if (!empty($translations)) {
            $this->appendContentItemTranslationsConstraint($queryBuilder, $translations, $useAlwaysAvailable);
        }

        return $queryBuilder;
    }

    private function appendContentItemTranslationsConstraint(
        QueryBuilder $queryBuilder,
        array $translations,
        bool $useAlwaysAvailable
    ): void {
        $expr = $queryBuilder->expr();
        try {
            $mask = $this->languageMaskGenerator->generateLanguageMaskFromLanguageCodes(
                $translations,
                $useAlwaysAvailable
            );
        } catch (NotFoundException $e) {
            return;
        }

        $queryBuilder->leftJoin(
            't',
            'ezcontentobject',
            'c',
            $expr->eq('t.contentobject_id', 'c.id')
        );

        $queryBuilder->andWhere(
            $expr->orX(
                $expr->gt(
                    $this->dbPlatform->getBitAndComparisonExpression('c.language_mask', $mask),
                    0
                ),
                // Root location doesn't have language mask
                $expr->eq(
                    't.node_id', 't.parent_node_id'
                )
            )
        );
    }

    /**
     * Mark eznode_assignment entry, identified by Content ID and Version ID, as main for the given
     * parent Location ID.
     *
     * **NOTE**: The method erases is_main from the other entries related to Content and Version IDs
     */
    private function setIsMainForContentVersionParentNodeAssignment(
        int $contentId,
        int $versionNo,
        int $parentLocationId
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eznode_assignment')
            ->set(
                'is_main',
                // set is_main = 1 only for current parent, set 0 for other entries
                'CASE WHEN parent_node <> :parent_location_id THEN 0 ELSE 1 END'
            )
            ->where('contentobject_id = :content_id')
            ->andWhere('contentobject_version = :version_no')
            ->setParameter('parent_location_id', $parentLocationId, ParameterType::INTEGER)
            ->setParameter('content_id', $contentId, ParameterType::INTEGER)
            ->setParameter('version_no', $versionNo, ParameterType::INTEGER);

        $query->execute();
    }

    /**
     * @param array $parentNode raw Location data
     */
    private function insertLocationIntoContentTree(
        CreateStruct $createStruct,
        array $parentNode
    ): Location {
        $location = new Location();
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::CONTENT_TREE_TABLE)
            ->values(
                [
                    'contentobject_id' => ':content_id',
                    'contentobject_is_published' => ':is_published',
                    'contentobject_version' => ':version_no',
                    'depth' => ':depth',
                    'is_hidden' => ':is_hidden',
                    'is_invisible' => ':is_invisible',
                    'modified_subnode' => ':modified_subnode',
                    'parent_node_id' => ':parent_node_id',
                    'path_string' => ':path_string',
                    'priority' => ':priority',
                    'remote_id' => ':remote_id',
                    'sort_field' => ':sort_field',
                    'sort_order' => ':sort_order',
                ]
            )
            ->setParameters(
                [
                    'content_id' => $location->contentId = $createStruct->contentId,
                    'is_published' => 1,
                    'version_no' => $createStruct->contentVersion,
                    'depth' => $location->depth = $parentNode['depth'] + 1,
                    'is_hidden' => $location->hidden = $createStruct->hidden,
                    'is_invisible' => $location->invisible = $createStruct->invisible,
                    'modified_subnode' => time(),
                    'parent_node_id' => $location->parentId = $parentNode['node_id'],
                    'path_string' => '', // Set later
                    'priority' => $location->priority = $createStruct->priority,
                    'remote_id' => $location->remoteId = $createStruct->remoteId,
                    'sort_field' => $location->sortField = $createStruct->sortField,
                    'sort_order' => $location->sortOrder = $createStruct->sortOrder,
                ],
                [
                    'contentobject_id' => ParameterType::INTEGER,
                    'contentobject_is_published' => ParameterType::INTEGER,
                    'contentobject_version' => ParameterType::INTEGER,
                    'depth' => ParameterType::INTEGER,
                    'is_hidden' => ParameterType::INTEGER,
                    'is_invisible' => ParameterType::INTEGER,
                    'modified_subnode' => ParameterType::INTEGER,
                    'parent_node_id' => ParameterType::INTEGER,
                    'path_string' => ParameterType::STRING,
                    'priority' => ParameterType::INTEGER,
                    'remote_id' => ParameterType::STRING,
                    'sort_field' => ParameterType::INTEGER,
                    'sort_order' => ParameterType::INTEGER,
                ]
            );
        $query->execute();

        $location->id = (int)$this->connection->lastInsertId(self::CONTENT_TREE_SEQ);

        return $location;
    }
}
