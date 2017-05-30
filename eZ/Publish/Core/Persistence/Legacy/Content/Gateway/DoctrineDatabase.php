<?php

/**
 * File containing the DoctrineDatabase Content Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Gateway;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\UpdateQuery;
use eZ\Publish\Core\Persistence\Database\InsertQuery;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
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
 */
class DoctrineDatabase extends Gateway
{
    /**
     * eZ Doctrine database handler.
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

    /**
     * Creates a new gateway based on $db.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $db
     * @param \Doctrine\DBAL\Connection $connection
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder $queryBuilder
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(
        DatabaseHandler $db,
        Connection $connection,
        QueryBuilder $queryBuilder,
        LanguageHandler $languageHandler,
        LanguageMaskGenerator $languageMaskGenerator
    ) {
        $this->dbHandler = $db;
        $this->connection = $connection;
        $this->queryBuilder = $queryBuilder;
        $this->languageHandler = $languageHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     * Get context definition for external storage layers.
     *
     * @return array
     */
    public function getContext()
    {
        return array(
            'identifier' => 'LegacyStorage',
            'connection' => $this->dbHandler,
        );
    }

    /**
     * Inserts a new content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param mixed $currentVersionNo
     *
     * @return int ID
     */
    public function insertContentObject(CreateStruct $struct, $currentVersionNo = 1)
    {
        $initialLanguageCode = $this->languageHandler->load($struct->initialLanguageId)->languageCode;
        if (isset($struct->name[$initialLanguageCode])) {
            $name = $struct->name[$initialLanguageCode];
        } else {
            $name = '';
        }

        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable('ezcontentobject')
        )->set(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->getAutoIncrementValue('ezcontentobject', 'id')
        )->set(
            $this->dbHandler->quoteColumn('current_version'),
            $q->bindValue($currentVersionNo, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('name'),
            $q->bindValue($name, null, \PDO::PARAM_STR)
        )->set(
            $this->dbHandler->quoteColumn('contentclass_id'),
            $q->bindValue($struct->typeId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('section_id'),
            $q->bindValue($struct->sectionId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('owner_id'),
            $q->bindValue($struct->ownerId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('initial_language_id'),
            $q->bindValue($struct->initialLanguageId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('remote_id'),
            $q->bindValue($struct->remoteId, null, \PDO::PARAM_STR)
        )->set(
            $this->dbHandler->quoteColumn('modified'),
            $q->bindValue(0, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('published'),
            $q->bindValue(0, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('status'),
            $q->bindValue(ContentInfo::STATUS_DRAFT, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('language_mask'),
            $q->bindValue(
                $this->generateLanguageMask(
                    $struct->fields,
                    $this->languageHandler->load($struct->initialLanguageId)->languageCode,
                    $struct->alwaysAvailable
                ),
                null,
                \PDO::PARAM_INT
            )
        );

        $q->prepare()->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName('ezcontentobject', 'id')
        );
    }

    /**
     * Generates a language mask for $version.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     * @param string $initialLanguageCode
     * @param bool $alwaysAvailable
     *
     * @return int
     */
    protected function generateLanguageMask(array $fields, $initialLanguageCode, $alwaysAvailable)
    {
        $languages = array($initialLanguageCode => true);
        foreach ($fields as $field) {
            if (isset($languages[$field->languageCode])) {
                continue;
            }

            $languages[$field->languageCode] = true;
        }

        if ($alwaysAvailable) {
            $languages['always-available'] = true;
        }

        return $this->languageMaskGenerator->generateLanguageMask($languages);
    }

    /**
     * Inserts a new version.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     *
     * @return int ID
     */
    public function insertVersion(VersionInfo $versionInfo, array $fields)
    {
        /** @var $q \eZ\Publish\Core\Persistence\Database\InsertQuery */
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable('ezcontentobject_version')
        )->set(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->getAutoIncrementValue('ezcontentobject_version', 'id')
        )->set(
            $this->dbHandler->quoteColumn('version'),
            $q->bindValue($versionInfo->versionNo, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('modified'),
            $q->bindValue($versionInfo->modificationDate, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('creator_id'),
            $q->bindValue($versionInfo->creatorId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('created'),
            $q->bindValue($versionInfo->creationDate, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('status'),
            $q->bindValue($versionInfo->status, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('initial_language_id'),
            $q->bindValue(
                $this->languageHandler->loadByLanguageCode($versionInfo->initialLanguageCode)->id,
                null,
                \PDO::PARAM_INT
            )
        )->set(
            $this->dbHandler->quoteColumn('contentobject_id'),
            $q->bindValue($versionInfo->contentInfo->id, null, \PDO::PARAM_INT)
        )->set(
            // As described in field mapping document
            $this->dbHandler->quoteColumn('workflow_event_pos'),
            $q->bindValue(0, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('language_mask'),
            $q->bindValue(
                $this->generateLanguageMask(
                    $fields,
                    $versionInfo->initialLanguageCode,
                    $versionInfo->contentInfo->alwaysAvailable
                ),
                null,
                \PDO::PARAM_INT
            )
        );

        $q->prepare()->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName('ezcontentobject_version', 'id')
        );
    }

    /**
     * Updates an existing content identified by $contentId in respect to $struct.
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $struct
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $prePublishVersionInfo Provided on publish
     */
    public function updateContent($contentId, MetadataUpdateStruct $struct, VersionInfo $prePublishVersionInfo = null)
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update($this->dbHandler->quoteTable('ezcontentobject'));

        if (isset($struct->name)) {
            $q->set(
                $this->dbHandler->quoteColumn('name'),
                $q->bindValue($struct->name, null, \PDO::PARAM_STR)
            );
        }
        if (isset($struct->mainLanguageId)) {
            $q->set(
                $this->dbHandler->quoteColumn('initial_language_id'),
                $q->bindValue($struct->mainLanguageId, null, \PDO::PARAM_INT)
            );
        }
        if (isset($struct->modificationDate)) {
            $q->set(
                $this->dbHandler->quoteColumn('modified'),
                $q->bindValue($struct->modificationDate, null, \PDO::PARAM_INT)
            );
        }
        if (isset($struct->ownerId)) {
            $q->set(
                $this->dbHandler->quoteColumn('owner_id'),
                $q->bindValue($struct->ownerId, null, \PDO::PARAM_INT)
            );
        }
        if (isset($struct->publicationDate)) {
            $q->set(
                $this->dbHandler->quoteColumn('published'),
                $q->bindValue($struct->publicationDate, null, \PDO::PARAM_INT)
            );
        }
        if (isset($struct->remoteId)) {
            $q->set(
                $this->dbHandler->quoteColumn('remote_id'),
                $q->bindValue($struct->remoteId, null, \PDO::PARAM_STR)
            );
        }
        if ($prePublishVersionInfo !== null) {
            $languages = [];
            foreach ($prePublishVersionInfo->languageCodes as $languageCodes) {
                if (!isset($languages[$languageCodes])) {
                    $languages[$languageCodes] = true;
                }
            }

            $languages['always-available'] = isset($struct->alwaysAvailable) ? $struct->alwaysAvailable :
                $prePublishVersionInfo->contentInfo->alwaysAvailable;

            $mask = $this->languageMaskGenerator->generateLanguageMask($languages);

            $q->set(
                $this->dbHandler->quoteColumn('language_mask'),
                $q->bindValue($mask, null, \PDO::PARAM_INT)
            );
        }
        $q->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $q->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );
        $q->prepare()->execute();

        // Handle alwaysAvailable flag update separately as it's a more complex task and has impact on several tables
        if (isset($struct->alwaysAvailable) || isset($struct->mainLanguageId)) {
            $this->updateAlwaysAvailableFlag($contentId, $struct->alwaysAvailable);
        }
    }

    /**
     * Updates version $versionNo for content identified by $contentId, in respect to $struct.
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $struct
     */
    public function updateVersion($contentId, $versionNo, UpdateStruct $struct)
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable('ezcontentobject_version')
        )->set(
            $this->dbHandler->quoteColumn('creator_id'),
            $q->bindValue($struct->creatorId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('modified'),
            $q->bindValue($struct->modificationDate, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('initial_language_id'),
            $q->bindValue($struct->initialLanguageId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('language_mask'),
            $q->expr->bitOr(
                $this->dbHandler->quoteColumn('language_mask'),
                $q->bindValue(
                    $this->generateLanguageMask(
                        $struct->fields,
                        $this->languageHandler->load($struct->initialLanguageId)->languageCode,
                        false
                    ),
                    null,
                    \PDO::PARAM_INT
                )
            )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id'),
                    $q->bindValue($contentId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $q->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Updates "always available" flag for Content identified by $contentId, in respect to
     * Content's current main language and optionally new $alwaysAvailable state.
     *
     * @param int $contentId
     * @param bool|null $alwaysAvailable New "always available" value or null if not defined
     */
    public function updateAlwaysAvailableFlag($contentId, $alwaysAvailable = null)
    {
        // We will need to know some info on the current language mask to update the flag
        // everywhere needed
        $contentInfoRow = $this->loadContentInfo($contentId);
        if (!isset($alwaysAvailable)) {
            $alwaysAvailable = (bool)$contentInfoRow['language_mask'] & 1;
        }

        /** @var $q \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $q = $this->dbHandler->createUpdateQuery();
        $q
            ->update($this->dbHandler->quoteTable('ezcontentobject'))
            ->set(
                $this->dbHandler->quoteColumn('language_mask'),
                $alwaysAvailable ?
                    $q->expr->bitOr($this->dbHandler->quoteColumn('language_mask'), 1) :
                    $q->expr->bitAnd($this->dbHandler->quoteColumn('language_mask'), -2)
            )
            ->where(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $q->bindValue($contentId, null, \PDO::PARAM_INT)
                )
            );
        $q->prepare()->execute();

        // Now we need to update ezcontentobject_name
        /** @var $qName \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $qName = $this->dbHandler->createUpdateQuery();
        $qName
            ->update($this->dbHandler->quoteTable('ezcontentobject_name'))
            ->set(
                $this->dbHandler->quoteColumn('language_id'),
                $alwaysAvailable ?
                    $qName->expr->bitOr($this->dbHandler->quoteColumn('language_id'), 1) :
                    $qName->expr->bitAnd($this->dbHandler->quoteColumn('language_id'), -2)
            )
            ->where(
                $qName->expr->lAnd(
                    $qName->expr->eq(
                        $this->dbHandler->quoteColumn('contentobject_id'),
                        $qName->bindValue($contentId, null, \PDO::PARAM_INT)
                    ),
                    $qName->expr->eq(
                        $this->dbHandler->quoteColumn('content_version'),
                        $qName->bindValue(
                            $contentInfoRow['current_version'],
                            null,
                            \PDO::PARAM_INT
                        )
                    )
                )
            );
        $qName->prepare()->execute();

        // Now update ezcontentobject_attribute for current version
        // Create update query that will be reused
        /** @var $qAttr \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $qAttr = $this->dbHandler->createUpdateQuery();
        $qAttr
            ->update($this->dbHandler->quoteTable('ezcontentobject_attribute'))
            ->where(
                $qAttr->expr->lAnd(
                    $qAttr->expr->eq(
                        $this->dbHandler->quoteColumn('contentobject_id'),
                        $qAttr->bindValue($contentId, null, \PDO::PARAM_INT)
                    ),
                    $qAttr->expr->eq(
                        $this->dbHandler->quoteColumn('version'),
                        $qAttr->bindValue(
                            $contentInfoRow['current_version'],
                            null,
                            \PDO::PARAM_INT
                        )
                    )
                )
            );

        // If there is only a single language, update all fields and return
        if (!$this->languageMaskGenerator->isLanguageMaskComposite($contentInfoRow['language_mask'])) {
            $qAttr->set(
                $this->dbHandler->quoteColumn('language_id'),
                $alwaysAvailable ?
                    $qAttr->expr->bitOr($this->dbHandler->quoteColumn('language_id'), 1) :
                    $qAttr->expr->bitAnd($this->dbHandler->quoteColumn('language_id'), -2)
            );
            $qAttr->prepare()->execute();

            return;
        }

        // Otherwise:
        // 1. Remove always available flag on all fields
        $qAttr->set(
            $this->dbHandler->quoteColumn('language_id'),
            $qAttr->expr->bitAnd($this->dbHandler->quoteColumn('language_id'), -2)
        );
        $qAttr->prepare()->execute();

        // 2. If Content is always available set the flag only on fields in main language
        if ($alwaysAvailable) {
            $qAttr->set(
                $this->dbHandler->quoteColumn('language_id'),
                $qAttr->expr->bitOr($this->dbHandler->quoteColumn('language_id'), 1)
            );
            $qAttr->where(
                $qAttr->expr->gt(
                    $qAttr->expr->bitAnd(
                        $this->dbHandler->quoteColumn('language_id'),
                        $qAttr->bindValue($contentInfoRow['initial_language_id'], null, PDO::PARAM_INT)
                    ),
                    $qAttr->bindValue(0, null, PDO::PARAM_INT)
                )
            );
            $qAttr->prepare()->execute();
        }
    }

    /**
     * Sets the status of the version identified by $contentId and $version to $status.
     *
     * The $status can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED
     *
     * @param int $contentId
     * @param int $version
     * @param int $status
     *
     * @return bool
     */
    public function setStatus($contentId, $version, $status)
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable('ezcontentobject_version')
        )->set(
            $this->dbHandler->quoteColumn('status'),
            $q->bindValue($status, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('modified'),
            $q->bindValue(time(), null, \PDO::PARAM_INT)
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id'),
                    $q->bindValue($contentId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $q->bindValue($version, null, \PDO::PARAM_INT)
                )
            )
        );
        $statement = $q->prepare();
        $statement->execute();

        if ((bool)$statement->rowCount() === false) {
            return false;
        }

        if ($status !== APIVersionInfo::STATUS_PUBLISHED) {
            return true;
        }

        // If the version's status is PUBLISHED, we set the content to published status as well
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable('ezcontentobject')
        )->set(
            $this->dbHandler->quoteColumn('status'),
            $q->bindValue(ContentInfo::STATUS_PUBLISHED, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('current_version'),
            $q->bindValue($version, null, \PDO::PARAM_INT)
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $q->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );
        $statement = $q->prepare();
        $statement->execute();

        return (bool)$statement->rowCount();
    }

    /**
     * Inserts a new field.
     *
     * Only used when a new field is created (i.e. a new object or a field in a
     * new language!). After that, field IDs need to stay the same, only the
     * version number changes.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     *
     * @return int ID
     */
    public function insertNewField(Content $content, Field $field, StorageFieldValue $value)
    {
        $q = $this->dbHandler->createInsertQuery();

        $this->setInsertFieldValues($q, $content, $field, $value);

        // Insert with auto increment ID
        $q->set(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->getAutoIncrementValue('ezcontentobject_attribute', 'id')
        );

        $q->prepare()->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName('ezcontentobject_attribute', 'id')
        );
    }

    /**
     * Inserts an existing field.
     *
     * Used to insert a field with an exsting ID but a new version number.
     *
     * @param Content $content
     * @param Field $field
     * @param StorageFieldValue $value
     */
    public function insertExistingField(Content $content, Field $field, StorageFieldValue $value)
    {
        $q = $this->dbHandler->createInsertQuery();

        $this->setInsertFieldValues($q, $content, $field, $value);

        $q->set(
            $this->dbHandler->quoteColumn('id'),
            $q->bindValue($field->id, null, \PDO::PARAM_INT)
        );

        $q->prepare()->execute();
    }

    /**
     * Sets field (ezcontentobject_attribute) values to the given query.
     *
     * @param \eZ\Publish\Core\Persistence\Database\InsertQuery $q
     * @param Content $content
     * @param Field $field
     * @param StorageFieldValue $value
     */
    protected function setInsertFieldValues(InsertQuery $q, Content $content, Field $field, StorageFieldValue $value)
    {
        $q->insertInto(
            $this->dbHandler->quoteTable('ezcontentobject_attribute')
        )->set(
            $this->dbHandler->quoteColumn('contentobject_id'),
            $q->bindValue($content->versionInfo->contentInfo->id, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('contentclassattribute_id'),
            $q->bindValue($field->fieldDefinitionId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_type_string'),
            $q->bindValue($field->type)
        )->set(
            $this->dbHandler->quoteColumn('language_code'),
            $q->bindValue($field->languageCode)
        )->set(
            $this->dbHandler->quoteColumn('version'),
            $q->bindValue($field->versionNo, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_float'),
            $q->bindValue($value->dataFloat)
        )->set(
            $this->dbHandler->quoteColumn('data_int'),
            $q->bindValue($value->dataInt, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_text'),
            $q->bindValue($value->dataText)
        )->set(
            $this->dbHandler->quoteColumn('sort_key_int'),
            $q->bindValue($value->sortKeyInt, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('sort_key_string'),
            $q->bindValue(mb_substr($value->sortKeyString, 0, 255))
        )->set(
            $this->dbHandler->quoteColumn('language_id'),
            $q->bindValue(
                $this->languageMaskGenerator->generateLanguageIndicator(
                    $field->languageCode,
                    $this->isLanguageAlwaysAvailable($content, $field->languageCode)
                ),
                null,
                \PDO::PARAM_INT
            )
        );
    }

    /**
     * Checks if $languageCode is always available in $content.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param string $languageCode
     *
     * @return bool
     */
    protected function isLanguageAlwaysAvailable(Content $content, $languageCode)
    {
        return
            $content->versionInfo->contentInfo->alwaysAvailable &&
            $content->versionInfo->contentInfo->mainLanguageCode === $languageCode
        ;
    }

    /**
     * Updates an existing field.
     *
     * @param Field $field
     * @param StorageFieldValue $value
     */
    public function updateField(Field $field, StorageFieldValue $value)
    {
        // Note, no need to care for language_id here, since Content->$alwaysAvailable
        // cannot change on update
        $q = $this->dbHandler->createUpdateQuery();
        $this->setFieldUpdateValues($q, $value);
        $q->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $q->bindValue($field->id, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $q->bindValue($field->versionNo, null, \PDO::PARAM_INT)
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Sets update fields for $value on $q.
     *
     * @param \eZ\Publish\Core\Persistence\Database\UpdateQuery $q
     * @param StorageFieldValue $value
     */
    protected function setFieldUpdateValues(UpdateQuery $q, StorageFieldValue $value)
    {
        $q->update(
            $this->dbHandler->quoteTable('ezcontentobject_attribute')
        )->set(
            $this->dbHandler->quoteColumn('data_float'),
            $q->bindValue($value->dataFloat)
        )->set(
            $this->dbHandler->quoteColumn('data_int'),
            $q->bindValue($value->dataInt, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('data_text'),
            $q->bindValue($value->dataText)
        )->set(
            $this->dbHandler->quoteColumn('sort_key_int'),
            $q->bindValue($value->sortKeyInt, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('sort_key_string'),
            $q->bindValue(mb_substr($value->sortKeyString, 0, 255))
        );
    }

    /**
     * Updates an existing, non-translatable field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param int $contentId
     */
    public function updateNonTranslatableField(
        Field $field,
        StorageFieldValue $value,
        $contentId
    ) {
        // Note, no need to care for language_id here, since Content->$alwaysAvailable
        // cannot change on update
        $q = $this->dbHandler->createUpdateQuery();
        $this->setFieldUpdateValues($q, $value);
        $q->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentclassattribute_id'),
                    $q->bindValue($field->fieldDefinitionId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id'),
                    $q->bindValue($contentId, null, \PDO::PARAM_INT)
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $q->bindValue($field->versionNo, null, \PDO::PARAM_INT)
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Loads data for a content object.
     *
     * Returns an array with the relevant data.
     *
     * @param mixed $contentId
     * @param mixed $version
     * @param string[] $translations
     *
     * @return array
     */
    public function load($contentId, $version, array $translations = null)
    {
        $query = $this->queryBuilder->createFindQuery($translations);
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
                    $query->bindValue($contentId)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('version', 'ezcontentobject_version'),
                    $query->bindValue($version)
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @see loadContentInfo(), loadContentInfoByRemoteId()
     *
     * @param string $column
     * @param mixed $id
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @return array
     */
    private function internalLoadContentInfo($column, $id)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            'ezcontentobject.*',
            $this->dbHandler->aliasedColumn($query, 'main_node_id', 'ezcontentobject_tree')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject')
        )->leftJoin(
            $this->dbHandler->quoteTable('ezcontentobject_tree'),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_tree'),
                    $this->dbHandler->quoteColumn('id', 'ezcontentobject')
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('main_node_id', 'ezcontentobject_tree'),
                    $this->dbHandler->quoteColumn('node_id', 'ezcontentobject_tree')
                )
            )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn($column, 'ezcontentobject'),
                $query->bindValue($id, null, $column === 'id' ? PDO::PARAM_INT : PDO::PARAM_STR)
            )
        );
        $statement = $query->prepare();
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (empty($row)) {
            throw new NotFound('content', "$column: $id");
        }

        return $row;
    }

    /**
     * Loads info for content identified by $contentId.
     * Will basically return a hash containing all field values for ezcontentobject table plus some additional keys:
     *  - always_available => Boolean indicating if content's language mask contains alwaysAvailable bit field
     *  - main_language_code => Language code for main (initial) language. E.g. "eng-GB".
     *
     * @param int $contentId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @return array
     */
    public function loadContentInfo($contentId)
    {
        return $this->internalLoadContentInfo('id', $contentId);
    }

    /**
     * Loads info for a content object identified by its remote ID.
     *
     * Returns an array with the relevant data.
     *
     * @param mixed $remoteId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @return array
     */
    public function loadContentInfoByRemoteId($remoteId)
    {
        return $this->internalLoadContentInfo('remote_id', $remoteId);
    }

    /**
     * Loads version info for content identified by $contentId and $versionNo.
     * Will basically return a hash containing all field values from ezcontentobject_version table plus following keys:
     *  - names => Hash of content object names. Key is the language code, value is the name.
     *  - languages => Hash of language ids. Key is the language code (e.g. "eng-GB"), value is the language numeric id without the always available bit.
     *  - initial_language_code => Language code for initial language in this version.
     *
     * @param int $contentId
     * @param int $versionNo
     *
     * @return array
     */
    public function loadVersionInfo($contentId, $versionNo)
    {
        $query = $this->queryBuilder->createVersionInfoFindQuery();
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_version'),
                    $query->bindValue($contentId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('version', 'ezcontentobject_version'),
                    $query->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Returns data for all versions with given status created by the given $userId.
     *
     * @param int $userId
     * @param int $status
     *
     * @return string[][]
     */
    public function listVersionsForUser($userId, $status = VersionInfo::STATUS_DRAFT)
    {
        $query = $this->queryBuilder->createVersionInfoFindQuery();
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('status', 'ezcontentobject_version'),
                    $query->bindValue($status, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('creator_id', 'ezcontentobject_version'),
                    $query->bindValue($userId, null, \PDO::PARAM_INT)
                )
            )
        );

        return $this->listVersionsHelper($query);
    }

    /**
     * Returns all version data for the given $contentId, optionally filtered by status.
     *
     * Result is returned with oldest version first (using version id as it has index and is auto increment).
     *
     * @param mixed $contentId
     * @param mixed|null $status Optional argument to filter versions by status, like {@see VersionInfo::STATUS_ARCHIVED}.
     * @param int $limit Limit for items returned, -1 means none.
     *
     * @return string[][]
     */
    public function listVersions($contentId, $status = null, $limit = -1)
    {
        $query = $this->queryBuilder->createVersionInfoFindQuery();

        $filter = $query->expr->eq(
            $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_version'),
            $query->bindValue($contentId, null, \PDO::PARAM_INT)
        );

        if ($status !== null) {
            $filter = $query->expr->lAnd(
                $filter,
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('status', 'ezcontentobject_version'),
                    $query->bindValue($status, null, \PDO::PARAM_INT)
                )
            );
        }

        $query->where($filter);

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $this->listVersionsHelper($query);
    }

    /**
     * Helper for {@see listVersions()} and {@see listVersionsForUser()} that filters duplicates
     * that are the result of the cartesian product performed by createVersionInfoFindQuery().
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     *
     * @return string[][]
     */
    private function listVersionsHelper(SelectQuery $query)
    {
        $query->orderBy(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject_version')
        );

        $statement = $query->prepare();
        $statement->execute();

        $results = array();
        $previousId = null;
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if ($row['ezcontentobject_version_id'] == $previousId) {
                continue;
            }

            $previousId = $row['ezcontentobject_version_id'];
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Returns all version numbers for the given $contentId.
     *
     * @param mixed $contentId
     *
     * @return int[]
     */
    public function listVersionNumbers($contentId)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->selectDistinct(
            $this->dbHandler->quoteColumn('version')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_version')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('contentobject_id'),
                $query->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns last version number for content identified by $contentId.
     *
     * @param int $contentId
     *
     * @return int
     */
    public function getLastVersionNumber($contentId)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->expr->max($this->dbHandler->quoteColumn('version'))
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_version')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('contentobject_id'),
                $query->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Returns all IDs for locations that refer to $contentId.
     *
     * @param int $contentId
     *
     * @return int[]
     */
    public function getAllLocationIds($contentId)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('node_id')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_tree')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('contentobject_id'),
                $query->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns all field IDs of $contentId grouped by their type.
     * If $versionNo is set only field IDs for that version are returned.
     *
     * @param int $contentId
     * @param int|null $versionNo
     *
     * @return int[][]
     */
    public function getFieldIdsByType($contentId, $versionNo = null)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->quoteColumn('data_type_string')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_attribute')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('contentobject_id'),
                $query->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );

        if (isset($versionNo)) {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $query->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        $result = array();
        foreach ($statement->fetchAll() as $row) {
            if (!isset($result[$row['data_type_string']])) {
                $result[$row['data_type_string']] = array();
            }
            $result[$row['data_type_string']][] = (int)$row['id'];
        }

        return $result;
    }

    /**
     * Deletes relations to and from $contentId.
     * If $versionNo is set only relations for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function deleteRelations($contentId, $versionNo = null)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable('ezcontentobject_link')
        );

        if (isset($versionNo)) {
            $query->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('from_contentobject_id'),
                        $query->bindValue($contentId, null, \PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('from_contentobject_version'),
                        $query->bindValue($versionNo, null, \PDO::PARAM_INT)
                    )
                )
            );
        } else {
            $query->where(
                $query->expr->lOr(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('from_contentobject_id'),
                        $query->bindValue($contentId, null, \PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('to_contentobject_id'),
                        $query->bindValue($contentId, null, \PDO::PARAM_INT)
                    )
                )
            );
        }

        $query->prepare()->execute();
    }

    /**
     * Removes relations to Content with $contentId from Relation and RelationList field type fields.
     *
     * @param int $contentId
     */
    public function removeReverseFieldRelations($contentId)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select('ezcontentobject_attribute.*')
            ->from('ezcontentobject_attribute')
            ->innerJoin(
                'ezcontentobject_link',
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('from_contentobject_id', 'ezcontentobject_link'),
                        $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_attribute')
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('from_contentobject_version', 'ezcontentobject_link'),
                        $this->dbHandler->quoteColumn('version', 'ezcontentobject_attribute')
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('contentclassattribute_id', 'ezcontentobject_link'),
                        $this->dbHandler->quoteColumn('contentclassattribute_id', 'ezcontentobject_attribute')
                    )
                )
            )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('to_contentobject_id', 'ezcontentobject_link'),
                    $query->bindValue($contentId, null, PDO::PARAM_INT)
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('relation_type', 'ezcontentobject_link'),
                        $query->bindValue(8, null, PDO::PARAM_INT)
                    ),
                    0
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($row['data_type_string'] === 'ezobjectrelation') {
                $this->removeRelationFromRelationField($row);
            }

            if ($row['data_type_string'] === 'ezobjectrelationlist') {
                $this->removeRelationFromRelationListField($contentId, $row);
            }
        }
    }

    /**
     * Updates field value of RelationList field type identified by given $row data,
     * removing relations toward given $contentId.
     *
     * @param int $contentId
     * @param array $row
     */
    protected function removeRelationFromRelationListField($contentId, array $row)
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->loadXML($row['data_text']);

        $xpath = new DOMXPath($document);
        $xpathExpression = "//related-objects/relation-list/relation-item[@contentobject-id='{$contentId}']";

        $relationItems = $xpath->query($xpathExpression);
        foreach ($relationItems as $relationItem) {
            $relationItem->parentNode->removeChild($relationItem);
        }

        $query = $this->dbHandler->createUpdateQuery();
        $query
            ->update('ezcontentobject_attribute')
            ->set(
                'data_text',
                $query->bindValue($document->saveXML(), null, PDO::PARAM_STR)
            )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('id'),
                        $query->bindValue($row['id'], null, PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('version'),
                        $query->bindValue($row['version'], null, PDO::PARAM_INT)
                    )
                )
            );

        $query->prepare()->execute();
    }

    /**
     * Updates field value of Relation field type identified by given $row data,
     * removing relation data.
     *
     * @param array $row
     */
    protected function removeRelationFromRelationField(array $row)
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query
            ->update('ezcontentobject_attribute')
            ->set('data_int', $query->bindValue(null, null, PDO::PARAM_INT))
            ->set('sort_key_int', $query->bindValue(0, null, PDO::PARAM_INT))
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('id'),
                        $query->bindValue($row['id'], null, PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('version'),
                        $query->bindValue($row['version'], null, PDO::PARAM_INT)
                    )
                )
            );

        $query->prepare()->execute();
    }

    /**
     * Deletes the field with the given $fieldId.
     *
     * @param int $fieldId
     */
    public function deleteField($fieldId)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable('ezcontentobject_attribute')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($fieldId, null, \PDO::PARAM_INT)
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Deletes all fields of $contentId in all versions.
     * If $versionNo is set only fields for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function deleteFields($contentId, $versionNo = null)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom('ezcontentobject_attribute')
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id'),
                    $query->bindValue($contentId, null, \PDO::PARAM_INT)
                )
            );

        if (isset($versionNo)) {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $query->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            );
        }

        $query->prepare()->execute();
    }

    /**
     * Deletes all versions of $contentId.
     * If $versionNo is set only that version is deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function deleteVersions($contentId, $versionNo = null)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom('ezcontentobject_version')
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id'),
                    $query->bindValue($contentId, null, \PDO::PARAM_INT)
                )
            );

        if (isset($versionNo)) {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('version'),
                    $query->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            );
        }

        $query->prepare()->execute();
    }

    /**
     * Deletes all names of $contentId.
     * If $versionNo is set only names for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function deleteNames($contentId, $versionNo = null)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom('ezcontentobject_name')
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id'),
                    $query->bindValue($contentId, null, \PDO::PARAM_INT)
                )
            );

        if (isset($versionNo)) {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('content_version'),
                    $query->bindValue($versionNo, null, \PDO::PARAM_INT)
                )
            );
        }

        $query->prepare()->execute();
    }

    /**
     * Sets the name for Content $contentId in version $version to $name in $language.
     *
     * @param int $contentId
     * @param int $version
     * @param string $name
     * @param string $language
     */
    public function setName($contentId, $version, $name, $language)
    {
        $language = $this->languageHandler->loadByLanguageCode($language);

        // Is it an insert or an update ?
        $qSelect = $this->dbHandler->createSelectQuery();
        $qSelect
            ->select(
                $qSelect->alias($qSelect->expr->count('*'), 'count')
            )
            ->from($this->dbHandler->quoteTable('ezcontentobject_name'))
            ->where(
                $qSelect->expr->lAnd(
                    $qSelect->expr->eq($this->dbHandler->quoteColumn('contentobject_id'), $qSelect->bindValue($contentId)),
                    $qSelect->expr->eq($this->dbHandler->quoteColumn('content_version'), $qSelect->bindValue($version)),
                    $qSelect->expr->eq($this->dbHandler->quoteColumn('content_translation'), $qSelect->bindValue($language->languageCode))
                )
            );
        $stmt = $qSelect->prepare();
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $insert = $res[0]['count'] == 0;
        if ($insert) {
            $q = $this->dbHandler->createInsertQuery();
            $q->insertInto($this->dbHandler->quoteTable('ezcontentobject_name'));
        } else {
            $q = $this->dbHandler->createUpdateQuery();
            $q->update($this->dbHandler->quoteTable('ezcontentobject_name'))
                ->where(
                    $q->expr->lAnd(
                        $q->expr->eq($this->dbHandler->quoteColumn('contentobject_id'), $q->bindValue($contentId)),
                        $q->expr->eq($this->dbHandler->quoteColumn('content_version'), $q->bindValue($version)),
                        $q->expr->eq($this->dbHandler->quoteColumn('content_translation'), $q->bindValue($language->languageCode))
                    )
                );
        }

        $q->set(
            $this->dbHandler->quoteColumn('contentobject_id'),
            $q->bindValue($contentId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('content_version'),
            $q->bindValue($version, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('language_id'),
            $q->bindValue($language->id, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('content_translation'),
            $q->bindValue($language->languageCode)
        )->set(
            $this->dbHandler->quoteColumn('real_translation'),
            $q->bindValue($language->languageCode)
        )->set(
            $this->dbHandler->quoteColumn('name'),
            $q->bindValue($name)
        );
        $q->prepare()->execute();
    }

    /**
     * Deletes the actual content object referred to by $contentId.
     *
     * @param int $contentId
     */
    public function deleteContent($contentId)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom('ezcontentobject')
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $query->bindValue($contentId, null, \PDO::PARAM_INT)
                )
            );

        $query->prepare()->execute();
    }

    /**
     * Loads relations from $contentId to published content, optionally only from $contentVersionNo.
     *
     * $relationType can also be filtered.
     *
     * @param int $contentId
     * @param int $contentVersionNo
     * @param int $relationType
     *
     * @return string[][] array of relation data
     */
    public function loadRelations($contentId, $contentVersionNo = null, $relationType = null)
    {
        $query = $this->queryBuilder->createRelationFindQuery();
        $query->innerJoin(
            $query->alias(
                $this->dbHandler->quoteTable('ezcontentobject'),
                'ezcontentobject_to'
            ),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('to_contentobject_id', 'ezcontentobject_link'),
                    $this->dbHandler->quoteColumn('id', 'ezcontentobject_to')
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('status', 'ezcontentobject_to'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                )
            )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('from_contentobject_id', 'ezcontentobject_link'),
                $query->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );

        // source version number
        if (isset($contentVersionNo)) {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('from_contentobject_version', 'ezcontentobject_link'),
                    $query->bindValue($contentVersionNo, null, \PDO::PARAM_INT)
                )
            );
        } else { // from published version only
            $query->from(
                $this->dbHandler->quoteTable('ezcontentobject')
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
                        $this->dbHandler->quoteColumn('from_contentobject_id', 'ezcontentobject_link')
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('current_version', 'ezcontentobject'),
                        $this->dbHandler->quoteColumn('from_contentobject_version', 'ezcontentobject_link')
                    )
                )
            );
        }

        // relation type
        if (isset($relationType)) {
            $query->where(
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('relation_type', 'ezcontentobject_link'),
                        $query->bindValue($relationType, null, \PDO::PARAM_INT)
                    ),
                    0
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Loads data that related to $toContentId.
     *
     * @param int $toContentId
     * @param int $relationType
     *
     * @return mixed[][] Content data, array structured like {@see \eZ\Publish\Core\Persistence\Legacy\Content\Gateway::load()}
     */
    public function loadReverseRelations($toContentId, $relationType = null)
    {
        $query = $this->queryBuilder->createRelationFindQuery();
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('to_contentobject_id', 'ezcontentobject_link'),
                $query->bindValue($toContentId, null, \PDO::PARAM_INT)
            )
        );

        // ezcontentobject join
        $query->from(
            $this->dbHandler->quoteTable('ezcontentobject')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('from_contentobject_id', 'ezcontentobject_link')
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('current_version', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('from_contentobject_version', 'ezcontentobject_link')
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('status', 'ezcontentobject'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                )
            )
        );

        // relation type
        if (isset($relationType)) {
            $query->where(
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('relation_type', 'ezcontentobject_link'),
                        $query->bindValue($relationType, null, \PDO::PARAM_INT)
                    ),
                    0
                )
            );
        }

        $statement = $query->prepare();

        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Inserts a new relation database record.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $createStruct
     *
     * @return int ID the inserted ID
     */
    public function insertRelation(RelationCreateStruct $createStruct)
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable('ezcontentobject_link')
        )->set(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->getAutoIncrementValue('ezcontentobject_link', 'id')
        )->set(
            $this->dbHandler->quoteColumn('contentclassattribute_id'),
            $q->bindValue((int)$createStruct->sourceFieldDefinitionId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('from_contentobject_id'),
            $q->bindValue($createStruct->sourceContentId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('from_contentobject_version'),
            $q->bindValue($createStruct->sourceContentVersionNo, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('relation_type'),
            $q->bindValue($createStruct->type, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('to_contentobject_id'),
            $q->bindValue($createStruct->destinationContentId, null, \PDO::PARAM_INT)
        );

        $q->prepare()->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName('ezcontentobject_link', 'id')
        );
    }

    /**
     * Deletes the relation with the given $relationId.
     *
     * @param int $relationId
     * @param int $type {@see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
     */
    public function deleteRelation($relationId, $type)
    {
        // Legacy Storage stores COMMON, LINK and EMBED types using bitmask, therefore first load
        // existing relation type by given $relationId for comparison
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('relation_type')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_link')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($relationId, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();
        $loadedRelationType = $statement->fetchColumn();

        if (!$loadedRelationType) {
            return;
        }

        // If relation type matches then delete
        if ($loadedRelationType == $type) {
            /** @var $query \eZ\Publish\Core\Persistence\Database\DeleteQuery */
            $query = $this->dbHandler->createDeleteQuery();
            $query->deleteFrom(
                'ezcontentobject_link'
            )->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $query->bindValue($relationId, null, \PDO::PARAM_INT)
                )
            );

            $query->prepare()->execute();
        } elseif ($loadedRelationType & $type) { // If relation type is composite update bitmask
            /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
            $query = $this->dbHandler->createUpdateQuery();
            $query->update(
                $this->dbHandler->quoteTable('ezcontentobject_link')
            )->set(
                $this->dbHandler->quoteColumn('relation_type'),
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('relation_type'),
                    $query->bindValue(~$type, null, \PDO::PARAM_INT)
                )
            )->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $query->bindValue($relationId, null, \PDO::PARAM_INT)
                )
            );

            $query->prepare()->execute();
        } else {
            // No match, do nothing
        }
    }

    /**
     * Returns all Content IDs for a given $contentTypeId.
     *
     * @param int $contentTypeId
     *
     * @return int[]
     */
    public function getContentIdsByContentTypeId($contentTypeId)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select($this->dbHandler->quoteColumn('id'))
            ->from($this->dbHandler->quoteTable('ezcontentobject'))
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentclass_id'),
                    $query->bindValue($contentTypeId, null, PDO::PARAM_INT)
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Load name data for set of content id's and corresponding version number.
     *
     * @param array[] $rows array of hashes with 'id' and 'version' to load names for
     *
     * @return array
     */
    public function loadVersionedNameData($rows)
    {
        $query = $this->queryBuilder->createNamesQuery();
        $conditions = array();
        foreach ($rows as $row) {
            $conditions[] = $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id'),
                    $query->bindValue($row['id'], null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('content_version'),
                    $query->bindValue($row['version'], null, \PDO::PARAM_INT)
                )
            );
        }

        $query->where($query->expr->lOr($conditions));
        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Batch method for copying all relation meta data for copied Content object.
     *
     * {@inheritdoc}
     *
     * @param int $originalContentId
     * @param int $copiedContentId
     * @param int|null $versionNo If specified only copy for a given version number, otherwise all.
     */
    public function copyRelations($originalContentId, $copiedContentId, $versionNo = null)
    {
        // Given we can retain all columns, we just create copies with new `from_contentobject_id` using INSERT INTO SELECT
        $sql = 'INSERT INTO ezcontentobject_link ( contentclassattribute_id, from_contentobject_id, from_contentobject_version, relation_type, to_contentobject_id )
                SELECT  L2.contentclassattribute_id, :copied_id, L2.from_contentobject_version, L2.relation_type, L2.to_contentobject_id
                FROM    ezcontentobject_link AS L2
                WHERE   L2.from_contentobject_id = :original_id';

        if ($versionNo) {
            $stmt = $this->connection->prepare($sql . ' AND L2.from_contentobject_version = :version');
            $stmt->bindValue('version', $versionNo, PDO::PARAM_INT);
        } else {
            $stmt = $this->connection->prepare($sql);
        }

        $stmt->bindValue('original_id', $originalContentId, PDO::PARAM_INT);
        $stmt->bindValue('copied_id', $copiedContentId, PDO::PARAM_INT);

        $stmt->execute();
    }
}
