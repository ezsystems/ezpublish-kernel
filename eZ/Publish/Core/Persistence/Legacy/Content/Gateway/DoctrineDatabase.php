<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\SharedGateway\Gateway as SharedGateway;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use DOMXPath;
use DOMDocument;
use PDO;

/**
 * Doctrine database based content gateway.
 *
 * @internal Gateway implementation is considered internal. Use Persistence Content Handler instead.
 *
 * @see \eZ\Publish\SPI\Persistence\Content\Handler
 */
final class DoctrineDatabase extends Gateway
{
    /**
     * The native Doctrine connection.
     *
     * Meant to be used to transition from eZ/Zeta interface to Doctrine.
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * Query builder.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Caching language handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected $languageHandler;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /** @var \eZ\Publish\Core\Persistence\Legacy\SharedGateway\Gateway */
    private $sharedGateway;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $databasePlatform;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection $connection,
        SharedGateway $sharedGateway,
        QueryBuilder $queryBuilder,
        LanguageHandler $languageHandler,
        LanguageMaskGenerator $languageMaskGenerator
    ) {
        $this->connection = $connection;
        $this->databasePlatform = $connection->getDatabasePlatform();
        $this->sharedGateway = $sharedGateway;
        $this->queryBuilder = $queryBuilder;
        $this->languageHandler = $languageHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    public function insertContentObject(CreateStruct $struct, int $currentVersionNo = 1): int
    {
        $initialLanguageId = !empty($struct->mainLanguageId) ? $struct->mainLanguageId : $struct->initialLanguageId;
        $initialLanguageCode = $this->languageHandler->load($initialLanguageId)->languageCode;

        $name = $struct->name[$initialLanguageCode] ?? '';

        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::CONTENT_ITEM_TABLE)
            ->values(
                [
                    'current_version' => $query->createPositionalParameter(
                        $currentVersionNo,
                        ParameterType::INTEGER
                    ),
                    'name' => $query->createPositionalParameter($name),
                    'contentclass_id' => $query->createPositionalParameter(
                        $struct->typeId,
                        ParameterType::INTEGER
                    ),
                    'section_id' => $query->createPositionalParameter(
                        $struct->sectionId,
                        ParameterType::INTEGER
                    ),
                    'owner_id' => $query->createPositionalParameter(
                        $struct->ownerId,
                        ParameterType::INTEGER
                    ),
                    'initial_language_id' => $query->createPositionalParameter(
                        $initialLanguageId,
                        ParameterType::INTEGER
                    ),
                    'remote_id' => $query->createPositionalParameter($struct->remoteId),
                    'modified' => $query->createPositionalParameter(0, ParameterType::INTEGER),
                    'published' => $query->createPositionalParameter(0, ParameterType::INTEGER),
                    'status' => $query->createPositionalParameter(
                        ContentInfo::STATUS_DRAFT,
                        ParameterType::INTEGER
                    ),
                    'language_mask' => $query->createPositionalParameter(
                        $this->languageMaskGenerator->generateLanguageMaskForFields(
                            $struct->fields,
                            $initialLanguageCode,
                            $struct->alwaysAvailable
                        ),
                        ParameterType::INTEGER
                    ),
                ]
            );

        $query->execute();

        return (int)$this->connection->lastInsertId(self::CONTENT_ITEM_SEQ);
    }

    public function insertVersion(VersionInfo $versionInfo, array $fields): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::CONTENT_VERSION_TABLE)
            ->values(
                [
                    'version' => $query->createPositionalParameter(
                        $versionInfo->versionNo,
                        ParameterType::INTEGER
                    ),
                    'modified' => $query->createPositionalParameter(
                        $versionInfo->modificationDate,
                        ParameterType::INTEGER
                    ),
                    'creator_id' => $query->createPositionalParameter(
                        $versionInfo->creatorId,
                        ParameterType::INTEGER
                    ),
                    'created' => $query->createPositionalParameter(
                        $versionInfo->creationDate,
                        ParameterType::INTEGER
                    ),
                    'status' => $query->createPositionalParameter(
                        $versionInfo->status,
                        ParameterType::INTEGER
                    ),
                    'initial_language_id' => $query->createPositionalParameter(
                        $this->languageHandler->loadByLanguageCode(
                            $versionInfo->initialLanguageCode
                        )->id,
                        ParameterType::INTEGER
                    ),
                    'contentobject_id' => $query->createPositionalParameter(
                        $versionInfo->contentInfo->id,
                        ParameterType::INTEGER
                    ),
                    'language_mask' => $query->createPositionalParameter(
                        $this->languageMaskGenerator->generateLanguageMaskForFields(
                            $fields,
                            $versionInfo->initialLanguageCode,
                            $versionInfo->contentInfo->alwaysAvailable
                        ),
                        ParameterType::INTEGER
                    ),
                ]
            );

        $query->execute();

        return (int)$this->connection->lastInsertId(self::CONTENT_VERSION_SEQ);
    }

    public function updateContent(
        int $contentId,
        MetadataUpdateStruct $struct,
        ?VersionInfo $prePublishVersionInfo = null
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query->update(self::CONTENT_ITEM_TABLE);

        $fieldsForUpdateMap = [
            'name' => [
                'value' => $struct->name,
                'type' => ParameterType::STRING,
            ],
            'initial_language_id' => [
                'value' => $struct->mainLanguageId,
                'type' => ParameterType::INTEGER,
            ],
            'modified' => [
                'value' => $struct->modificationDate,
                'type' => ParameterType::INTEGER,
            ],
            'owner_id' => [
                'value' => $struct->ownerId,
                'type' => ParameterType::INTEGER,
            ],
            'published' => [
                'value' => $struct->publicationDate,
                'type' => ParameterType::INTEGER,
            ],
            'remote_id' => [
                'value' => $struct->remoteId,
                'type' => ParameterType::STRING,
            ],
            'is_hidden' => [
                'value' => $struct->isHidden,
                'type' => ParameterType::BOOLEAN,
            ],
        ];

        foreach ($fieldsForUpdateMap as $fieldName => $field) {
            if (null === $field['value']) {
                continue;
            }
            $query->set(
                $fieldName,
                $query->createNamedParameter($field['value'], $field['type'], ":{$fieldName}")
            );
        }

        if ($prePublishVersionInfo !== null) {
            $mask = $this->languageMaskGenerator->generateLanguageMaskFromLanguageCodes(
                $prePublishVersionInfo->languageCodes,
                $struct->alwaysAvailable ?? $prePublishVersionInfo->contentInfo->alwaysAvailable
            );
            $query->set(
                'language_mask',
                $query->createNamedParameter($mask, ParameterType::INTEGER, ':languageMask')
            );
        }

        $query->where(
            $query->expr()->eq(
                'id',
                $query->createNamedParameter($contentId, ParameterType::INTEGER, ':contentId')
            )
        );

        if (!empty($query->getQueryPart('set'))) {
            $query->execute();
        }

        // Handle alwaysAvailable flag update separately as it's a more complex task and has impact on several tables
        if (isset($struct->alwaysAvailable) || isset($struct->mainLanguageId)) {
            $this->updateAlwaysAvailableFlag($contentId, $struct->alwaysAvailable);
        }
    }

    /**
     * Updates version $versionNo for content identified by $contentId, in respect to $struct.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function updateVersion(int $contentId, int $versionNo, UpdateStruct $struct): void
    {
        $query = $this->connection->createQueryBuilder();

        $query
            ->update(self::CONTENT_VERSION_TABLE)
            ->set('creator_id', ':creator_id')
            ->set('modified', ':modified')
            ->set('initial_language_id', ':initial_language_id')
            ->set(
                'language_mask',
                $this->databasePlatform->getBitOrComparisonExpression(
                    'language_mask',
                    ':language_mask'
                )
            )
            ->setParameter('creator_id', $struct->creatorId, ParameterType::INTEGER)
            ->setParameter('modified', $struct->modificationDate, ParameterType::INTEGER)
            ->setParameter(
                'initial_language_id',
                $struct->initialLanguageId,
                ParameterType::INTEGER
            )
            ->setParameter(
                'language_mask',
                $this->languageMaskGenerator->generateLanguageMaskForFields(
                    $struct->fields,
                    $this->languageHandler->load($struct->initialLanguageId)->languageCode,
                    false
                ),
                ParameterType::INTEGER
            )
            ->where('contentobject_id = :content_id')
            ->andWhere('version = :version_no')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER)
            ->setParameter('version_no', $versionNo, ParameterType::INTEGER);

        $query->execute();
    }

    public function updateAlwaysAvailableFlag(int $contentId, ?bool $alwaysAvailable = null): void
    {
        // We will need to know some info on the current language mask to update the flag
        // everywhere needed
        $contentInfoRow = $this->loadContentInfo($contentId);
        $versionNo = (int)$contentInfoRow['current_version'];
        $languageMask = (int)$contentInfoRow['language_mask'];
        $initialLanguageId = (int)$contentInfoRow['initial_language_id'];
        if (!isset($alwaysAvailable)) {
            $alwaysAvailable = 1 === ($languageMask & 1);
        }

        $this->updateContentItemAlwaysAvailableFlag($contentId, $alwaysAvailable);
        $this->updateContentNameAlwaysAvailableFlag(
            $contentId,
            $versionNo,
            $alwaysAvailable
        );
        $this->updateContentFieldsAlwaysAvailableFlag(
            $contentId,
            $versionNo,
            $alwaysAvailable,
            $languageMask,
            $initialLanguageId
        );
    }

    private function updateContentItemAlwaysAvailableFlag(
        int $contentId,
        bool $alwaysAvailable
    ): void {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->update(self::CONTENT_ITEM_TABLE)
            ->set(
                'language_mask',
                $alwaysAvailable
                    ? $this->databasePlatform->getBitOrComparisonExpression(
                    'language_mask',
                    ':languageMaskOperand'
                )
                    : $this->databasePlatform->getBitAndComparisonExpression(
                    'language_mask',
                    ':languageMaskOperand'
                )
            )
            ->setParameter('languageMaskOperand', $alwaysAvailable ? 1 : -2)
            ->where(
                $expr->eq(
                    'id',
                    $query->createNamedParameter($contentId, ParameterType::INTEGER, ':contentId')
                )
            );
        $query->execute();
    }

    private function updateContentNameAlwaysAvailableFlag(
        int $contentId,
        int $versionNo,
        bool $alwaysAvailable
    ): void {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->update(self::CONTENT_NAME_TABLE)
            ->set(
                'language_id',
                $alwaysAvailable
                    ? $this->databasePlatform->getBitOrComparisonExpression(
                    'language_id',
                    ':languageMaskOperand'
                )
                    : $this->databasePlatform->getBitAndComparisonExpression(
                    'language_id',
                    ':languageMaskOperand'
                )
            )
            ->setParameter('languageMaskOperand', $alwaysAvailable ? 1 : -2)
            ->where(
                $expr->eq(
                    'contentobject_id',
                    $query->createNamedParameter($contentId, ParameterType::INTEGER, ':contentId')
                )
            )
            ->andWhere(
                $expr->eq(
                    'content_version',
                    $query->createNamedParameter($versionNo, ParameterType::INTEGER, ':versionNo')
                )
            );
        $query->execute();
    }

    private function updateContentFieldsAlwaysAvailableFlag(
        int $contentId,
        int $versionNo,
        bool $alwaysAvailable,
        int $languageMask,
        int $initialLanguageId
    ): void {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->update(self::CONTENT_FIELD_TABLE)
            ->where(
                $expr->eq(
                    'contentobject_id',
                    $query->createNamedParameter($contentId, ParameterType::INTEGER, ':contentId')
                )
            )
            ->andWhere(
                $expr->eq(
                    'version',
                    $query->createNamedParameter($versionNo, ParameterType::INTEGER, ':versionNo')
                )
            );

        // If there is only a single language, update all fields and return
        if (!$this->languageMaskGenerator->isLanguageMaskComposite($languageMask)) {
            $query
                ->set(
                    'language_id',
                    $alwaysAvailable
                        ? $this->databasePlatform->getBitOrComparisonExpression(
                        'language_id',
                        ':languageMaskOperand'
                    )
                        : $this->databasePlatform->getBitAndComparisonExpression(
                        'language_id',
                        ':languageMaskOperand'
                    )
                )
                ->setParameter('languageMaskOperand', $alwaysAvailable ? 1 : -2);

            $query->execute();

            return;
        }

        // Otherwise:
        // 1. Remove always available flag on all fields
        $query
            ->set(
                'language_id',
                $this->databasePlatform->getBitAndComparisonExpression(
                    'language_id',
                    ':languageMaskOperand'
                )
            )
            ->setParameter('languageMaskOperand', -2)
        ;
        $query->execute();
        $query->resetQueryPart('set');

        // 2. If Content is always available set the flag only on fields in main language
        if ($alwaysAvailable) {
            $query
                ->set(
                    'language_id',
                    $this->databasePlatform->getBitOrComparisonExpression(
                        'language_id',
                        ':languageMaskOperand'
                    )
                )
                ->setParameter('languageMaskOperand', $alwaysAvailable ? 1 : -2);

            $query->andWhere(
                $expr->gt(
                    $this->databasePlatform->getBitAndComparisonExpression(
                        'language_id',
                        $query->createNamedParameter($initialLanguageId, ParameterType::INTEGER, ':initialLanguageId')
                    ),
                    $query->createNamedParameter(0, ParameterType::INTEGER, ':zero')
                )
            );
            $query->execute();
        }
    }

    public function setStatus(int $contentId, int $version, int $status): bool
    {
        if ($status !== APIVersionInfo::STATUS_PUBLISHED) {
            $query = $this->queryBuilder->getSetVersionStatusQuery($contentId, $version, $status);
            $rowCount = $query->execute();

            return $rowCount > 0;
        } else {
            // If the version's status is PUBLISHED, we use dedicated method for publishing
            $this->setPublishedStatus($contentId, $version);

            return true;
        }
    }

    public function setPublishedStatus(int $contentId, int $versionNo): void
    {
        $query = $this->queryBuilder->getSetVersionStatusQuery(
            $contentId,
            $versionNo,
            VersionInfo::STATUS_PUBLISHED
        );

        /* this part allows set status `published` only if there is no other published version of the content */
        $notExistPublishedVersion = <<<SQL
            NOT EXISTS (
                SELECT 1 FROM (
                    SELECT 1 FROM ezcontentobject_version
                    WHERE contentobject_id = :contentId AND status = :status
                ) as V
            )
            SQL;

        $query->andWhere($notExistPublishedVersion);
        if (0 === $query->execute()) {
            throw new BadStateException(
                '$contentId', "Someone just published another version of Content item {$contentId}"
            );
        }
        $this->markContentAsPublished($contentId, $versionNo);
    }

    private function markContentAsPublished(int $contentId, int $versionNo): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezcontentobject')
            ->set('status', ':status')
            ->set('current_version', ':versionNo')
            ->where('id =:contentId')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, ParameterType::INTEGER)
            ->setParameter('versionNo', $versionNo, ParameterType::INTEGER)
            ->setParameter('contentId', $contentId, ParameterType::INTEGER);
        $query->execute();
    }

    /**
     * @return int ID
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function insertNewField(Content $content, Field $field, StorageFieldValue $value): int
    {
        $query = $this->connection->createQueryBuilder();

        $this->setInsertFieldValues($query, $content, $field, $value);

        // Insert with auto increment ID
        $nextId = $this->sharedGateway->getColumnNextIntegerValue(
            self::CONTENT_FIELD_TABLE,
            'id',
            self::CONTENT_FIELD_SEQ
        );
        // avoid trying to insert NULL to trigger default column value behavior
        if (null !== $nextId) {
            $query
                ->setValue('id', ':field_id')
                ->setParameter('field_id', $nextId, ParameterType::INTEGER);
        }

        $query->execute();

        return (int)$this->sharedGateway->getLastInsertedId(self::CONTENT_FIELD_SEQ);
    }

    public function insertExistingField(
        Content $content,
        Field $field,
        StorageFieldValue $value
    ): void {
        $query = $this->connection->createQueryBuilder();

        $this->setInsertFieldValues($query, $content, $field, $value);

        $query
            ->setValue('id', ':field_id')
            ->setParameter('field_id', $field->id, ParameterType::INTEGER);

        $query->execute();
    }

    /**
     * Set the given query field (ezcontentobject_attribute) values.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function setInsertFieldValues(
        DoctrineQueryBuilder $query,
        Content $content,
        Field $field,
        StorageFieldValue $value
    ): void {
        $query
            ->insert(self::CONTENT_FIELD_TABLE)
            ->values(
                [
                    'contentobject_id' => ':content_id',
                    'contentclassattribute_id' => ':field_definition_id',
                    'data_type_string' => ':data_type_string',
                    'language_code' => ':language_code',
                    'version' => ':version_no',
                    'data_float' => ':data_float',
                    'data_int' => ':data_int',
                    'data_text' => ':data_text',
                    'sort_key_int' => ':sort_key_int',
                    'sort_key_string' => ':sort_key_string',
                    'language_id' => ':language_id',
                ]
            )
            ->setParameter(
                'content_id',
                $content->versionInfo->contentInfo->id,
                ParameterType::INTEGER
            )
            ->setParameter('field_definition_id', $field->fieldDefinitionId, ParameterType::INTEGER)
            ->setParameter('data_type_string', $field->type, ParameterType::STRING)
            ->setParameter('language_code', $field->languageCode, ParameterType::STRING)
            ->setParameter('version_no', $field->versionNo, ParameterType::INTEGER)
            ->setParameter('data_float', $value->dataFloat)
            ->setParameter('data_int', $value->dataInt, ParameterType::INTEGER)
            ->setParameter('data_text', $value->dataText, ParameterType::STRING)
            ->setParameter('sort_key_int', $value->sortKeyInt, ParameterType::INTEGER)
            ->setParameter(
                'sort_key_string',
                mb_substr((string)$value->sortKeyString, 0, 255),
                ParameterType::STRING
            )
            ->setParameter(
                'language_id',
                $this->languageMaskGenerator->generateLanguageIndicator(
                    $field->languageCode,
                    $this->isLanguageAlwaysAvailable($content, $field->languageCode)
                ),
                ParameterType::INTEGER
            );
    }

    /**
     * Check if $languageCode is always available in $content.
     */
    private function isLanguageAlwaysAvailable(Content $content, string $languageCode): bool
    {
        return
            $content->versionInfo->contentInfo->alwaysAvailable &&
            $content->versionInfo->contentInfo->mainLanguageCode === $languageCode
        ;
    }

    public function updateField(Field $field, StorageFieldValue $value): void
    {
        // Note, no need to care for language_id here, since Content->$alwaysAvailable
        // cannot change on update
        $query = $this->connection->createQueryBuilder();
        $this->setFieldUpdateValues($query, $value);
        $query
            ->where('id = :field_id')
            ->andWhere('version = :version_no')
            ->setParameter('field_id', $field->id, ParameterType::INTEGER)
            ->setParameter('version_no', $field->versionNo, ParameterType::INTEGER);

        $query->execute();
    }

    /**
     * Set update fields on $query based on $value.
     */
    private function setFieldUpdateValues(
        DoctrineQueryBuilder $query,
        StorageFieldValue $value
    ): void {
        $query
            ->update(self::CONTENT_FIELD_TABLE)
            ->set('data_float', ':data_float')
            ->set('data_int', ':data_int')
            ->set('data_text', ':data_text')
            ->set('sort_key_int', ':sort_key_int')
            ->set('sort_key_string', ':sort_key_string')
            ->setParameter('data_float', $value->dataFloat)
            ->setParameter('data_int', $value->dataInt, ParameterType::INTEGER)
            ->setParameter('data_text', $value->dataText, ParameterType::STRING)
            ->setParameter('sort_key_int', $value->sortKeyInt, ParameterType::INTEGER)
            ->setParameter('sort_key_string', mb_substr((string)$value->sortKeyString, 0, 255))
        ;
    }

    /**
     * Update an existing, non-translatable field.
     */
    public function updateNonTranslatableField(
        Field $field,
        StorageFieldValue $value,
        int $contentId
    ): void {
        // Note, no need to care for language_id here, since Content->$alwaysAvailable
        // cannot change on update
        $query = $this->connection->createQueryBuilder();
        $this->setFieldUpdateValues($query, $value);
        $query
            ->where('contentclassattribute_id = :field_definition_id')
            ->andWhere('contentobject_id = :content_id')
            ->andWhere('version = :version_no')
            ->setParameter('field_definition_id', $field->fieldDefinitionId, ParameterType::INTEGER)
            ->setParameter('content_id', $contentId, ParameterType::INTEGER)
            ->setParameter('version_no', $field->versionNo, ParameterType::INTEGER);

        $query->execute();
    }

    public function load(int $contentId, ?int $version = null, ?array $translations = null): array
    {
        return $this->internalLoadContent([$contentId], $version, $translations);
    }

    public function loadContentList(array $contentIds, ?array $translations = null): array
    {
        return $this->internalLoadContent($contentIds, null, $translations);
    }

    /**
     * Build query for the <code>load</code> and <code>loadContentList</code> methods.
     *
     * @param int[] $contentIds
     * @param string[]|null $translations a list of language codes
     *
     * @see load(), loadContentList()
     */
    private function internalLoadContent(
        array $contentIds,
        ?int $version = null,
        ?array $translations = null
    ): array {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr = $queryBuilder->expr();
        $queryBuilder
            ->select(
                'c.id AS ezcontentobject_id',
                'c.contentclass_id AS ezcontentobject_contentclass_id',
                'c.section_id AS ezcontentobject_section_id',
                'c.owner_id AS ezcontentobject_owner_id',
                'c.remote_id AS ezcontentobject_remote_id',
                'c.current_version AS ezcontentobject_current_version',
                'c.initial_language_id AS ezcontentobject_initial_language_id',
                'c.modified AS ezcontentobject_modified',
                'c.published AS ezcontentobject_published',
                'c.status AS ezcontentobject_status',
                'c.name AS ezcontentobject_name',
                'c.language_mask AS ezcontentobject_language_mask',
                'c.is_hidden AS ezcontentobject_is_hidden',
                'v.id AS ezcontentobject_version_id',
                'v.version AS ezcontentobject_version_version',
                'v.modified AS ezcontentobject_version_modified',
                'v.creator_id AS ezcontentobject_version_creator_id',
                'v.created AS ezcontentobject_version_created',
                'v.status AS ezcontentobject_version_status',
                'v.language_mask AS ezcontentobject_version_language_mask',
                'v.initial_language_id AS ezcontentobject_version_initial_language_id',
                'a.id AS ezcontentobject_attribute_id',
                'a.contentclassattribute_id AS ezcontentobject_attribute_contentclassattribute_id',
                'a.data_type_string AS ezcontentobject_attribute_data_type_string',
                'a.language_code AS ezcontentobject_attribute_language_code',
                'a.language_id AS ezcontentobject_attribute_language_id',
                'a.data_float AS ezcontentobject_attribute_data_float',
                'a.data_int AS ezcontentobject_attribute_data_int',
                'a.data_text AS ezcontentobject_attribute_data_text',
                'a.sort_key_int AS ezcontentobject_attribute_sort_key_int',
                'a.sort_key_string AS ezcontentobject_attribute_sort_key_string',
                't.main_node_id AS ezcontentobject_tree_main_node_id'
            )
            ->from('ezcontentobject', 'c')
            ->innerJoin(
                'c',
                'ezcontentobject_version',
                'v',
                $expr->andX(
                    $expr->eq('c.id', 'v.contentobject_id'),
                    $expr->eq('v.version', $version ?? 'c.current_version')
                )
            )
            ->innerJoin(
                'v',
                'ezcontentobject_attribute',
                'a',
                $expr->andX(
                    $expr->eq('v.contentobject_id', 'a.contentobject_id'),
                    $expr->eq('v.version', 'a.version')
                )
            )
            ->leftJoin(
                'c',
                'ezcontentobject_tree',
                't',
                $expr->andX(
                    $expr->eq('c.id', 't.contentobject_id'),
                    $expr->eq('t.node_id', 't.main_node_id')
                )
            );

        $queryBuilder->where(
            $expr->in(
                'c.id',
                $queryBuilder->createNamedParameter($contentIds, Connection::PARAM_INT_ARRAY)
            )
        );

        if (!empty($translations)) {
            $queryBuilder->andWhere(
                $expr->in(
                    'a.language_code',
                    $queryBuilder->createNamedParameter($translations, Connection::PARAM_STR_ARRAY)
                )
            );
        }

        return $queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function loadContentInfo(int $contentId): array
    {
        $queryBuilder = $this->queryBuilder->createLoadContentInfoQueryBuilder();
        $queryBuilder
            ->where('c.id = :id')
            ->setParameter('id', $contentId, ParameterType::INTEGER);

        $results = $queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE);
        if (empty($results)) {
            throw new NotFound('content', "id: $contentId");
        }

        return $results[0];
    }

    public function loadContentInfoList(array $contentIds): array
    {
        $queryBuilder = $this->queryBuilder->createLoadContentInfoQueryBuilder();
        $queryBuilder
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $contentIds, Connection::PARAM_INT_ARRAY);

        return $queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function loadContentInfoByRemoteId(string $remoteId): array
    {
        $queryBuilder = $this->queryBuilder->createLoadContentInfoQueryBuilder();
        $queryBuilder
            ->where('c.remote_id = :id')
            ->setParameter('id', $remoteId, ParameterType::STRING);

        $results = $queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE);
        if (empty($results)) {
            throw new NotFound('content', "remote_id: $remoteId");
        }

        return $results[0];
    }

    public function loadContentInfoByLocationId(int $locationId): array
    {
        $queryBuilder = $this->queryBuilder->createLoadContentInfoQueryBuilder(false);
        $queryBuilder
            ->where('t.node_id = :id')
            ->setParameter('id', $locationId, ParameterType::INTEGER);

        $results = $queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE);
        if (empty($results)) {
            throw new NotFound('content', "node_id: $locationId");
        }

        return $results[0];
    }

    public function loadVersionInfo(int $contentId, ?int $versionNo = null): array
    {
        $queryBuilder = $this->queryBuilder->createVersionInfoFindQueryBuilder();
        $expr = $queryBuilder->expr();

        $queryBuilder
            ->where(
                $expr->eq(
                    'v.contentobject_id',
                    $queryBuilder->createNamedParameter(
                        $contentId,
                        ParameterType::INTEGER,
                        ':content_id'
                    )
                )
            );

        if (null !== $versionNo) {
            $queryBuilder
                ->andWhere(
                    $expr->eq(
                        'v.version',
                        $queryBuilder->createNamedParameter(
                            $versionNo,
                            ParameterType::INTEGER,
                            ':version_no'
                        )
                    )
                );
        } else {
            $queryBuilder->andWhere($expr->eq('v.version', 'c.current_version'));
        }

        return $queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function countVersionsForUser(int $userId, int $status = VersionInfo::STATUS_DRAFT): int
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select($this->databasePlatform->getCountExpression('v.id'))
            ->from('ezcontentobject_version', 'v')
            ->innerJoin(
                'v',
                'ezcontentobject',
                'c',
                $expr->andX(
                    $expr->eq('c.id', 'v.contentobject_id'),
                    $expr->neq('c.status', ContentInfo::STATUS_TRASHED)
                )
            )
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('v.status', ':status'),
                    $query->expr()->eq('v.creator_id', ':user_id')
                )
            )
            ->setParameter(':status', $status, ParameterType::INTEGER)
            ->setParameter(':user_id', $userId, ParameterType::INTEGER);

        return (int) $query->execute()->fetchColumn();
    }

    /**
     * Return data for all versions with the given status created by the given $userId.
     *
     * @return string[][]
     */
    public function listVersionsForUser(int $userId, int $status = VersionInfo::STATUS_DRAFT): array
    {
        $query = $this->queryBuilder->createVersionInfoFindQueryBuilder();
        $query
            ->where('v.status = :status')
            ->andWhere('v.creator_id = :user_id')
            ->setParameter('status', $status, ParameterType::INTEGER)
            ->setParameter('user_id', $userId, ParameterType::INTEGER)
            ->orderBy('v.id');

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function loadVersionsForUser(
        int $userId,
        int $status = VersionInfo::STATUS_DRAFT,
        int $offset = 0,
        int $limit = -1
    ): array {
        $query = $this->queryBuilder->createVersionInfoFindQueryBuilder();
        $expr = $query->expr();
        $query->where(
            $expr->andX(
                $expr->eq('v.status', ':status'),
                $expr->eq('v.creator_id', ':user_id'),
                $expr->neq('c.status', ContentInfo::STATUS_TRASHED)
            )
        )
        ->setFirstResult($offset)
        ->setParameter(':status', $status, ParameterType::INTEGER)
        ->setParameter(':user_id', $userId, ParameterType::INTEGER);

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        $query->orderBy('v.modified', 'DESC');
        $query->addOrderBy('v.id', 'DESC');

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function listVersions(int $contentId, ?int $status = null, int $limit = -1): array
    {
        $query = $this->queryBuilder->createVersionInfoFindQueryBuilder();
        $query
            ->where('v.contentobject_id = :content_id')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER);

        if ($status !== null) {
            $query
                ->andWhere('v.status = :status')
                ->setParameter('status', $status);
        }

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        $query->orderBy('v.id');

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * @return int[]
     */
    public function listVersionNumbers(int $contentId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('version')
            ->from(self::CONTENT_VERSION_TABLE)
            ->where('contentobject_id = :contentId')
            ->groupBy('version')
            ->setParameter('contentId', $contentId, ParameterType::INTEGER);

        return array_map('intval', $query->execute()->fetchAll(FetchMode::COLUMN));
    }

    public function getLastVersionNumber(int $contentId): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->databasePlatform->getMaxExpression('version'))
            ->from(self::CONTENT_VERSION_TABLE)
            ->where('contentobject_id = :content_id')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER);

        $statement = $query->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * @return int[]
     */
    public function getAllLocationIds(int $contentId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('node_id')
            ->from('ezcontentobject_tree')
            ->where('contentobject_id = :content_id')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER);

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::COLUMN);
    }

    /**
     * @return int[][]
     */
    public function getFieldIdsByType(
        int $contentId,
        ?int $versionNo = null,
        ?string $languageCode = null
    ): array {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('id', 'data_type_string')
            ->from(self::CONTENT_FIELD_TABLE)
            ->where('contentobject_id = :content_id')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER);

        if (null !== $versionNo) {
            $query
                ->andWhere('version = :version_no')
                ->setParameter('version_no', $versionNo, ParameterType::INTEGER);
        }

        if (!empty($languageCode)) {
            $query
                ->andWhere('language_code = :language_code')
                ->setParameter('language_code', $languageCode, ParameterType::STRING);
        }

        $statement = $query->execute();

        $result = [];
        foreach ($statement->fetchAll(FetchMode::ASSOCIATIVE) as $row) {
            if (!isset($result[$row['data_type_string']])) {
                $result[$row['data_type_string']] = [];
            }
            $result[$row['data_type_string']][] = (int)$row['id'];
        }

        return $result;
    }

    public function deleteRelations(int $contentId, ?int $versionNo = null): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::CONTENT_RELATION_TABLE)
            ->where('from_contentobject_id = :content_id')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER);

        if (null !== $versionNo) {
            $query
                ->andWhere('from_contentobject_version = :version_no')
                ->setParameter('version_no', $versionNo, ParameterType::INTEGER);
        } else {
            $query->orWhere('to_contentobject_id = :content_id');
        }

        $query->execute();
    }

    public function removeReverseFieldRelations(int $contentId): void
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select(['a.id', 'a.version', 'a.data_type_string', 'a.data_text'])
            ->from(self::CONTENT_FIELD_TABLE, 'a')
            ->innerJoin(
                'a',
                'ezcontentobject_link',
                'l',
                $expr->andX(
                    'l.from_contentobject_id = a.contentobject_id',
                    'l.from_contentobject_version = a.version',
                    'l.contentclassattribute_id = a.contentclassattribute_id'
                )
            )
            ->where('l.to_contentobject_id = :content_id')
            ->andWhere(
                $expr->gt(
                    $this->databasePlatform->getBitAndComparisonExpression(
                        'l.relation_type',
                        ':relation_type'
                    ),
                    0
                )
            )
            ->setParameter('content_id', $contentId, ParameterType::INTEGER)
            ->setParameter('relation_type', Relation::FIELD, ParameterType::INTEGER);

        $statement = $query->execute();

        while ($row = $statement->fetch(FetchMode::ASSOCIATIVE)) {
            if ($row['data_type_string'] === 'ezobjectrelation') {
                $this->removeRelationFromRelationField($row);
            }

            if ($row['data_type_string'] === 'ezobjectrelationlist') {
                $this->removeRelationFromRelationListField($contentId, $row);
            }
        }
    }

    /**
     * Update field value of RelationList field type identified by given $row data,
     * removing relations toward given $contentId.
     *
     * @param array $row
     */
    private function removeRelationFromRelationListField(int $contentId, array $row): void
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->loadXML($row['data_text']);

        $xpath = new DOMXPath($document);
        $xpathExpression = "//related-objects/relation-list/relation-item[@contentobject-id='{$contentId}']";

        $relationItems = $xpath->query($xpathExpression);
        foreach ($relationItems as $relationItem) {
            $relationItem->parentNode->removeChild($relationItem);
        }

        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_FIELD_TABLE)
            ->set('data_text', ':data_text')
            ->setParameter('data_text', $document->saveXML(), ParameterType::STRING)
            ->where('id = :attribute_id')
            ->andWhere('version = :version_no')
            ->setParameter('attribute_id', (int)$row['id'], ParameterType::INTEGER)
            ->setParameter('version_no', (int)$row['version'], ParameterType::INTEGER);

        $query->execute();
    }

    /**
     * Update field value of Relation field type identified by given $row data,
     * removing relation data.
     *
     * @param array $row
     */
    private function removeRelationFromRelationField(array $row): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_FIELD_TABLE)
            ->set('data_int', ':data_int')
            ->set('sort_key_int', ':sort_key_int')
            ->setParameter('data_int', null, ParameterType::NULL)
            ->setParameter('sort_key_int', 0, ParameterType::INTEGER)
            ->where('id = :attribute_id')
            ->andWhere('version = :version_no')
            ->setParameter('attribute_id', (int)$row['id'], ParameterType::INTEGER)
            ->setParameter('version_no', (int)$row['version'], ParameterType::INTEGER);

        $query->execute();
    }

    public function deleteField(int $fieldId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::CONTENT_FIELD_TABLE)
            ->where('id = :field_id')
            ->setParameter('field_id', $fieldId, ParameterType::INTEGER)
        ;

        $query->execute();
    }

    public function deleteFields(int $contentId, ?int $versionNo = null): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::CONTENT_FIELD_TABLE)
            ->where('contentobject_id = :content_id')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER);

        if (null !== $versionNo) {
            $query
                ->andWhere('version = :version_no')
                ->setParameter('version_no', $versionNo, ParameterType::INTEGER);
        }

        $query->execute();
    }

    public function deleteVersions(int $contentId, ?int $versionNo = null): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::CONTENT_VERSION_TABLE)
            ->where('contentobject_id = :content_id')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER);

        if (null !== $versionNo) {
            $query
                ->andWhere('version = :version_no')
                ->setParameter('version_no', $versionNo, ParameterType::INTEGER);
        }

        $query->execute();
    }

    public function deleteNames(int $contentId, int $versionNo = null): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::CONTENT_NAME_TABLE)
            ->where('contentobject_id = :content_id')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER);

        if (isset($versionNo)) {
            $query
                ->andWhere('content_version = :version_no')
                ->setParameter('version_no', $versionNo, ParameterType::INTEGER);
        }

        $query->execute();
    }

    /**
     * Query Content name table to find if a name record for the given parameters exists.
     */
    private function contentNameExists(int $contentId, int $version, string $languageCode): bool
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->databasePlatform->getCountExpression('contentobject_id'))
            ->from(self::CONTENT_NAME_TABLE)
            ->where('contentobject_id = :content_id')
            ->andWhere('content_version = :version_no')
            ->andWhere('content_translation = :language_code')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER)
            ->setParameter('version_no', $version, ParameterType::INTEGER)
            ->setParameter('language_code', $languageCode, ParameterType::STRING);

        $stmt = $query->execute();

        return (int)$stmt->fetch(FetchMode::COLUMN) > 0;
    }

    public function setName(int $contentId, int $version, string $name, string $languageCode): void
    {
        $language = $this->languageHandler->loadByLanguageCode($languageCode);

        $query = $this->connection->createQueryBuilder();

        // prepare parameters
        $query
            ->setParameter('name', $name, ParameterType::STRING)
            ->setParameter('content_id', $contentId, ParameterType::INTEGER)
            ->setParameter('version_no', $version, ParameterType::INTEGER)
            ->setParameter('language_id', $language->id, ParameterType::INTEGER)
            ->setParameter('language_code', $language->languageCode, ParameterType::STRING)
        ;

        if (!$this->contentNameExists($contentId, $version, $language->languageCode)) {
            $query
                ->insert(self::CONTENT_NAME_TABLE)
                ->values(
                    [
                        'contentobject_id' => ':content_id',
                        'content_version' => ':version_no',
                        'content_translation' => ':language_code',
                        'name' => ':name',
                        'language_id' => $this->getSetNameLanguageMaskSubQuery(),
                        'real_translation' => ':language_code',
                    ]
                );
        } else {
            $query
                ->update(self::CONTENT_NAME_TABLE)
                ->set('name', ':name')
                ->set('language_id', $this->getSetNameLanguageMaskSubQuery())
                ->set('real_translation', ':language_code')
                ->where('contentobject_id = :content_id')
                ->andWhere('content_version = :version_no')
                ->andWhere('content_translation = :language_code');
        }

        $query->execute();
    }

    /**
     * Return a language sub select query for setName.
     *
     * The query generates the proper language mask at the runtime of the INSERT/UPDATE query
     * generated by setName.
     *
     * @see setName
     */
    private function getSetNameLanguageMaskSubQuery(): string
    {
        return <<<SQL
            (SELECT
                CASE
                    WHEN (initial_language_id = :language_id AND (language_mask & :language_id) <> 0 )
                    THEN (:language_id | 1)
                    ELSE :language_id
                END
                FROM ezcontentobject
                WHERE id = :content_id)
            SQL;
    }

    public function deleteContent(int $contentId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::CONTENT_ITEM_TABLE)
            ->where('id = :content_id')
            ->setParameter('content_id', $contentId, ParameterType::INTEGER)
        ;

        $query->execute();
    }

    public function loadRelations(
        int $contentId,
        ?int $contentVersionNo = null,
        ?int $relationType = null
    ): array {
        $query = $this->queryBuilder->createRelationFindQueryBuilder();
        $expr = $query->expr();
        $query
            ->innerJoin(
                'l',
                'ezcontentobject',
                'ezcontentobject_to',
                $expr->andX(
                    'l.to_contentobject_id = ezcontentobject_to.id',
                    'ezcontentobject_to.status = :status'
                )
            )
            ->where(
                'l.from_contentobject_id = :content_id'
            )
            ->setParameter(
                'status',
                ContentInfo::STATUS_PUBLISHED,
                ParameterType::INTEGER
            )
            ->setParameter('content_id', $contentId, ParameterType::INTEGER);

        // source version number
        if (null !== $contentVersionNo) {
            $query
                ->andWhere('l.from_contentobject_version = :version_no')
                ->setParameter('version_no', $contentVersionNo, ParameterType::INTEGER);
        } else {
            // from published version only
            $query
                ->innerJoin(
                    'ezcontentobject_to',
                    'ezcontentobject',
                    'c',
                    $expr->andX(
                        'c.id = l.from_contentobject_id',
                        'c.current_version = l.from_contentobject_version'
                    )
                );
        }

        // relation type
        if (null !== $relationType) {
            $query
                ->andWhere(
                    $expr->gt(
                        $this->databasePlatform->getBitAndComparisonExpression(
                            'l.relation_type',
                            ':relation_type'
                        ),
                        0
                    )
                )
                ->setParameter('relation_type', $relationType, ParameterType::INTEGER);
        }

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function countReverseRelations(int $toContentId, ?int $relationType = null): int
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select($this->databasePlatform->getCountExpression('l.id'))
            ->from(self::CONTENT_RELATION_TABLE, 'l')
            ->innerJoin(
                'l',
                'ezcontentobject',
                'c',
                $expr->andX(
                    $expr->eq('l.from_contentobject_id', 'c.id'),
                    $expr->eq('l.from_contentobject_version', 'c.current_version'),
                    $expr->eq('c.status', ':status')
                )
            )
            ->where(
                $expr->eq('l.to_contentobject_id', ':to_content_id')
            )
            ->setParameter('to_content_id', $toContentId, ParameterType::INTEGER)
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, ParameterType::INTEGER)
        ;

        // relation type
        if ($relationType !== null) {
            $query->andWhere(
                $expr->gt(
                    $this->databasePlatform->getBitAndComparisonExpression(
                        'l.relation_type',
                        $relationType
                    ),
                    0
                )
            );
        }

        return (int)$query->execute()->fetchColumn();
    }

    public function loadReverseRelations(int $toContentId, ?int $relationType = null): array
    {
        $query = $this->queryBuilder->createRelationFindQueryBuilder();
        $expr = $query->expr();
        $query
            ->join(
                'l',
                'ezcontentobject',
                'c',
                $expr->andX(
                    'c.id = l.from_contentobject_id',
                    'c.current_version = l.from_contentobject_version',
                    'c.status = :status'
                )
            )
            ->where('l.to_contentobject_id = :to_content_id')
            ->setParameter('to_content_id', $toContentId, ParameterType::INTEGER)
            ->setParameter(
                'status',
                ContentInfo::STATUS_PUBLISHED,
                ParameterType::INTEGER
            );

        // relation type
        if (null !== $relationType) {
            $query->andWhere(
                $expr->gt(
                    $this->databasePlatform->getBitAndComparisonExpression(
                        'l.relation_type',
                        ':relation_type'
                    ),
                    0
                )
            )
                ->setParameter('relation_type', $relationType, ParameterType::INTEGER);
        }

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function listReverseRelations(
        int $toContentId,
        int $offset = 0,
        int $limit = -1,
        ?int $relationType = null
    ): array {
        $query = $this->queryBuilder->createRelationFindQueryBuilder();
        $expr = $query->expr();
        $query
            ->innerJoin(
                'l',
                'ezcontentobject',
                'c',
                $expr->andX(
                    $expr->eq('l.from_contentobject_id', 'c.id'),
                    $expr->eq('l.from_contentobject_version', 'c.current_version'),
                    $expr->eq('c.status', ContentInfo::STATUS_PUBLISHED)
                )
            )
            ->where(
                $expr->eq('l.to_contentobject_id', ':toContentId')
            )
            ->setParameter(':toContentId', $toContentId, ParameterType::INTEGER);

        // relation type
        if ($relationType !== null) {
            $query->andWhere(
                $expr->gt(
                    $this->databasePlatform->getBitAndComparisonExpression(
                        'l.relation_type',
                        $relationType
                    ),
                    0
                )
            );
        }
        $query->setFirstResult($offset);
        if ($limit > 0) {
            $query->setMaxResults($limit);
        }
        $query->orderBy('l.id', 'DESC');

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function insertRelation(RelationCreateStruct $createStruct): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::CONTENT_RELATION_TABLE)
            ->values(
                [
                    'contentclassattribute_id' => ':field_definition_id',
                    'from_contentobject_id' => ':from_content_id',
                    'from_contentobject_version' => ':from_version_no',
                    'relation_type' => ':relation_type',
                    'to_contentobject_id' => ':to_content_id',
                ]
            )
            ->setParameter(
                'field_definition_id',
                (int)$createStruct->sourceFieldDefinitionId,
                ParameterType::INTEGER
            )
            ->setParameter(
                'from_content_id',
                $createStruct->sourceContentId,
                ParameterType::INTEGER
            )
            ->setParameter(
                'from_version_no',
                $createStruct->sourceContentVersionNo,
                ParameterType::INTEGER
            )
            ->setParameter('relation_type', $createStruct->type, ParameterType::INTEGER)
            ->setParameter(
                'to_content_id',
                $createStruct->destinationContentId,
                ParameterType::INTEGER
            );

        $query->execute();

        return (int)$this->connection->lastInsertId(self::CONTENT_RELATION_SEQ);
    }

    public function deleteRelation(int $relationId, int $type): void
    {
        // Legacy Storage stores COMMON, LINK and EMBED types using bitmask, therefore first load
        // existing relation type by given $relationId for comparison
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('relation_type')
            ->from(self::CONTENT_RELATION_TABLE)
            ->where('id = :relation_id')
            ->setParameter('relation_id', $relationId, ParameterType::INTEGER)
        ;

        $loadedRelationType = $query->execute()->fetchColumn();

        if (!$loadedRelationType) {
            return;
        }

        $query = $this->connection->createQueryBuilder();
        // If relation type matches then delete
        if (((int)$loadedRelationType) === ((int)$type)) {
            $query
                ->delete(self::CONTENT_RELATION_TABLE)
                ->where('id = :relation_id')
                ->setParameter('relation_id', $relationId, ParameterType::INTEGER)
            ;

            $query->execute();
        } elseif ($loadedRelationType & $type) {
            // If relation type is composite update bitmask

            $query
                ->update(self::CONTENT_RELATION_TABLE)
                ->set(
                    'relation_type',
                    // make & operation removing given $type from the bitmask
                    $this->databasePlatform->getBitAndComparisonExpression(
                        'relation_type',
                        ':relation_type'
                    )
                )
                // set the relation type as needed for the above & expression
                ->setParameter('relation_type', ~$type, ParameterType::INTEGER)
                ->where('id = :relation_id')
                ->setParameter('relation_id', $relationId, ParameterType::INTEGER)
            ;

            $query->execute();
        }
    }

    /**
     * @return int[]
     */
    public function getContentIdsByContentTypeId(int $contentTypeId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('id')
            ->from(self::CONTENT_ITEM_TABLE)
            ->where('contentclass_id = :content_type_id')
            ->setParameter('content_type_id', $contentTypeId, ParameterType::INTEGER);

        $statement = $query->execute();

        return array_map('intval', $statement->fetchAll(FetchMode::COLUMN));
    }

    public function loadVersionedNameData(array $rows): array
    {
        $query = $this->queryBuilder->createNamesQuery();
        $expr = $query->expr();
        $conditions = [];
        foreach ($rows as $row) {
            $conditions[] = $expr->andX(
                $expr->eq(
                    'contentobject_id',
                    $query->createPositionalParameter($row['id'], ParameterType::INTEGER)
                ),
                $expr->eq(
                    'content_version',
                    $query->createPositionalParameter($row['version'], ParameterType::INTEGER)
                ),
            );
        }

        $query->where($expr->orX(...$conditions));

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function copyRelations(
        int $originalContentId,
        int $copiedContentId,
        ?int $versionNo = null
    ): void {
        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select(
                'l.contentclassattribute_id',
                ':copied_id',
                'l.from_contentobject_version',
                'l.relation_type',
                'l.to_contentobject_id'
            )
            ->from(self::CONTENT_RELATION_TABLE, 'l')
            ->where('l.from_contentobject_id = :original_id')
            ->setParameter('copied_id', $copiedContentId, ParameterType::INTEGER)
            ->setParameter('original_id', $originalContentId, ParameterType::INTEGER);

        if ($versionNo) {
            $selectQuery
                ->andWhere('l.from_contentobject_version = :version')
                ->setParameter(':version', $versionNo, ParameterType::INTEGER);
        }
        // Given we can retain all columns, we just create copies with new `from_contentobject_id` using INSERT INTO SELECT
        $insertQuery = <<<SQL
            INSERT INTO ezcontentobject_link (
                contentclassattribute_id,
                from_contentobject_id,
                from_contentobject_version,
                relation_type,
                to_contentobject_id
            )
            SQL;

        $insertQuery .= $selectQuery->getSQL();

        $this->connection->executeUpdate(
            $insertQuery,
            $selectQuery->getParameters(),
            $selectQuery->getParameterTypes()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteTranslationFromContent(int $contentId, string $languageCode): void
    {
        $language = $this->languageHandler->loadByLanguageCode($languageCode);

        $this->connection->beginTransaction();
        try {
            $this->deleteTranslationFromContentVersions($contentId, $language->id);
            $this->deleteTranslationFromContentNames($contentId, $languageCode);
            $this->deleteTranslationFromContentObject($contentId, $language->id);

            $this->connection->commit();
        } catch (DBALException $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function deleteTranslatedFields(
        string $languageCode,
        int $contentId,
        ?int $versionNo = null
    ): void {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('ezcontentobject_attribute')
            ->where('contentobject_id = :contentId')
            ->andWhere('language_code = :languageCode')
            ->setParameters(
                [
                    ':contentId' => $contentId,
                    ':languageCode' => $languageCode,
                ]
            )
        ;

        if (null !== $versionNo) {
            $query
                ->andWhere('version = :versionNo')
                ->setParameter(':versionNo', $versionNo)
            ;
        }

        $query->execute();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteTranslationFromVersion(
        int $contentId,
        int $versionNo,
        string $languageCode
    ): void {
        $language = $this->languageHandler->loadByLanguageCode($languageCode);

        $this->connection->beginTransaction();
        try {
            $this->deleteTranslationFromContentVersions($contentId, $language->id, $versionNo);
            $this->deleteTranslationFromContentNames($contentId, $languageCode, $versionNo);

            $this->connection->commit();
        } catch (DBALException $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Delete translation from the ezcontentobject_name table.
     *
     * @param int $versionNo optional, if specified, apply to this Version only.
     */
    private function deleteTranslationFromContentNames(
        int $contentId,
        string $languageCode,
        ?int $versionNo = null
    ) {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('ezcontentobject_name')
            ->where('contentobject_id=:contentId')
            ->andWhere('real_translation=:languageCode')
            ->setParameters(
                [
                    ':languageCode' => $languageCode,
                    ':contentId' => $contentId,
                ]
            )
        ;

        if (null !== $versionNo) {
            $query
                ->andWhere('content_version = :versionNo')
                ->setParameter(':versionNo', $versionNo)
            ;
        }

        $query->execute();
    }

    /**
     * Remove language from language_mask of ezcontentobject.
     *
     * @param int $contentId
     * @param int $languageId
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException
     */
    private function deleteTranslationFromContentObject($contentId, $languageId)
    {
        $query = $this->connection->createQueryBuilder();
        $query->update('ezcontentobject')
            // parameter for bitwise operation has to be placed verbatim (w/o binding) for this to work cross-DBMS
            ->set('language_mask', 'language_mask & ~ ' . $languageId)
            ->set('modified', ':now')
            ->where('id = :contentId')
            ->andWhere(
            // make sure removed translation is not the last one (incl. alwaysAvailable)
                $query->expr()->andX(
                    'language_mask & ~ ' . $languageId . ' <> 0',
                    'language_mask & ~ ' . $languageId . ' <> 1'
                )
            )
            ->setParameter(':now', time())
            ->setParameter(':contentId', $contentId)
        ;

        $rowCount = $query->execute();

        // no rows updated means that most likely somehow it was the last remaining translation
        if ($rowCount === 0) {
            throw new BadStateException(
                '$languageCode',
                'The provided translation is the only translation in this version'
            );
        }
    }

    /**
     * Remove language from language_mask of ezcontentobject_version and update initialLanguageId
     * if it matches the removed one.
     *
     * @param int|null $versionNo optional, if specified, apply to this Version only.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    private function deleteTranslationFromContentVersions(
        int $contentId,
        int $languageId,
        ?int $versionNo = null
    ) {
        $query = $this->connection->createQueryBuilder();
        $query->update('ezcontentobject_version')
            // parameter for bitwise operation has to be placed verbatim (w/o binding) for this to work cross-DBMS
            ->set('language_mask', 'language_mask & ~ ' . $languageId)
            ->set('modified', ':now')
            // update initial_language_id only if it matches removed translation languageId
            ->set(
                'initial_language_id',
                'CASE WHEN initial_language_id = :languageId ' .
                'THEN (SELECT initial_language_id AS main_language_id FROM ezcontentobject c WHERE c.id = :contentId) ' .
                'ELSE initial_language_id END'
            )
            ->where('contentobject_id = :contentId')
            ->andWhere(
            // make sure removed translation is not the last one (incl. alwaysAvailable)
                $query->expr()->andX(
                    'language_mask & ~ ' . $languageId . ' <> 0',
                    'language_mask & ~ ' . $languageId . ' <> 1'
                )
            )
            ->setParameter(':now', time())
            ->setParameter(':contentId', $contentId)
            ->setParameter(':languageId', $languageId)
        ;

        if (null !== $versionNo) {
            $query
                ->andWhere('version = :versionNo')
                ->setParameter(':versionNo', $versionNo)
            ;
        }

        $rowCount = $query->execute();

        // no rows updated means that most likely somehow it was the last remaining translation
        if ($rowCount === 0) {
            throw new BadStateException(
                '$languageCode',
                'The provided translation is the only translation in this version'
            );
        }
    }
}
