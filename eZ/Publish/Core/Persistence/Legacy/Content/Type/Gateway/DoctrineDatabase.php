<?php

/**
 * File containing the DoctrineDatabase Content Type Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\SPI\Persistence\ValueObject;
use eZ\Publish\Core\Persistence\Database\Query;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use PDO;

/**
 * Doctrine database based content type gateway.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * Columns of database tables.
     *
     * @var array
     */
    protected $columns = array(
        'ezcontentclass' => array(
            'id',
            'always_available',
            'contentobject_name',
            'created',
            'creator_id',
            'modified',
            'modifier_id',
            'identifier',
            'initial_language_id',
            'is_container',
            'language_mask',
            'remote_id',
            'serialized_description_list',
            'serialized_name_list',
            'sort_field',
            'sort_order',
            'url_alias_name',
            'version',
        ),
        'ezcontentclass_attribute' => array(
            'id',
            'can_translate',
            'category',
            'contentclass_id',
            'data_float1',
            'data_float2',
            'data_float3',
            'data_float4',
            'data_int1',
            'data_int2',
            'data_int3',
            'data_int4',
            'data_text1',
            'data_text2',
            'data_text3',
            'data_text4',
            'data_text5',
            'data_type_string',
            'identifier',
            'is_information_collector',
            'is_required',
            'is_searchable',
            'placement',
            'serialized_data_text',
            'serialized_description_list',
            'serialized_name_list',
        ),
    );

    /**
     * DoctrineDatabase handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * The native Doctrine connection.
     *
     * Meant to be used to transition from eZ/Zeta interface to Doctrine.
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Creates a new gateway based on $db.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $db
     * @param \Doctrine\DBAL\Connection $connection
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(DatabaseHandler $db, Connection $connection, MaskGenerator $languageMaskGenerator)
    {
        $this->dbHandler = $db;
        $this->connection = $connection;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     * Inserts the given $group.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group $group
     *
     * @return mixed Group ID
     */
    public function insertGroup(Group $group)
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable('ezcontentclassgroup')
        )->set(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->getAutoIncrementValue('ezcontentclassgroup', 'id')
        )->set(
            $this->dbHandler->quoteColumn('created'),
            $q->bindValue($group->created, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('creator_id'),
            $q->bindValue($group->creatorId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('modified'),
            $q->bindValue($group->modified, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('modifier_id'),
            $q->bindValue($group->modifierId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('name'),
            $q->bindValue($group->identifier)
        );
        $q->prepare()->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName('ezcontentclassgroup', 'id')
        );
    }

    /**
     * Updates a group with data in $group.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct $group
     */
    public function updateGroup(GroupUpdateStruct $group)
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteColumn('ezcontentclassgroup')
        )->set(
            $this->dbHandler->quoteColumn('modified'),
            $q->bindValue($group->modified, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('modifier_id'),
            $q->bindValue($group->modifierId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('name'),
            $q->bindValue($group->identifier)
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $q->bindValue($group->id, null, \PDO::PARAM_INT)
            )
        );

        $q->prepare()->execute();
    }

    /**
     * Returns the number of types in a certain group.
     *
     * @param int $groupId
     *
     * @return int
     */
    public function countTypesInGroup($groupId)
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            $q->alias(
                $q->expr->count(
                    $this->dbHandler->quoteColumn('contentclass_id')
                ),
                'count'
            )
        )->from(
            $this->dbHandler->quoteTable('ezcontentclass_classgroup')
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn('group_id'),
                $q->bindValue($groupId, null, \PDO::PARAM_INT)
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Returns the number of Groups the type is assigned to.
     *
     * @param int $typeId
     * @param int $status
     *
     * @return int
     */
    public function countGroupsForType($typeId, $status)
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            $q->alias(
                $q->expr->count(
                    $this->dbHandler->quoteColumn('group_id')
                ),
                'count'
            )
        )->from(
            $this->dbHandler->quoteTable('ezcontentclass_classgroup')
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $q->bindValue($typeId, null, \PDO::PARAM_INT)
                )
            ),
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_version'),
                    $q->bindValue($status, null, \PDO::PARAM_INT)
                )
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Deletes the Group with the given $groupId.
     *
     * @param int $groupId
     */
    public function deleteGroup($groupId)
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom($this->dbHandler->quoteTable('ezcontentclassgroup'))
            ->where(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $q->bindValue($groupId, null, \PDO::PARAM_INT)
                )
            );
        $q->prepare()->execute();
    }

    /**
     * Inserts data into contentclass_name.
     *
     * @param int $typeId
     * @param int $typeStatus
     * @param string[] $languages
     */
    protected function insertTypeNameData($typeId, $typeStatus, array $languages)
    {
        $tmpLanguages = $languages;
        if (isset($tmpLanguages['always-available'])) {
            unset($tmpLanguages['always-available']);
        }

        foreach ($tmpLanguages as $language => $name) {
            $query = $this->dbHandler->createInsertQuery();
            $query
                ->insertInto($this->dbHandler->quoteTable('ezcontentclass_name'))
                ->set('contentclass_id', $query->bindValue($typeId, null, \PDO::PARAM_INT))
                ->set('contentclass_version', $query->bindValue($typeStatus, null, \PDO::PARAM_INT))
                ->set(
                    'language_id',
                    $query->bindValue(
                        $this->languageMaskGenerator->generateLanguageIndicator(
                            $language,
                            $this->languageMaskGenerator->isLanguageAlwaysAvailable(
                                $language,
                                $languages
                            )
                        ),
                        null,
                        \PDO::PARAM_INT
                    )
                )
                ->set('language_locale', $query->bindValue($language))
                ->set('name', $query->bindValue($name));
            $query->prepare()->execute();
        }
    }

    /**
     * Inserts a new content type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $type
     * @param mixed|null $typeId
     *
     * @return mixed Type ID
     */
    public function insertType(Type $type, $typeId = null)
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto($this->dbHandler->quoteTable('ezcontentclass'));
        $q->set(
            $this->dbHandler->quoteColumn('id'),
            isset($typeId) ? $q->bindValue($typeId, null, \PDO::PARAM_INT) : $this->dbHandler->getAutoIncrementValue('ezcontentclass', 'id')
        )->set(
            $this->dbHandler->quoteColumn('version'),
            $q->bindValue($type->status, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('created'),
            $q->bindValue($type->created, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('creator_id'),
            $q->bindValue($type->creatorId, null, \PDO::PARAM_INT)
        );
        $this->setCommonTypeColumns($q, $type);

        $q->prepare()->execute();

        if (empty($typeId)) {
            $typeId = $this->dbHandler->lastInsertId(
                $this->dbHandler->getSequenceName('ezcontentclass', 'id')
            );
        }

        $this->insertTypeNameData($typeId, $type->status, $type->name);

        return $typeId;
    }

    /**
     * Set common columns for insert/update of a Type.
     *
     * @param \eZ\Publish\Core\Persistence\Database\InsertQuery|\eZ\Publish\Core\Persistence\Database\UpdateQuery $q
     * @param \eZ\Publish\SPI\Persistence\ValueObject|\eZ\Publish\SPI\Persistence\Content\Type|\eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct $type
     */
    protected function setCommonTypeColumns(Query $q, ValueObject $type)
    {
        $q->set(
            $this->dbHandler->quoteColumn('serialized_name_list'),
            $q->bindValue(serialize($type->name))
        )->set(
            $this->dbHandler->quoteColumn('serialized_description_list'),
            $q->bindValue(serialize($type->description))
        )->set(
            $this->dbHandler->quoteColumn('identifier'),
            $q->bindValue($type->identifier)
        )->set(
            $this->dbHandler->quoteColumn('modified'),
            $q->bindValue($type->modified, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('modifier_id'),
            $q->bindValue($type->modifierId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('remote_id'),
            $q->bindValue($type->remoteId)
        )->set(
            $this->dbHandler->quoteColumn('url_alias_name'),
            $q->bindValue($type->urlAliasSchema)
        )->set(
            $this->dbHandler->quoteColumn('contentobject_name'),
            $q->bindValue($type->nameSchema)
        )->set(
            $this->dbHandler->quoteColumn('is_container'),
            $q->bindValue($type->isContainer ? 1 : 0, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('language_mask'),
            $q->bindValue(
                $this->languageMaskGenerator->generateLanguageMask($type->name),
                null,
                \PDO::PARAM_INT
            )
        )->set(
            $this->dbHandler->quoteColumn('initial_language_id'),
            $q->bindValue($type->initialLanguageId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('sort_field'),
            $q->bindValue($type->sortField, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('sort_order'),
            $q->bindValue($type->sortOrder, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('always_available'),
            $q->bindValue((int)$type->defaultAlwaysAvailable, null, \PDO::PARAM_INT)
        );
    }

    /**
     * Insert assignment of $typeId to $groupId.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $status
     */
    public function insertGroupAssignment($groupId, $typeId, $status)
    {
        $groups = $this->loadGroupData([$groupId]);
        $group = $groups[0];

        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable('ezcontentclass_classgroup')
        )->set(
            $this->dbHandler->quoteColumn('contentclass_id'),
            $q->bindValue($typeId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('contentclass_version'),
            $q->bindValue($status, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('group_id'),
            $q->bindValue($groupId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('group_name'),
            $q->bindValue($group['name'])
        );

        $q->prepare()->execute();
    }

    /**
     * Deletes a group assignments for a Type.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $status
     */
    public function deleteGroupAssignment($groupId, $typeId, $status)
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable('ezcontentclass_classgroup')
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $q->bindValue($typeId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_version'),
                    $q->bindValue($status, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('group_id'),
                    $q->bindValue($groupId, null, \PDO::PARAM_INT)
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Loads data about Groups with $groupIds.
     *
     * @param int[] $groupIds
     *
     * @return string[][]
     */
    public function loadGroupData(array $groupIds)
    {
        $q = $this->connection->createQueryBuilder();
        $q
            ->select('created', 'creator_id', 'id', 'modified', 'modifier_id', 'name')
            ->from('ezcontentclassgroup')
            ->where('id IN (:ids)')
            ->setParameter('ids', $groupIds, Connection::PARAM_INT_ARRAY);

        return $q->execute()->fetchAll();
    }

    /**
     * Loads data about Group with $identifier.
     *
     * @param mixed $identifier
     *
     * @return string[][]
     */
    public function loadGroupDataByIdentifier($identifier)
    {
        $q = $this->createGroupLoadQuery();
        $q->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn('name'),
                $q->bindValue($identifier, null, \PDO::PARAM_STR)
            )
        );
        $stmt = $q->prepare();
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Returns an array with data about all Group objects.
     *
     * @return string[][]
     */
    public function loadAllGroupsData()
    {
        $q = $this->createGroupLoadQuery();

        $stmt = $q->prepare();
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Creates the basic query to load Group data.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    protected function createGroupLoadQuery()
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            $this->dbHandler->quoteColumn('created'),
            $this->dbHandler->quoteColumn('creator_id'),
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->quoteColumn('modified'),
            $this->dbHandler->quoteColumn('modifier_id'),
            $this->dbHandler->quoteColumn('name')
        )->from(
            $this->dbHandler->quoteTable('ezcontentclassgroup')
        );

        return $q;
    }

    /**
     * Loads data for all Types in $status in $groupId.
     *
     * @param mixed $groupId
     * @param int $status
     *
     * @return string[][]
     */
    public function loadTypesDataForGroup($groupId, $status)
    {
        $q = $this->getLoadTypeQueryBuilder();
        $q
            ->where('g.group_id = :gid')
            ->andWhere('c.version = :version')
            ->addOrderBy('c.identifier')
            ->setParameter('gid', $groupId, ParameterType::INTEGER)
            ->setParameter('version', $status, ParameterType::INTEGER);

        return $q->execute()->fetchAll();
    }

    /**
     * Inserts a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageFieldDef
     *
     * @return mixed Field definition ID
     */
    public function insertFieldDefinition(
        $typeId,
        $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ) {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto($this->dbHandler->quoteTable('ezcontentclass_attribute'));
        $q->set(
            $this->dbHandler->quoteColumn('id'),
            isset($fieldDefinition->id) ? $q->bindValue($fieldDefinition->id, null, \PDO::PARAM_INT) : $this->dbHandler->getAutoIncrementValue('ezcontentclass_attribute', 'id')
        )->set(
            $this->dbHandler->quoteColumn('contentclass_id'),
            $q->bindValue($typeId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('version'),
            $q->bindValue($status, null, \PDO::PARAM_INT)
        );
        $this->setCommonFieldColumns($q, $fieldDefinition, $storageFieldDef);

        $q->prepare()->execute();

        if (!isset($fieldDefinition->id)) {
            return $this->dbHandler->lastInsertId(
                $this->dbHandler->getSequenceName('ezcontentclass_attribute', 'id')
            );
        }

        return $fieldDefinition->id;
    }

    /**
     * Set common columns for insert/update of FieldDefinition.
     *
     * @param \eZ\Publish\Core\Persistence\Database\InsertQuery|\eZ\Publish\Core\Persistence\Database\UpdateQuery $q
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageFieldDef
     */
    protected function setCommonFieldColumns(
        Query $q,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ) {
        $q->set(
            $this->dbHandler->quoteColumn('serialized_name_list'),
            $q->bindValue(serialize($fieldDefinition->name))
        )->set(
            $this->dbHandler->quoteColumn('serialized_description_list'),
            $q->bindValue(serialize($fieldDefinition->description))
        )->set(
            $this->dbHandler->quoteColumn('identifier'),
            $q->bindValue($fieldDefinition->identifier)
        )->set(
            $this->dbHandler->quoteColumn('category'),
            $q->bindValue($fieldDefinition->fieldGroup, null, \PDO::PARAM_STR)
        )->set(
            $this->dbHandler->quoteColumn('placement'),
            $q->bindValue($fieldDefinition->position, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_type_string'),
            $q->bindValue($fieldDefinition->fieldType)
        )->set(
            $this->dbHandler->quoteColumn('can_translate'),
            $q->bindValue(($fieldDefinition->isTranslatable ? 1 : 0), null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('is_required'),
            $q->bindValue(($fieldDefinition->isRequired ? 1 : 0), null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('is_information_collector'),
            $q->bindValue(($fieldDefinition->isInfoCollector ? 1 : 0), null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_float1'),
            $q->bindValue($storageFieldDef->dataFloat1)
        )->set(
            $this->dbHandler->quoteColumn('data_float2'),
            $q->bindValue($storageFieldDef->dataFloat2)
        )->set(
            $this->dbHandler->quoteColumn('data_float3'),
            $q->bindValue($storageFieldDef->dataFloat3)
        )->set(
            $this->dbHandler->quoteColumn('data_float4'),
            $q->bindValue($storageFieldDef->dataFloat4)
        )->set(
            $this->dbHandler->quoteColumn('data_int1'),
            $q->bindValue($storageFieldDef->dataInt1, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_int2'),
            $q->bindValue($storageFieldDef->dataInt2, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_int3'),
            $q->bindValue($storageFieldDef->dataInt3, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_int4'),
            $q->bindValue($storageFieldDef->dataInt4, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_text1'),
            $q->bindValue($storageFieldDef->dataText1)
        )->set(
            $this->dbHandler->quoteColumn('data_text2'),
            $q->bindValue($storageFieldDef->dataText2)
        )->set(
            $this->dbHandler->quoteColumn('data_text3'),
            $q->bindValue($storageFieldDef->dataText3)
        )->set(
            $this->dbHandler->quoteColumn('data_text4'),
            $q->bindValue($storageFieldDef->dataText4)
        )->set(
            $this->dbHandler->quoteColumn('data_text5'),
            $q->bindValue($storageFieldDef->dataText5)
        )->set(
            $this->dbHandler->quoteColumn('serialized_data_text'),
            $q->bindValue(serialize($storageFieldDef->serializedDataText))
        )->set(
            $this->dbHandler->quoteColumn('is_searchable'),
            $q->bindValue(($fieldDefinition->isSearchable ? 1 : 0), null, \PDO::PARAM_INT)
        );
    }

    /**
     * Loads an array with data about field definition referred $id and $status.
     *
     * @param mixed $id field definition id
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     *
     * @return array Data rows.
     */
    public function loadFieldDefinition($id, $status)
    {
        $q = $this->dbHandler->createSelectQuery();
        $this->selectColumns($q, 'ezcontentclass_attribute');
        $q->from(
            $this->dbHandler->quoteTable('ezcontentclass_attribute')
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('id', 'ezcontentclass_attribute'),
                    $q->bindValue($id, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version', 'ezcontentclass_attribute'),
                    $q->bindValue($status, null, \PDO::PARAM_INT)
                )
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a field definition.
     *
     * @param mixed $typeId
     * @param int $status
     * @param mixed $fieldDefinitionId
     */
    public function deleteFieldDefinition($typeId, $status, $fieldDefinitionId)
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable('ezcontentclass_attribute')
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $q->bindValue($fieldDefinitionId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $q->bindValue($status, null, \PDO::PARAM_INT)
                ),
                // @todo FIXME: Actually not needed
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $q->bindValue($typeId, null, \PDO::PARAM_INT)
                )
            )
        );

        $q->prepare()->execute();
    }

    /**
     * Updates a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageFieldDef
     */
    public function updateFieldDefinition(
        $typeId,
        $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ) {
        $q = $this->dbHandler->createUpdateQuery();
        $q
            ->update(
                $this->dbHandler->quoteTable('ezcontentclass_attribute')
            )->where(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $q->bindValue($fieldDefinition->id, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $q->bindValue($status, null, \PDO::PARAM_INT)
                ),
                // @todo FIXME: Actually not needed
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $q->bindValue($typeId, null, \PDO::PARAM_INT)
                )
            );
        $this->setCommonFieldColumns($q, $fieldDefinition, $storageFieldDef);

        $q->prepare()->execute();
    }

    /**
     * Deletes all name data for $typeId in $typeStatus.
     *
     * @param int $typeId
     * @param int $typeStatus
     */
    protected function deleteTypeNameData($typeId, $typeStatus)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom('ezcontentclass_name')
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('contentclass_id'),
                        $query->bindValue($typeId, null, \PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('contentclass_version'),
                        $query->bindValue($typeStatus, null, \PDO::PARAM_INT)
                    )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Update a type with $updateStruct.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct $updateStruct
     */
    public function updateType($typeId, $status, UpdateStruct $updateStruct)
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update($this->dbHandler->quoteTable('ezcontentclass'));

        $this->setCommonTypeColumns($q, $updateStruct);

        $q->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $q->bindValue($typeId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $q->bindValue($status, null, \PDO::PARAM_INT)
                )
            )
        );

        $q->prepare()->execute();

        $this->deleteTypeNameData($typeId, $status);
        $this->insertTypeNameData($typeId, $status, $updateStruct->name);
    }

    public function loadTypesDataList(array $typeIds): array
    {
        $q = $this->getLoadTypeQueryBuilder();
        $q
            ->where('c.id IN (:ids)')
            ->andWhere($q->expr()->eq('c.version', Type::STATUS_DEFINED))
            ->setParameter('ids', $typeIds, Connection::PARAM_INT_ARRAY);

        return $q->execute()->fetchAll();
    }

    /**
     * Loads an array with data about $typeId in $status.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return array Data rows.
     */
    public function loadTypeData($typeId, $status)
    {
        $q = $this->getLoadTypeQueryBuilder();
        $q
            ->where('c.id = :id')
            ->andWhere('c.version = :version')
            ->setParameter('id', $typeId, ParameterType::INTEGER)
            ->setParameter('version', $status, ParameterType::INTEGER);

        return $q->execute()->fetchAll();
    }

    /**
     * Loads an array with data about the type referred to by $identifier in
     * $status.
     *
     * @param string $identifier
     * @param int $status
     *
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    public function loadTypeDataByIdentifier($identifier, $status)
    {
        $q = $this->getLoadTypeQueryBuilder();
        $q
            ->where('c.identifier = :identifier')
            ->andWhere('c.version = :version')
            ->setParameter('identifier', $identifier, ParameterType::STRING)
            ->setParameter('version', $status, ParameterType::INTEGER);

        return $q->execute()->fetchAll();
    }

    /**
     * Loads an array with data about the type referred to by $remoteId in
     * $status.
     *
     * @param mixed $remoteId
     * @param int $status
     *
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    public function loadTypeDataByRemoteId($remoteId, $status)
    {
        $q = $this->getLoadTypeQueryBuilder();
        $q
            ->where('c.remote_id = :remote')
            ->andWhere('c.version = :version')
            ->setParameter('remote', $remoteId, ParameterType::STRING)
            ->setParameter('version', $status, ParameterType::INTEGER);

        return $q->execute()->fetchAll();
    }

    /**
     * Returns a basic query to retrieve Type data.
     */
    private function getLoadTypeQueryBuilder(): QueryBuilder
    {
        $q = $this->connection->createQueryBuilder();
        $q
            ->select([
                'c.id AS ezcontentclass_id',
                'c.version AS ezcontentclass_version',
                'c.serialized_name_list AS ezcontentclass_serialized_name_list',
                'c.serialized_description_list AS ezcontentclass_serialized_description_list',
                'c.identifier AS ezcontentclass_identifier',
                'c.created AS ezcontentclass_created',
                'c.modified AS ezcontentclass_modified',
                'c.modifier_id AS ezcontentclass_modifier_id',
                'c.creator_id AS ezcontentclass_creator_id',
                'c.remote_id AS ezcontentclass_remote_id',
                'c.url_alias_name AS ezcontentclass_url_alias_name',
                'c.contentobject_name AS ezcontentclass_contentobject_name',
                'c.is_container AS ezcontentclass_is_container',
                'c.initial_language_id AS ezcontentclass_initial_language_id',
                'c.always_available AS ezcontentclass_always_available',
                'c.sort_field AS ezcontentclass_sort_field',
                'c.sort_order AS ezcontentclass_sort_order',

                'a.id AS ezcontentclass_attribute_id',
                'a.serialized_name_list AS ezcontentclass_attribute_serialized_name_list',
                'a.serialized_description_list AS ezcontentclass_attribute_serialized_description_list',
                'a.identifier AS ezcontentclass_attribute_identifier',
                'a.category AS ezcontentclass_attribute_category',
                'a.data_type_string AS ezcontentclass_attribute_data_type_string',
                'a.can_translate AS ezcontentclass_attribute_can_translate',
                'a.is_required AS ezcontentclass_attribute_is_required',
                'a.is_information_collector AS ezcontentclass_attribute_is_information_collector',
                'a.is_searchable AS ezcontentclass_attribute_is_searchable',
                'a.placement AS ezcontentclass_attribute_placement',
                'a.data_float1 AS ezcontentclass_attribute_data_float1',
                'a.data_float2 AS ezcontentclass_attribute_data_float2',
                'a.data_float3 AS ezcontentclass_attribute_data_float3',
                'a.data_float4 AS ezcontentclass_attribute_data_float4',
                'a.data_int1 AS ezcontentclass_attribute_data_int1',
                'a.data_int2 AS ezcontentclass_attribute_data_int2',
                'a.data_int3 AS ezcontentclass_attribute_data_int3',
                'a.data_int4 AS ezcontentclass_attribute_data_int4',
                'a.data_text1 AS ezcontentclass_attribute_data_text1',
                'a.data_text2 AS ezcontentclass_attribute_data_text2',
                'a.data_text3 AS ezcontentclass_attribute_data_text3',
                'a.data_text4 AS ezcontentclass_attribute_data_text4',
                'a.data_text5 AS ezcontentclass_attribute_data_text5',
                'a.serialized_data_text AS ezcontentclass_attribute_serialized_data_text',

                'g.group_id AS ezcontentclass_classgroup_group_id',
            ])
            ->from('ezcontentclass', 'c')
            ->leftJoin(
                'c',
                'ezcontentclass_attribute',
                'a',
                'c.id = a.contentclass_id AND c.version = a.version'
            )
            ->leftJoin(
                'c',
                'ezcontentclass_classgroup',
                'g',
                'c.id = g.contentclass_id AND c.version = g.contentclass_version'
            )
            ->orderBy('a.placement');

        return $q;
    }

    /**
     * Counts the number of instances that exists of the identified type.
     *
     * @param int $typeId
     *
     * @return int
     */
    public function countInstancesOfType($typeId)
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            $q->alias(
                $q->expr->count(
                    $this->dbHandler->quoteColumn('id')
                ),
                'count'
            )
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject')
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn('contentclass_id'),
                $q->bindValue($typeId, null, \PDO::PARAM_INT)
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Deletes all field definitions of a Type.
     *
     * @param mixed $typeId
     * @param int $status
     */
    public function deleteFieldDefinitionsForType($typeId, $status)
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable('ezcontentclass_attribute')
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $q->bindValue($typeId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $q->bindValue($status, null, \PDO::PARAM_INT)
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Deletes a Type completely.
     *
     * @param mixed $typeId
     * @param int $status
     */
    public function delete($typeId, $status)
    {
        $this->deleteGroupAssignmentsForType($typeId, $status);
        $this->deleteFieldDefinitionsForType($typeId, $status);
        $this->deleteTypeNameData($typeId, $status);
        $this->deleteType($typeId, $status);
    }

    /**
     * Deletes a the Type.
     *
     * Does no delete the field definitions!
     *
     * @param mixed $typeId
     * @param int $status
     */
    public function deleteType($typeId, $status)
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable('ezcontentclass')
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $q->bindValue($typeId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $q->bindValue($status, null, \PDO::PARAM_INT)
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Deletes all group assignments for a Type.
     *
     * @param mixed $typeId
     * @param int $status
     */
    public function deleteGroupAssignmentsForType($typeId, $status)
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable('ezcontentclass_classgroup')
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $q->bindValue($typeId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_version'),
                    $q->bindValue($status, null, \PDO::PARAM_INT)
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Publishes the Type with $typeId from $sourceVersion to $targetVersion,
     * including its fields.
     *
     * @param int $typeId
     * @param int $sourceVersion
     * @param int $targetVersion
     */
    public function publishTypeAndFields($typeId, $sourceVersion, $targetVersion)
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezcontentclass')
        )->set(
            $this->dbHandler->quoteColumn('version'),
            $query->bindValue($targetVersion, null, \PDO::PARAM_INT)
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $query->bindValue($typeId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $query->bindValue($sourceVersion, null, \PDO::PARAM_INT)
                )
            )
        );

        $query->prepare()->execute();

        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezcontentclass_classgroup')
        )->set(
            $this->dbHandler->quoteColumn('contentclass_version'),
            $query->bindValue($targetVersion, null, \PDO::PARAM_INT)
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $query->bindValue($typeId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_version'),
                    $query->bindValue($sourceVersion, null, \PDO::PARAM_INT)
                )
            )
        );

        $query->prepare()->execute();

        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezcontentclass_attribute')
        )->set(
            $this->dbHandler->quoteColumn('version'),
            $query->bindValue($targetVersion, null, \PDO::PARAM_INT)
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $query->bindValue($typeId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $query->bindValue($sourceVersion, null, \PDO::PARAM_INT)
                )
            )
        );

        $query->prepare()->execute();

        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezcontentclass_name')
        )->set(
            $this->dbHandler->quoteColumn('contentclass_version'),
            $query->bindValue($targetVersion, null, \PDO::PARAM_INT)
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $query->bindValue($typeId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_version'),
                    $query->bindValue($sourceVersion, null, \PDO::PARAM_INT)
                )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Creates an array of select columns for $tableName.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $q
     * @param string $tableName
     */
    protected function selectColumns(SelectQuery $q, $tableName)
    {
        foreach ($this->columns[$tableName] as $col) {
            $q->select(
                $this->dbHandler->aliasedColumn($q, $col, $tableName)
            );
        }
    }

    /**
     * Returns searchable field mapping data.
     *
     * @return array
     */
    public function getSearchableFieldMapData()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->alias(
                    $this->dbHandler->quoteColumn('identifier', 'ezcontentclass_attribute'),
                    $this->dbHandler->quoteIdentifier('field_definition_identifier')
                ),
                $this->dbHandler->alias(
                    $this->dbHandler->quoteColumn('identifier', 'ezcontentclass'),
                    $this->dbHandler->quoteIdentifier('content_type_identifier')
                ),
                $this->dbHandler->alias(
                    $this->dbHandler->quoteColumn('id', 'ezcontentclass_attribute'),
                    $this->dbHandler->quoteIdentifier('field_definition_id')
                ),
                $this->dbHandler->alias(
                    $this->dbHandler->quoteColumn('data_type_string', 'ezcontentclass_attribute'),
                    $this->dbHandler->quoteIdentifier('field_type_identifier')
                )
            )
            ->from(
                $this->dbHandler->quoteTable('ezcontentclass_attribute')
            )
            ->innerJoin(
                $this->dbHandler->quoteTable('ezcontentclass'),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id', 'ezcontentclass_attribute'),
                    $this->dbHandler->quoteColumn('id', 'ezcontentclass')
                )
            )->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_searchable', 'ezcontentclass_attribute'),
                    $query->bindValue(1, null, PDO::PARAM_INT)
                )
            );

        $statement = $query->prepare($query);
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
