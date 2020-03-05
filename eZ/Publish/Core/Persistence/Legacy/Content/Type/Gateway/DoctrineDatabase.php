<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Persistence\Legacy\Content\MultilingualStorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\SharedGateway\Gateway as SharedGateway;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use function sprintf;

/**
 * Content Type gateway implementation using the Doctrine database.
 *
 * @internal Gateway implementation is considered internal. Use Persistence Content Type Handler instead.
 *
 * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler
 */
final class DoctrineDatabase extends Gateway
{
    /**
     * Columns of database tables.
     *
     * @var array
     */
    private $columns = [
        'ezcontentclass' => [
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
        ],
        'ezcontentclass_attribute' => [
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
            'is_thumbnail',
            'placement',
            'serialized_data_text',
            'serialized_description_list',
            'serialized_name_list',
        ],
    ];

    /**
     * DoctrineDatabase handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     * @deprecated Start to use DBAL $connection instead.
     */
    private $dbHandler;

    /**
     * The native Doctrine connection.
     *
     * Meant to be used to transition from eZ/Zeta interface to Doctrine.
     *
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    /** @var \eZ\Publish\Core\Persistence\Legacy\SharedGateway\Gateway */
    private $sharedGateway;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    private $languageMaskGenerator;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        DatabaseHandler $db,
        Connection $connection,
        SharedGateway $sharedGateway,
        MaskGenerator $languageMaskGenerator
    ) {
        $this->dbHandler = $db;
        $this->connection = $connection;
        $this->dbPlatform = $connection->getDatabasePlatform();
        $this->sharedGateway = $sharedGateway;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    public function insertGroup(Group $group): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::CONTENT_TYPE_GROUP_TABLE)
            ->values(
                [
                    'created' => $query->createPositionalParameter(
                        $group->created,
                        ParameterType::INTEGER
                    ),
                    'creator_id' => $query->createPositionalParameter(
                        $group->creatorId,
                        ParameterType::INTEGER
                    ),
                    'modified' => $query->createPositionalParameter(
                        $group->modified,
                        ParameterType::INTEGER
                    ),
                    'modifier_id' => $query->createPositionalParameter(
                        $group->modifierId,
                        ParameterType::INTEGER
                    ),
                    'name' => $query->createPositionalParameter(
                        $group->identifier,
                        ParameterType::STRING
                    ),
                ]
            );
        $query->execute();

        return (int)$this->connection->lastInsertId(self::CONTENT_TYPE_GROUP_SEQ);
    }

    public function updateGroup(GroupUpdateStruct $group): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_TYPE_GROUP_TABLE)
            ->set(
                'modified',
                $query->createPositionalParameter($group->modified, ParameterType::INTEGER)
            )
            ->set(
                'modifier_id',
                $query->createPositionalParameter($group->modifierId, ParameterType::INTEGER)
            )
            ->set(
                'name',
                $query->createPositionalParameter($group->identifier, ParameterType::STRING)
            )->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($group->id, ParameterType::INTEGER)
                )
            );

        $query->execute();
    }

    public function countTypesInGroup(int $groupId): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->dbPlatform->getCountExpression('contentclass_id'))
            ->from(self::CONTENT_TYPE_TO_GROUP_ASSIGNMENT_TABLE)
            ->where(
                $query->expr()->eq(
                    'group_id',
                    $query->createPositionalParameter($groupId, ParameterType::INTEGER)
                )
            );

        return (int)$query->execute()->fetchColumn();
    }

    public function countGroupsForType(int $typeId, int $status): int
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select($this->dbPlatform->getCountExpression('group_id'))
            ->from(self::CONTENT_TYPE_TO_GROUP_ASSIGNMENT_TABLE)
            ->where(
                $expr->eq(
                    'contentclass_id',
                    $query->createPositionalParameter($typeId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'contentclass_version',
                    $query->createPositionalParameter($status, ParameterType::INTEGER)
                )
            );

        return (int)$query->execute()->fetchColumn();
    }

    public function deleteGroup(int $groupId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->delete(self::CONTENT_TYPE_GROUP_TABLE)
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($groupId, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    /**
     * @param string[] $languages
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if at least one of the used languages does not exist
     */
    private function insertTypeNameData(int $typeId, int $typeStatus, array $languages): void
    {
        $tmpLanguages = $languages;
        if (isset($tmpLanguages['always-available'])) {
            unset($tmpLanguages['always-available']);
        }

        foreach ($tmpLanguages as $language => $name) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->insert(self::CONTENT_TYPE_NAME_TABLE)
                ->values(
                    [
                        'contentclass_id' => $query->createPositionalParameter(
                            $typeId,
                            ParameterType::INTEGER
                        ),
                        'contentclass_version' => $query->createPositionalParameter(
                            $typeStatus,
                            ParameterType::INTEGER
                        ),
                        'language_id' => $query->createPositionalParameter(
                            $this->languageMaskGenerator->generateLanguageIndicator(
                                $language,
                                $this->languageMaskGenerator->isLanguageAlwaysAvailable(
                                    $language,
                                    $languages
                                )
                            ),
                            ParameterType::INTEGER
                        ),
                        'language_locale' => $query->createPositionalParameter(
                            $language,
                            ParameterType::STRING
                        ),
                        'name' => $query->createPositionalParameter($name, ParameterType::STRING),
                    ]
                );
            $query->execute();
        }
    }

    private function setNextAutoIncrementedValueIfAvailable(
        QueryBuilder $queryBuilder,
        string $tableName,
        string $idColumnName,
        string $sequenceName,
        ?int $defaultValue = null
    ): void {
        if (null === $defaultValue) {
            // usually returns null to trigger default column value behavior
            $defaultValue = $this->sharedGateway->getColumnNextIntegerValue(
                $tableName,
                $idColumnName,
                $sequenceName
            );
        }
        // avoid trying to insert NULL to trigger default column value behavior
        if (null !== $defaultValue) {
            $queryBuilder->setValue(
                $idColumnName,
                $queryBuilder->createNamedParameter(
                    $defaultValue,
                    ParameterType::INTEGER,
                    ":{$idColumnName}"
                )
            );
        }
    }

    public function insertType(Type $type, ?int $typeId = null): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::CONTENT_TYPE_TABLE)
            ->values(
                [
                    // Legacy Storage: "version" stores CT status (draft, published)
                    'version' => $query->createNamedParameter(
                        $type->status,
                        ParameterType::INTEGER,
                        ':status'
                    ),
                    'created' => $query->createNamedParameter(
                        $type->created,
                        ParameterType::INTEGER,
                        ':created'
                    ),
                    'creator_id' => $query->createNamedParameter(
                        $type->creatorId,
                        ParameterType::INTEGER,
                        ':creator_id'
                    ),
                ]
            );
        $this->setNextAutoIncrementedValueIfAvailable(
            $query,
            self::CONTENT_TYPE_TABLE,
            'id',
            self::CONTENT_TYPE_SEQ,
            $typeId
        );

        $columnQueryValueAndTypeMap = $this->mapCommonContentTypeColumnsToQueryValuesAndTypes(
            $type
        );
        foreach ($columnQueryValueAndTypeMap as $columnName => $data) {
            [$value, $parameterType] = $data;
            $query
                ->setValue(
                    $columnName,
                    $query->createNamedParameter($value, $parameterType, ":{$columnName}")
                );
        }

        $query->setParameter('status', $type->status, ParameterType::INTEGER);
        $query->setParameter('created', $type->created, ParameterType::INTEGER);
        $query->setParameter('creator_id', $type->creatorId, ParameterType::INTEGER);

        $query->execute();

        if (empty($typeId)) {
            $typeId = $this->sharedGateway->getLastInsertedId(self::CONTENT_TYPE_SEQ);
        }

        $this->insertTypeNameData($typeId, $type->status, $type->name);

        // $typeId passed as the argument could still be non-int
        return (int)$typeId;
    }

    /**
     * Get a map of Content Type storage column name to its value and parameter type.
     *
     * Key value of the map is represented as a two-elements array with column value and its type.
     */
    private function mapCommonContentTypeColumnsToQueryValuesAndTypes(Type $type): array
    {
        return [
            'serialized_name_list' => [serialize($type->name), ParameterType::STRING],
            'serialized_description_list' => [serialize($type->description), ParameterType::STRING],
            'identifier' => [$type->identifier, ParameterType::STRING],
            'modified' => [$type->modified, ParameterType::INTEGER],
            'modifier_id' => [$type->modifierId, ParameterType::INTEGER],
            'remote_id' => [$type->remoteId, ParameterType::STRING],
            'url_alias_name' => [$type->urlAliasSchema, ParameterType::STRING],
            'contentobject_name' => [$type->nameSchema, ParameterType::STRING],
            'is_container' => [(int)$type->isContainer, ParameterType::INTEGER],
            'language_mask' => [
                $this->languageMaskGenerator->generateLanguageMaskFromLanguageCodes(
                    $type->languageCodes,
                    array_key_exists('always-available', $type->name)
                ),
                ParameterType::INTEGER,
            ],
            'initial_language_id' => [$type->initialLanguageId, ParameterType::INTEGER],
            'sort_field' => [$type->sortField, ParameterType::INTEGER],
            'sort_order' => [$type->sortOrder, ParameterType::INTEGER],
            'always_available' => [(int)$type->defaultAlwaysAvailable, ParameterType::INTEGER],
        ];
    }

    public function insertGroupAssignment(int $groupId, int $typeId, int $status): void
    {
        $groups = $this->loadGroupData([$groupId]);
        if (empty($groups)) {
            throw new NotFoundException('Content Type Group', $groupId);
        }
        $group = $groups[0];

        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::CONTENT_TYPE_TO_GROUP_ASSIGNMENT_TABLE)
            ->values(
                [
                    'contentclass_id' => $query->createPositionalParameter(
                        $typeId,
                        ParameterType::INTEGER
                    ),
                    'contentclass_version' => $query->createPositionalParameter(
                        $status,
                        ParameterType::INTEGER
                    ),
                    'group_id' => $query->createPositionalParameter(
                        $groupId,
                        ParameterType::INTEGER
                    ),
                    'group_name' => $query->createPositionalParameter(
                        $group['name'],
                        ParameterType::STRING
                    ),
                ]
            );

        $query->execute();
    }

    public function deleteGroupAssignment(int $groupId, int $typeId, int $status): void
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->delete(self::CONTENT_TYPE_TO_GROUP_ASSIGNMENT_TABLE)
            ->where(
                $expr->eq(
                    'contentclass_id',
                    $query->createPositionalParameter($typeId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'contentclass_version',
                    $query->createPositionalParameter($status, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'group_id',
                    $query->createPositionalParameter($groupId, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    public function loadGroupData(array $groupIds): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('created', 'creator_id', 'id', 'modified', 'modifier_id', 'name')
            ->from(self::CONTENT_TYPE_GROUP_TABLE)
            ->where($query->expr()->in('id', ':ids'))
            ->setParameter('ids', $groupIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll();
    }

    public function loadGroupDataByIdentifier(string $identifier): array
    {
        $query = $this->createGroupLoadQuery();
        $query->where(
            $query->expr()->eq(
                'name',
                $query->createPositionalParameter($identifier, ParameterType::STRING)
            )
        );

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function loadAllGroupsData(): array
    {
        $query = $this->createGroupLoadQuery();

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Create the basic query to load Group data.
     */
    private function createGroupLoadQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(
            'created',
            'creator_id',
            'id',
            'modified',
            'modifier_id',
            'name'
        )->from(self::CONTENT_TYPE_GROUP_TABLE);

        return $query;
    }

    public function loadTypesDataForGroup(int $groupId, int $status): array
    {
        $query = $this->getLoadTypeQueryBuilder();
        $expr = $query->expr();
        $query
            ->where($expr->eq('g.group_id', ':gid'))
            ->andWhere($expr->eq('c.version', ':version'))
            ->addOrderBy('c.identifier')
            ->setParameter('gid', $groupId, ParameterType::INTEGER)
            ->setParameter('version', $status, ParameterType::INTEGER);

        return $query->execute()->fetchAll();
    }

    public function insertFieldDefinition(
        int $typeId,
        int $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ): int {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::FIELD_DEFINITION_TABLE)
            ->values(
                [
                    'contentclass_id' => $query->createNamedParameter(
                        $typeId,
                        ParameterType::INTEGER,
                        ':content_type_id'
                    ),
                    'version' => $query->createNamedParameter(
                        $status,
                        ParameterType::INTEGER,
                        ':status'
                    ),
                ]
            );
        $this->setNextAutoIncrementedValueIfAvailable(
            $query,
            self::FIELD_DEFINITION_TABLE,
            'id',
            self::FIELD_DEFINITION_SEQ,
            $fieldDefinition->id
        );
        $columnValueAndTypeMap = $this->mapCommonFieldDefinitionColumnsToQueryValuesAndTypes(
            $fieldDefinition,
            $storageFieldDef
        );
        foreach ($columnValueAndTypeMap as $columnName => $data) {
            [$columnValue, $parameterType] = $data;
            $query
                ->setValue($columnName, ":{$columnName}")
                ->setParameter($columnName, $columnValue, $parameterType);
        }

        $query->execute();

        $fieldDefinitionId = $fieldDefinition->id ?? $this->sharedGateway->getLastInsertedId(
            self::FIELD_DEFINITION_SEQ
        );

        foreach ($storageFieldDef->multilingualData as $multilingualData) {
            $this->insertFieldDefinitionMultilingualData(
                $fieldDefinitionId,
                $multilingualData,
                $status
            );
        }

        return $fieldDefinitionId;
    }

    private function insertFieldDefinitionMultilingualData(
        int $fieldDefinitionId,
        MultilingualStorageFieldDefinition $multilingualData,
        int $status
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::MULTILINGUAL_FIELD_DEFINITION_TABLE)
            ->values(
                [
                    'data_text' => ':data_text',
                    'data_json' => ':data_json',
                    'name' => ':name',
                    'description' => ':description',
                    'contentclass_attribute_id' => ':field_definition_id',
                    'version' => ':status',
                    'language_id' => ':language_id',
                ]
            )
            ->setParameter('data_text', $multilingualData->dataText)
            ->setParameter('data_json', $multilingualData->dataJson)
            ->setParameter('name', $multilingualData->name)
            ->setParameter('description', $multilingualData->description)
            ->setParameter('field_definition_id', $fieldDefinitionId, ParameterType::INTEGER)
            ->setParameter('status', $status, ParameterType::INTEGER)
            ->setParameter('language_id', $multilingualData->languageId, ParameterType::INTEGER);

        $query->execute();
    }

    /**
     * Get a map of Field Definition storage column name to its value and parameter type.
     *
     * Key value of the map is represented as a two-elements array with column value and its type.
     */
    private function mapCommonFieldDefinitionColumnsToQueryValuesAndTypes(
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ): array {
        return [
            'serialized_name_list' => [serialize($fieldDefinition->name), ParameterType::STRING],
            'serialized_description_list' => [
                serialize($fieldDefinition->description),
                ParameterType::STRING,
            ],
            'serialized_data_text' => [
                serialize($storageFieldDef->serializedDataText),
                ParameterType::STRING,
            ],
            'identifier' => [$fieldDefinition->identifier, ParameterType::STRING],
            'category' => [$fieldDefinition->fieldGroup, ParameterType::STRING],
            'placement' => [$fieldDefinition->position, ParameterType::INTEGER],
            'data_type_string' => [$fieldDefinition->fieldType, ParameterType::STRING],
            'can_translate' => [(int)$fieldDefinition->isTranslatable, ParameterType::INTEGER],
            'is_thumbnail' => [(bool)$fieldDefinition->isThumbnail, ParameterType::INTEGER],
            'is_required' => [(int)$fieldDefinition->isRequired, ParameterType::INTEGER],
            'is_information_collector' => [
                (int)$fieldDefinition->isInfoCollector,
                ParameterType::INTEGER,
            ],
            'is_searchable' => [(int)$fieldDefinition->isSearchable, ParameterType::INTEGER],
            'data_float1' => [$storageFieldDef->dataFloat1, null],
            'data_float2' => [$storageFieldDef->dataFloat2, null],
            'data_float3' => [$storageFieldDef->dataFloat3, null],
            'data_float4' => [$storageFieldDef->dataFloat4, null],
            'data_int1' => [$storageFieldDef->dataInt1, ParameterType::INTEGER],
            'data_int2' => [$storageFieldDef->dataInt2, ParameterType::INTEGER],
            'data_int3' => [$storageFieldDef->dataInt3, ParameterType::INTEGER],
            'data_int4' => [$storageFieldDef->dataInt4, ParameterType::INTEGER],
            'data_text1' => [$storageFieldDef->dataText1, ParameterType::STRING],
            'data_text2' => [$storageFieldDef->dataText2, ParameterType::STRING],
            'data_text3' => [$storageFieldDef->dataText3, ParameterType::STRING],
            'data_text4' => [$storageFieldDef->dataText4, ParameterType::STRING],
            'data_text5' => [$storageFieldDef->dataText5, ParameterType::STRING],
        ];
    }

    public function loadFieldDefinition(int $id, int $status): array
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $this
            ->selectColumns($query, self::FIELD_DEFINITION_TABLE, 'f_def')
            ->addSelect(
                [
                    'ct.initial_language_id AS ezcontentclass_initial_language_id',
                    'transl_f_def.name AS ezcontentclass_attribute_multilingual_name',
                    'transl_f_def.description AS ezcontentclass_attribute_multilingual_description',
                    'transl_f_def.language_id AS ezcontentclass_attribute_multilingual_language_id',
                    'transl_f_def.data_text AS ezcontentclass_attribute_multilingual_data_text',
                    'transl_f_def.data_json AS ezcontentclass_attribute_multilingual_data_json',
                ]
            )
            ->from(self::FIELD_DEFINITION_TABLE, 'f_def')
            ->leftJoin(
                'f_def',
                self::CONTENT_TYPE_TABLE,
                'ct',
                $expr->andX(
                    $expr->eq('f_def.contentclass_id', 'ct.id'),
                    $expr->eq('f_def.version', 'ct.version')
                )
            )
            ->leftJoin(
                'f_def',
                self::MULTILINGUAL_FIELD_DEFINITION_TABLE,
                'transl_f_def',
                $expr->andX(
                    $expr->eq(
                        'f_def.id',
                        'transl_f_def.contentclass_attribute_id'
                    ),
                    $expr->eq(
                        'f_def.version',
                        'transl_f_def.version'
                    )
                )
            )
            ->where(
                $expr->eq(
                    'f_def.id',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'f_def.version',
                    $query->createPositionalParameter($status, ParameterType::INTEGER)
                )
            );

        $stmt = $query->execute();

        return $stmt->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function deleteFieldDefinition(
        int $typeId,
        int $status,
        int $fieldDefinitionId
    ): void {
        // Delete multilingual data first to keep DB integrity
        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete(self::MULTILINGUAL_FIELD_DEFINITION_TABLE)
            ->where('contentclass_attribute_id = :field_definition_id')
            ->andWhere('version = :status')
            ->setParameter('field_definition_id', $fieldDefinitionId, ParameterType::INTEGER)
            ->setParameter('status', $status, ParameterType::INTEGER);

        $deleteQuery->execute();

        // Delete legacy Field Definition data
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::FIELD_DEFINITION_TABLE)
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($fieldDefinitionId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
            // in Legacy Storage Field Definition table the "version" column stores status (e.g. draft, published, modified)
                $query->expr()->eq(
                    'version',
                    $query->createPositionalParameter($status, ParameterType::INTEGER)
                )
            )
        ;

        $query->execute();
    }

    public function updateFieldDefinition(
        int $typeId,
        int $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::FIELD_DEFINITION_TABLE)
            ->where('id = :field_definition_id')
            ->andWhere('version = :status')
            ->setParameter('field_definition_id', $fieldDefinition->id, ParameterType::INTEGER)
            ->setParameter('status', $status, ParameterType::INTEGER);

        $fieldDefinitionValueAndTypeMap = $this->mapCommonFieldDefinitionColumnsToQueryValuesAndTypes(
            $fieldDefinition,
            $storageFieldDef
        );
        foreach ($fieldDefinitionValueAndTypeMap as $columnName => $data) {
            [$value, $parameterType] = $data;
            $query
                ->set(
                    $columnName,
                    $query->createNamedParameter($value, $parameterType, ":{$columnName}")
                );
        }

        $query->execute();

        foreach ($storageFieldDef->multilingualData as $data) {
            $dataExists = $this->fieldDefinitionMultilingualDataExist(
                $fieldDefinition,
                $data->languageId,
                $status
            );

            if ($dataExists) {
                $this->updateFieldDefinitionMultilingualData(
                    $fieldDefinition->id,
                    $data,
                    $status
                );
            } else {
                //When creating new translation there are no fields for update.
                $this->insertFieldDefinitionMultilingualData(
                    $fieldDefinition->id,
                    $data,
                    $status
                );
            }
        }
    }

    private function fieldDefinitionMultilingualDataExist(
        FieldDefinition $fieldDefinition,
        int $languageId,
        int $status
    ): bool {
        $existQuery = $this->connection->createQueryBuilder();
        $existQuery
            ->select($this->dbPlatform->getCountExpression('1'))
            ->from(self::MULTILINGUAL_FIELD_DEFINITION_TABLE)
            ->where('contentclass_attribute_id = :field_definition_id')
            ->andWhere('version = :status')
            ->andWhere('language_id = :language_id')
            ->setParameter('field_definition_id', $fieldDefinition->id, ParameterType::INTEGER)
            ->setParameter('status', $status, ParameterType::INTEGER)
            ->setParameter('language_id', $languageId, ParameterType::INTEGER);

        return 0 < (int)$existQuery->execute()->fetchColumn();
    }

    private function updateFieldDefinitionMultilingualData(
        int $fieldDefinitionId,
        MultilingualStorageFieldDefinition $multilingualData,
        int $status
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::MULTILINGUAL_FIELD_DEFINITION_TABLE)
            ->set('data_text', ':data_text')
            ->set('data_json', ':data_json')
            ->set('name', ':name')
            ->set('description', ':description')
            ->where('contentclass_attribute_id = :field_definition_id')
            ->andWhere('version = :status')
            ->andWhere('language_id = :languageId')
            ->setParameter('data_text', $multilingualData->dataText)
            ->setParameter('data_json', $multilingualData->dataJson)
            ->setParameter('name', $multilingualData->name)
            ->setParameter('description', $multilingualData->description)
            ->setParameter('field_definition_id', $fieldDefinitionId, ParameterType::INTEGER)
            ->setParameter('status', $status, ParameterType::INTEGER)
            ->setParameter('languageId', $multilingualData->languageId, ParameterType::INTEGER);

        $query->execute();
    }

    /**
     * Delete entire name data for the given Content Type of the given status.
     */
    private function deleteTypeNameData(int $typeId, int $typeStatus): void
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->delete(self::CONTENT_TYPE_NAME_TABLE)
            ->where(
                $expr->eq(
                    'contentclass_id',
                    $query->createPositionalParameter($typeId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'contentclass_version',
                    $query->createPositionalParameter($typeStatus, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    public function updateType(int $typeId, int $status, Type $type): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->update(self::CONTENT_TYPE_TABLE);

        $columnQueryValueAndTypeMap = $this->mapCommonContentTypeColumnsToQueryValuesAndTypes(
            $type
        );
        foreach ($columnQueryValueAndTypeMap as $columnName => $data) {
            [$value, $parameterType] = $data;
            $query
                ->set(
                    $columnName,
                    $query->createNamedParameter($value, $parameterType, ":{$columnName}")
                );
        }
        $expr = $query->expr();
        $query
            ->where(
                $expr->eq(
                    'id',
                    $query->createNamedParameter($typeId, ParameterType::INTEGER, ':id')
                )
            )
            ->andWhere(
                $expr->eq(
                    'version',
                    $query->createNamedParameter($status, ParameterType::INTEGER, ':status')
                )
            );

        $query->execute();

        $this->deleteTypeNameData($typeId, $status);
        $this->insertTypeNameData($typeId, $status, $type->name);
    }

    public function loadTypesListData(array $typeIds): array
    {
        $query = $this->getLoadTypeQueryBuilder();

        $query
            ->where($query->expr()->in('c.id', ':ids'))
            ->andWhere($query->expr()->eq('c.version', Type::STATUS_DEFINED))
            ->setParameter('ids', $typeIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll();
    }

    public function loadTypeData(int $typeId, int $status): array
    {
        $query = $this->getLoadTypeQueryBuilder();
        $expr = $query->expr();
        $query
            ->where($expr->eq('c.id', ':id'))
            ->andWhere($expr->eq('c.version', ':version'))
            ->setParameter('id', $typeId, ParameterType::INTEGER)
            ->setParameter('version', $status, ParameterType::INTEGER);

        return $query->execute()->fetchAll();
    }

    public function loadTypeDataByIdentifier(string $identifier, int $status): array
    {
        $query = $this->getLoadTypeQueryBuilder();
        $expr = $query->expr();
        $query
            ->where($expr->eq('c.identifier', ':identifier'))
            ->andWhere($expr->eq('c.version', ':version'))
            ->setParameter('identifier', $identifier, ParameterType::STRING)
            ->setParameter('version', $status, ParameterType::INTEGER);

        return $query->execute()->fetchAll();
    }

    public function loadTypeDataByRemoteId(string $remoteId, int $status): array
    {
        $query = $this->getLoadTypeQueryBuilder();
        $query
            ->where($query->expr()->eq('c.remote_id', ':remote'))
            ->andWhere($query->expr()->eq('c.version', ':version'))
            ->setParameter('remote', $remoteId, ParameterType::STRING)
            ->setParameter('version', $status, ParameterType::INTEGER);

        return $query->execute()->fetchAll();
    }

    /**
     * Return a basic query to retrieve Type data.
     */
    private function getLoadTypeQueryBuilder(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select(
                [
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
                    'c.language_mask AS ezcontentclass_language_mask',

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
                    'a.is_thumbnail AS ezcontentclass_attribute_is_thumbnail',
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

                    'ml.name AS ezcontentclass_attribute_multilingual_name',
                    'ml.description AS ezcontentclass_attribute_multilingual_description',
                    'ml.language_id AS ezcontentclass_attribute_multilingual_language_id',
                    'ml.data_text AS ezcontentclass_attribute_multilingual_data_text',
                    'ml.data_json AS ezcontentclass_attribute_multilingual_data_json',
                ]
            )
            ->from(self::CONTENT_TYPE_TABLE, 'c')
            ->leftJoin(
                'c',
                self::FIELD_DEFINITION_TABLE,
                'a',
                $expr->andX(
                    $expr->eq('c.id', 'a.contentclass_id'),
                    $expr->eq('c.version', 'a.version')
                )
            )
            ->leftJoin(
                'c',
                self::CONTENT_TYPE_TO_GROUP_ASSIGNMENT_TABLE,
                'g',
                $expr->andX(
                    $expr->eq('c.id', 'g.contentclass_id'),
                    $expr->eq('c.version', 'g.contentclass_version')
                )
            )
            ->leftJoin(
                'a',
                self::MULTILINGUAL_FIELD_DEFINITION_TABLE,
                'ml',
                $expr->andX(
                    $expr->eq('a.id', 'ml.contentclass_attribute_id'),
                    $expr->eq('a.version', 'ml.version')
                )
            )
            ->orderBy('a.placement');

        return $query;
    }

    public function countInstancesOfType(int $typeId): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->dbPlatform->getCountExpression('id'))
            ->from('ezcontentobject')
            ->where(
                $query->expr()->eq(
                    'contentclass_id',
                    $query->createPositionalParameter($typeId, ParameterType::INTEGER)
                )
            );

        $stmt = $query->execute();

        return (int)$stmt->fetchColumn();
    }

    public function deleteFieldDefinitionsForType(int $typeId, int $status): void
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->delete(self::FIELD_DEFINITION_TABLE)
            ->where(
                $query->expr()->eq(
                    'contentclass_id',
                    $query->createPositionalParameter($typeId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'version',
                    $query->createPositionalParameter($status, ParameterType::INTEGER)
                )
            );

        $query->execute();

        $subQuery = $this->connection->createQueryBuilder();
        $subQuery
            ->select('f_def.id as ezcontentclass_attribute_id')
            ->from(self::FIELD_DEFINITION_TABLE, 'f_def')
            ->where('f_def.contentclass_id = :content_type_id')
            ->andWhere('f_def.id = ezcontentclass_attribute_ml.contentclass_attribute_id');

        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete(self::MULTILINGUAL_FIELD_DEFINITION_TABLE)
            ->where(
                sprintf('EXISTS (%s)', $subQuery->getSQL())
            )
            // note: not all drivers support aliasing tables in DELETE query, hence the following:
            ->andWhere(sprintf('%s.version = :status', self::MULTILINGUAL_FIELD_DEFINITION_TABLE))
            ->setParameter('content_type_id', $typeId, ParameterType::INTEGER)
            ->setParameter('status', $status, ParameterType::INTEGER);

        $deleteQuery->execute();
    }

    public function delete(int $typeId, int $status): void
    {
        $this->deleteGroupAssignmentsForType($typeId, $status);
        $this->deleteFieldDefinitionsForType($typeId, $status);
        $this->deleteTypeNameData($typeId, $status);
        $this->deleteType($typeId, $status);
    }

    public function deleteType(int $typeId, int $status): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::CONTENT_TYPE_TABLE)
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq(
                        'id',
                        $query->createPositionalParameter($typeId, ParameterType::INTEGER)
                    ),
                    $query->expr()->eq(
                        'version',
                        $query->createPositionalParameter($status, ParameterType::INTEGER)
                    )
                )
            );
        $query->execute();
    }

    public function deleteGroupAssignmentsForType(int $typeId, int $status): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::CONTENT_TYPE_TO_GROUP_ASSIGNMENT_TABLE)
            ->where(
                $query->expr()->eq(
                    'contentclass_id',
                    $query->createPositionalParameter($typeId, ParameterType::INTEGER)
                )
            )->andWhere(
                $query->expr()->eq(
                    'contentclass_version',
                    $query->createPositionalParameter($status, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    /**
     * Append all columns of a given table to the SELECT part of a query.
     *
     * Each column is aliased in the form of
     * <code><column_name> AS <table_name>_<column_name></code>.
     */
    private function selectColumns(
        QueryBuilder $queryBuilder,
        string $tableName,
        string $tableAlias = ''
    ): QueryBuilder {
        if (empty($tableAlias)) {
            $tableAlias = $tableName;
        }
        $queryBuilder
            ->addSelect(
                array_map(
                    function (string $columnName) use ($tableName, $tableAlias): string {
                        return sprintf(
                            '%s.%s as %s_%s',
                            $tableAlias,
                            $this->connection->quoteIdentifier($columnName),
                            $tableName,
                            $columnName
                        );
                    },
                    $this->columns[$tableName]
                )
            );

        return $queryBuilder;
    }

    public function internalChangeContentTypeStatus(
        int $typeId,
        int $sourceStatus,
        int $targetStatus,
        string $tableName,
        string $typeIdColumnName,
        string $statusColumnName
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update($tableName)
            ->set(
                $statusColumnName,
                $query->createPositionalParameter($targetStatus, ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->eq(
                    $typeIdColumnName,
                    $query->createPositionalParameter($typeId, ParameterType::INTEGER)
                )
            )->andWhere(
                $query->expr()->eq(
                    $statusColumnName,
                    $query->createPositionalParameter($sourceStatus, ParameterType::INTEGER)
                )
            );

        $query->execute();
    }

    public function publishTypeAndFields(int $typeId, int $sourceStatus, int $targetStatus): void
    {
        $this->internalChangeContentTypeStatus(
            $typeId,
            $sourceStatus,
            $targetStatus,
            self::CONTENT_TYPE_TABLE,
            'id',
            'version'
        );

        $this->internalChangeContentTypeStatus(
            $typeId,
            $sourceStatus,
            $targetStatus,
            self::CONTENT_TYPE_TO_GROUP_ASSIGNMENT_TABLE,
            'contentclass_id',
            'contentclass_version'
        );

        $this->internalChangeContentTypeStatus(
            $typeId,
            $sourceStatus,
            $targetStatus,
            self::FIELD_DEFINITION_TABLE,
            'contentclass_id',
            'version'
        );

        $this->internalChangeContentTypeStatus(
            $typeId,
            $sourceStatus,
            $targetStatus,
            self::CONTENT_TYPE_NAME_TABLE,
            'contentclass_id',
            'contentclass_version'
        );

        $subQuery = $this->connection->createQueryBuilder();
        $subQuery
            ->select('f_def.id as ezcontentclass_attribute_id')
            ->from(self::FIELD_DEFINITION_TABLE, 'f_def')
            ->where('f_def.contentclass_id = :type_id')
            ->andWhere('f_def.id = ezcontentclass_attribute_ml.contentclass_attribute_id');

        $mlDataPublishQuery = $this->connection->createQueryBuilder();
        $mlDataPublishQuery
            ->update(self::MULTILINGUAL_FIELD_DEFINITION_TABLE)
            ->set('version', ':target_status')
            ->where(
                sprintf('EXISTS (%s)', $subQuery->getSQL())
            )
            // note: not all drivers support aliasing tables in UPDATE query, hence the following:
            ->andWhere(
                sprintf('%s.version = :source_status', self::MULTILINGUAL_FIELD_DEFINITION_TABLE)
            )
            ->setParameter('type_id', $typeId, ParameterType::INTEGER)
            ->setParameter('target_status', $targetStatus, ParameterType::INTEGER)
            ->setParameter('source_status', $sourceStatus, ParameterType::INTEGER);

        $mlDataPublishQuery->execute();
    }

    public function getSearchableFieldMapData(): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                'f_def.identifier AS field_definition_identifier',
                'ct.identifier AS content_type_identifier',
                'f_def.id AS field_definition_id',
                'f_def.data_type_string AS field_type_identifier'
            )
            ->from(self::FIELD_DEFINITION_TABLE, 'f_def')
            ->innerJoin('f_def', self::CONTENT_TYPE_TABLE, 'ct', 'f_def.contentclass_id = ct.id')
            ->where(
                $query->expr()->eq(
                    'f_def.is_searchable',
                    $query->createPositionalParameter(1, ParameterType::INTEGER)
                )
            );

        $statement = $query->execute($query);

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function removeFieldDefinitionTranslation(
        int $fieldDefinitionId,
        string $languageCode,
        int $status
    ): void {
        $languageId = $this->languageMaskGenerator->generateLanguageMaskFromLanguageCodes(
            [$languageCode]
        );

        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete(self::MULTILINGUAL_FIELD_DEFINITION_TABLE)
            ->where('contentclass_attribute_id = :field_definition_id')
            ->andWhere('version = :status')
            ->andWhere('language_id = :language_id')
            ->setParameter('field_definition_id', $fieldDefinitionId, ParameterType::INTEGER)
            ->setParameter('status', $status, ParameterType::INTEGER)
            ->setParameter('language_id', $languageId, ParameterType::INTEGER);

        $deleteQuery->execute();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function removeByUserAndVersion(int $userId, int $version): void
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->delete(self::CONTENT_TYPE_TABLE)
            ->where('creator_id = :user or modifier_id = :user')
            ->andWhere('version = :version')
            ->setParameter('user', $userId, ParameterType::INTEGER)
            ->setParameter('version', $version, ParameterType::INTEGER)
        ;

        try {
            $this->connection->beginTransaction();

            $queryBuilder->execute();
            $this->cleanupAssociations();

            $this->connection->commit();
        } catch (DBALException $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function cleanupAssociations(): void
    {
        $this->cleanupClassAttributeTable();
        $this->cleanupClassAttributeMLTable();
        $this->cleanupClassGroupTable();
        $this->cleanupClassNameTable();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function cleanupClassAttributeTable(): void
    {
        $sql = <<<SQL
          DELETE FROM ezcontentclass_attribute
            WHERE NOT EXISTS (
              SELECT 1 FROM ezcontentclass
                WHERE ezcontentclass.id = ezcontentclass_attribute.contentclass_id 
                AND ezcontentclass.version = ezcontentclass_attribute.version
            )
SQL;
        $this->connection->executeUpdate($sql);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function cleanupClassAttributeMLTable(): void
    {
        $sql = <<<SQL
          DELETE FROM ezcontentclass_attribute_ml 
            WHERE NOT EXISTS (
              SELECT 1 FROM ezcontentclass_attribute 
                WHERE ezcontentclass_attribute.id = ezcontentclass_attribute_ml.contentclass_attribute_id 
                AND ezcontentclass_attribute.version = ezcontentclass_attribute_ml.version
            )
SQL;
        $this->connection->executeUpdate($sql);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function cleanupClassGroupTable(): void
    {
        $sql = <<<SQL
          DELETE FROM ezcontentclass_classgroup 
            WHERE NOT EXISTS (
              SELECT 1 FROM ezcontentclass 
                WHERE ezcontentclass.id = ezcontentclass_classgroup.contentclass_id 
                AND ezcontentclass.version = ezcontentclass_classgroup.contentclass_version
            )
SQL;
        $this->connection->executeUpdate($sql);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function cleanupClassNameTable(): void
    {
        $sql = <<< SQL
          DELETE FROM ezcontentclass_name 
            WHERE NOT EXISTS (
              SELECT 1 FROM ezcontentclass 
                WHERE ezcontentclass.id = ezcontentclass_name.contentclass_id 
                AND ezcontentclass.version = ezcontentclass_name.contentclass_version
            )
SQL;
        $this->connection->executeUpdate($sql);
    }
}
