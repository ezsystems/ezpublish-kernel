<?php

/**
 * File containing the DoctrineDatabase ObjectState Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;

/**
 * ObjectState Doctrine database Gateway.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $maskGenerator;

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(Connection $connection, MaskGenerator $maskGenerator)
    {
        $this->connection = $connection;
        $this->dbPlatform = $this->connection->getDatabasePlatform();
        $this->maskGenerator = $maskGenerator;
    }

    /**
     * Loads data for an object state.
     *
     * @param mixed $stateId
     *
     * @return array
     */
    public function loadObjectStateData($stateId)
    {
        $query = $this->createObjectStateFindQuery();
        $query->where(
            $query->expr()->eq(
                'state.id',
                $query->createPositionalParameter($stateId, ParameterType::INTEGER)
            )
        );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads data for an object state by identifier.
     *
     * @param string $identifier
     * @param mixed $groupId
     *
     * @return array
     */
    public function loadObjectStateDataByIdentifier($identifier, $groupId)
    {
        $query = $this->createObjectStateFindQuery();
        $query->where(
            $query->expr()->andX(
                $query->expr()->eq(
                    'state.identifier',
                    $query->createPositionalParameter($identifier, ParameterType::STRING)
                ),
                $query->expr()->eq(
                    'state.group_id',
                    $query->createPositionalParameter($groupId, ParameterType::INTEGER)
                )
            )
        );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads data for all object states belonging to group with $groupId ID.
     *
     * @param mixed $groupId
     *
     * @return array
     */
    public function loadObjectStateListData($groupId)
    {
        $query = $this->createObjectStateFindQuery();
        $query->where(
            $query->expr()->eq(
                'state.group_id',
                $query->createPositionalParameter($groupId, ParameterType::INTEGER)
            )
        )->orderBy('state.priority', 'ASC');

        $statement = $query->execute();

        $rows = [];
        while ($row = $statement->fetch(FetchMode::ASSOCIATIVE)) {
            $rows[$row['ezcobj_state_id']][] = $row;
        }

        return array_values($rows);
    }

    /**
     * Loads data for an object state group.
     *
     * @param mixed $groupId
     *
     * @return array
     */
    public function loadObjectStateGroupData($groupId)
    {
        $query = $this->createObjectStateGroupFindQuery();
        $query->where(
            $query->expr()->eq(
                'state_group.id',
                $query->createPositionalParameter($groupId, ParameterType::INTEGER)
            )
        );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads data for an object state group by identifier.
     *
     * @param string $identifier
     *
     * @return array
     */
    public function loadObjectStateGroupDataByIdentifier($identifier)
    {
        $query = $this->createObjectStateGroupFindQuery();
        $query->where(
            $query->expr()->eq(
                'state_group.identifier',
                $query->createPositionalParameter($identifier, ParameterType::STRING)
            )
        );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads data for all object state groups, filtered by $offset and $limit.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function loadObjectStateGroupListData($offset, $limit)
    {
        $query = $this->createObjectStateGroupFindQuery();
        if ($limit > 0) {
            $query->setMaxResults($limit);
            $query->setFirstResult($offset);
        }

        $statement = $query->execute();

        $rows = [];
        while ($row = $statement->fetch(FetchMode::ASSOCIATIVE)) {
            $rows[$row['ezcobj_state_group_id']][] = $row;
        }

        return array_values($rows);
    }

    /**
     * Inserts a new object state into database.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     * @param int $groupId
     */
    public function insertObjectState(ObjectState $objectState, $groupId)
    {
        $maxPriority = $this->getMaxPriorityForObjectStatesInGroup($groupId);

        $objectState->priority = $maxPriority === null ? 0 : (int)$maxPriority + 1;
        $objectState->groupId = (int)$groupId;

        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::OBJECT_STATE_TABLE)
            ->values(
                [
                    'group_id' => $query->createPositionalParameter(
                        $objectState->groupId,
                        ParameterType::INTEGER
                    ),
                    'default_language_id' => $query->createPositionalParameter(
                        $this->maskGenerator->generateLanguageIndicator(
                            $objectState->defaultLanguage,
                            false
                        ),
                        ParameterType::INTEGER
                    ),
                    'identifier' => $query->createPositionalParameter(
                        $objectState->identifier,
                        ParameterType::STRING
                    ),
                    'language_mask' => $query->createPositionalParameter(
                        $this->maskGenerator->generateLanguageMaskFromLanguageCodes(
                            $objectState->languageCodes,
                            true
                        ),
                        ParameterType::INTEGER
                    ),
                    'priority' => $query->createPositionalParameter(
                        $objectState->priority,
                        ParameterType::INTEGER
                    ),
                ]
            );

        $query->execute();

        $objectState->id = (int)$this->connection->lastInsertId(self::OBJECT_STATE_TABLE_SEQ);

        $this->insertObjectStateTranslations($objectState);

        // If this is a first state in group, assign it to all content objects
        if ($maxPriority === null) {
            $this->connection->executeUpdate(
                'INSERT INTO ezcobj_state_link (contentobject_id, contentobject_state_id) ' .
                "SELECT id, {$objectState->id} FROM ezcontentobject"
            );
        }
    }

    /**
     * @param string $tableName
     * @param int $id
     * @param string $identifier
     * @param string $defaultLanguageCode
     * @param string[] $languageCodes
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function updateObjectStateCommonFields(
        string $tableName,
        int $id,
        string $identifier,
        string $defaultLanguageCode,
        array $languageCodes
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update($tableName)
            ->set(
                'default_language_id',
                $query->createPositionalParameter(
                    $this->maskGenerator->generateLanguageIndicator(
                        $defaultLanguageCode,
                        false
                    ),
                    ParameterType::INTEGER
                )
            )
            ->set(
                'identifier',
                $query->createPositionalParameter(
                    $identifier,
                    ParameterType::STRING
                )
            )
            ->set(
                'language_mask',
                $query->createPositionalParameter(
                    $this->maskGenerator->generateLanguageMaskFromLanguageCodes(
                        $languageCodes,
                        true
                    ),
                    ParameterType::INTEGER
                )
            )
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            );

        $query->execute();
    }

    /**
     * Updates the stored object state with provided data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     */
    public function updateObjectState(ObjectState $objectState)
    {
        // First update the state
        $this->updateObjectStateCommonFields(
            self::OBJECT_STATE_TABLE,
            $objectState->id,
            $objectState->identifier,
            $objectState->defaultLanguage,
            $objectState->languageCodes
        );

        // And then refresh object state translations
        // by removing existing ones and adding new ones
        $this->deleteObjectStateTranslations($objectState->id);
        $this->insertObjectStateTranslations($objectState);
    }

    /**
     * Deletes object state identified by $stateId.
     *
     * @param int $stateId
     */
    public function deleteObjectState($stateId)
    {
        $this->deleteObjectStateTranslations($stateId);

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::OBJECT_STATE_TABLE)
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($stateId, ParameterType::INTEGER)
                )
        );

        $query->execute();
    }

    /**
     * Update object state links to $newStateId.
     *
     * @param int $oldStateId
     * @param int $newStateId
     */
    public function updateObjectStateLinks($oldStateId, $newStateId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::OBJECT_STATE_LINK_TABLE)
            ->set(
                'contentobject_state_id',
                $query->createPositionalParameter($newStateId, ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->eq(
                    'contentobject_state_id',
                    $query->createPositionalParameter($oldStateId, ParameterType::INTEGER)
                )
            )
        ;

        $query->execute();
    }

    /**
     * Change Content to object state assignment.
     */
    private function updateContentStateAssignment(int $contentId, int $stateId, int $assignedStateId): void
    {
        // no action required if there's no change
        if ($stateId === $assignedStateId) {
            return;
        }

        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::OBJECT_STATE_LINK_TABLE)
            ->set(
                'contentobject_state_id',
                $query->createPositionalParameter($stateId, ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->eq(
                    'contentobject_id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'contentobject_state_id',
                    $query->createPositionalParameter($assignedStateId, ParameterType::INTEGER)
                )
            )
        ;

        $query->execute();
    }

    /**
     * Deletes object state links identified by $stateId.
     *
     * @param int $stateId
     */
    public function deleteObjectStateLinks($stateId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::OBJECT_STATE_LINK_TABLE)
            ->where(
                $query->expr()->eq(
                    'contentobject_state_id',
                    $query->createPositionalParameter($stateId, ParameterType::INTEGER)
                )
        );

        $query->execute();
    }

    /**
     * Inserts a new object state group into database.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    public function insertObjectStateGroup(Group $objectStateGroup)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::OBJECT_STATE_GROUP_TABLE)
            ->values(
                [
                    'default_language_id' => $query->createPositionalParameter(
                        $this->maskGenerator->generateLanguageIndicator(
                            $objectStateGroup->defaultLanguage,
                            false
                        ),
                        ParameterType::INTEGER
                    ),
                    'identifier' => $query->createPositionalParameter(
                        $objectStateGroup->identifier,
                        ParameterType::STRING
                    ),
                    'language_mask' => $query->createPositionalParameter(
                        $this->maskGenerator->generateLanguageMaskFromLanguageCodes(
                            $objectStateGroup->languageCodes,
                            true
                        ),
                        ParameterType::INTEGER
                    ),
                ]
            )
        ;

        $query->execute();

        $objectStateGroup->id = (int)$this->connection->lastInsertId(
            self::OBJECT_STATE_GROUP_TABLE_SEQ
        );

        $this->insertObjectStateGroupTranslations($objectStateGroup);
    }

    /**
     * Updates the stored object state group with provided data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    public function updateObjectStateGroup(Group $objectStateGroup)
    {
        // First update the group
        $this->updateObjectStateCommonFields(
            self::OBJECT_STATE_GROUP_TABLE,
            $objectStateGroup->id,
            $objectStateGroup->identifier,
            $objectStateGroup->defaultLanguage,
            $objectStateGroup->languageCodes
        );

        // And then refresh group translations
        // by removing old ones and adding new ones
        $this->deleteObjectStateGroupTranslations($objectStateGroup->id);
        $this->insertObjectStateGroupTranslations($objectStateGroup);
    }

    /**
     * Deletes the object state group identified by $groupId.
     *
     * @param mixed $groupId
     */
    public function deleteObjectStateGroup($groupId)
    {
        $this->deleteObjectStateGroupTranslations($groupId);

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::OBJECT_STATE_GROUP_TABLE)
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($groupId, ParameterType::INTEGER)
                )
            )
        ;

        $query->execute();
    }

    /**
     * Sets the object state $stateId to content with $contentId ID.
     *
     * @param mixed $contentId
     * @param mixed $groupId
     * @param mixed $stateId
     */
    public function setContentState($contentId, $groupId, $stateId)
    {
        // First find out if $contentId is related to existing states in $groupId
        $assignedStateId = $this->getContentStateId($contentId, $groupId);

        if (null !== $assignedStateId) {
            // We already have a state assigned to $contentId, update to new one
            $this->updateContentStateAssignment($contentId, $stateId, $assignedStateId);
        } else {
            // No state assigned to $contentId from specified group, create assignment
            $this->insertContentStateAssignment($contentId, $stateId);
        }
    }

    /**
     * Loads object state data for $contentId content from $stateGroupId state group.
     *
     * @param int $contentId
     * @param int $stateGroupId
     *
     * @return array
     */
    public function loadObjectStateDataForContent($contentId, $stateGroupId)
    {
        $query = $this->createObjectStateFindQuery();
        $expr = $query->expr();
        $query
            ->innerJoin(
                'state',
                'ezcobj_state_link',
                'link',
                'state.id = link.contentobject_state_id'
            )
            ->where(
                $expr->eq(
                    'state.group_id',
                    $query->createPositionalParameter($stateGroupId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'link.contentobject_id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Returns the number of objects which are in this state.
     *
     * @param mixed $stateId
     *
     * @return int
     */
    public function getContentCount($stateId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->dbPlatform->getCountExpression('contentobject_id')
            )
            ->from(self::OBJECT_STATE_LINK_TABLE)
            ->where(
                $query->expr()->eq(
                    'contentobject_state_id',
                    $query->createPositionalParameter($stateId, ParameterType::INTEGER)
                )
            );

        return (int)$query->execute()->fetchColumn();
    }

    /**
     * Updates the object state priority to provided value.
     *
     * @param mixed $stateId
     * @param int $priority
     */
    public function updateObjectStatePriority($stateId, $priority)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::OBJECT_STATE_TABLE)
            ->set('priority', $query->createPositionalParameter($priority, ParameterType::INTEGER))
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($stateId, ParameterType::INTEGER)
                )
            )
        ;

        $query->execute();
    }

    /**
     * Create a generic query for fetching object state(s).
     */
    protected function createObjectStateFindQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                // Object state
                'state.default_language_id AS ezcobj_state_default_language_id',
                'state.group_id AS ezcobj_state_group_id',
                'state.id AS ezcobj_state_id',
                'state.identifier AS ezcobj_state_identifier',
                'state.language_mask AS ezcobj_state_language_mask',
                'state.priority AS ezcobj_state_priority',
                // Object state language
                'lang.description AS ezcobj_state_language_description',
                'lang.language_id AS ezcobj_state_language_language_id',
                'lang.name AS ezcobj_state_language_name'
            )
            ->from(self::OBJECT_STATE_TABLE, 'state')
            ->innerJoin(
                'state',
                self::OBJECT_STATE_LANGUAGE_TABLE,
                'lang',
                'state.id = lang.contentobject_state_id',
                );

        return $query;
    }

    /**
     * Create a generic query for fetching object state group(s).
     */
    protected function createObjectStateGroupFindQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                // Object state group
                'state_group.default_language_id AS ezcobj_state_group_default_language_id',
                'state_group.id AS ezcobj_state_group_id',
                'state_group.identifier AS ezcobj_state_group_identifier',
                'state_group.language_mask AS ezcobj_state_group_language_mask',
                // Object state group language
                'state_group_lang.description AS ezcobj_state_group_language_description',
                'state_group_lang.language_id AS ezcobj_state_group_language_language_id',
                'state_group_lang.real_language_id AS ezcobj_state_group_language_real_language_id',
                'state_group_lang.name AS ezcobj_state_group_language_name'
            )
            ->from(self::OBJECT_STATE_GROUP_TABLE, 'state_group')
            ->innerJoin(
                'state_group',
                self::OBJECT_STATE_GROUP_LANGUAGE_TABLE,
                'state_group_lang',
                'state_group.id = state_group_lang.contentobject_state_group_id'
            );

        return $query;
    }

    /**
     * Inserts object state group translations into database.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     */
    protected function insertObjectStateTranslations(ObjectState $objectState)
    {
        foreach ($objectState->languageCodes as $languageCode) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->insert(self::OBJECT_STATE_LANGUAGE_TABLE)
                ->values(
                    [
                        'contentobject_state_id' => $query->createPositionalParameter(
                            $objectState->id,
                            ParameterType::INTEGER
                        ),
                        'description' => $query->createPositionalParameter(
                            $objectState->description[$languageCode],
                            ParameterType::STRING
                        ),
                        'name' => $query->createPositionalParameter(
                            $objectState->name[$languageCode],
                            ParameterType::STRING
                        ),
                        'language_id' => $query->createPositionalParameter(
                            $this->maskGenerator->generateLanguageIndicator(
                                $languageCode,
                                $languageCode === $objectState->defaultLanguage
                            ),
                            ParameterType::INTEGER
                        ),
                    ]
                );

            $query->execute();
        }
    }

    /**
     * Deletes all translations of the $stateId state.
     *
     * @param mixed $stateId
     */
    protected function deleteObjectStateTranslations($stateId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::OBJECT_STATE_LANGUAGE_TABLE)
            ->where(
                $query->expr()->eq(
                    'contentobject_state_id',
                    $query->createPositionalParameter($stateId, ParameterType::INTEGER)
                )
        );

        $query->execute();
    }

    /**
     * Inserts object state group translations into database.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    protected function insertObjectStateGroupTranslations(Group $objectStateGroup)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::OBJECT_STATE_GROUP_LANGUAGE_TABLE)
            ->values(
                [
                    'contentobject_state_group_id' => ':contentobject_state_group_id',
                    'description' => ':description',
                    'name' => ':name',
                    'language_id' => ':language_id',
                    'real_language_id' => ':real_language_id',
                ]
            )
        ;
        foreach ($objectStateGroup->languageCodes as $languageCode) {
            $languageId = $this->maskGenerator->generateLanguageIndicator(
                $languageCode,
                $languageCode === $objectStateGroup->defaultLanguage
            );
            $query
                ->setParameter('contentobject_state_group_id', $objectStateGroup->id, ParameterType::INTEGER)
                ->setParameter('description', $objectStateGroup->description[$languageCode], ParameterType::STRING)
                ->setParameter('name', $objectStateGroup->name[$languageCode], ParameterType::STRING)
                ->setParameter('language_id', $languageId, ParameterType::INTEGER)
                ->setParameter('real_language_id', $languageId & ~1, ParameterType::INTEGER);

            $query->execute();
        }
    }

    /**
     * Deletes all translations of the $groupId state group.
     *
     * @param mixed $groupId
     */
    protected function deleteObjectStateGroupTranslations($groupId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::OBJECT_STATE_GROUP_LANGUAGE_TABLE)
            ->where(
                $query->expr()->eq(
                    'contentobject_state_group_id',
                    $query->createPositionalParameter($groupId, ParameterType::INTEGER)
                )
        );

        $query->execute();
    }

    private function getMaxPriorityForObjectStatesInGroup($groupId): ?int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->dbPlatform->getMaxExpression('priority')
            )
            ->from(self::OBJECT_STATE_TABLE)
            ->where(
                $query->expr()->eq(
                    'group_id',
                    $query->createPositionalParameter($groupId, ParameterType::INTEGER)
                )
            );

        $priority = $query->execute()->fetchColumn();

        return null !== $priority ? (int)$priority : null;
    }

    private function getContentStateId(int $contentId, int $groupId): ?int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('state.id')
            ->from(self::OBJECT_STATE_TABLE, 'state')
            ->innerJoin(
                'state',
                self::OBJECT_STATE_LINK_TABLE,
                'link',
                'state.id = link.contentobject_state_id'
            )
            ->where(
                $query->expr()->eq(
                    'state.group_id',
                    $query->createPositionalParameter($groupId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'link.contentobject_id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            );

        $stateId = $query->execute()->fetch(FetchMode::COLUMN);

        return false !== $stateId ? (int)$stateId : null;
    }

    /**
     * @param int $contentId
     * @param int $stateId
     */
    private function insertContentStateAssignment(int $contentId, int $stateId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::OBJECT_STATE_LINK_TABLE)
            ->values(
                [
                    'contentobject_id' => $query->createPositionalParameter(
                        $contentId,
                        ParameterType::INTEGER
                    ),
                    'contentobject_state_id' => $query->createPositionalParameter(
                        $stateId,
                        ParameterType::INTEGER
                    ),
                ]
            );

        $query->execute();
    }
}
