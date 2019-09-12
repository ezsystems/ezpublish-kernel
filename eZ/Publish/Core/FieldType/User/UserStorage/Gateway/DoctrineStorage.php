<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\User\UserStorage\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use eZ\Publish\Core\FieldType\User\UserStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use PDO;

/**
 * User DoctrineStorage gateway.
 */
class DoctrineStorage extends Gateway
{
    const USER_TABLE = 'ezuser';
    const USER_SETTING_TABLE = 'ezuser_setting';

    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    /**
     * Default values for user fields.
     *
     * @var array
     */
    protected $defaultValues = [
        'hasStoredLogin' => false,
        'contentId' => null,
        'login' => null,
        'email' => null,
        'passwordHash' => null,
        'passwordHashType' => null,
        'passwordUpdatedAt' => null,
        'enabled' => false,
        'maxLogin' => null,
    ];

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldData($fieldId, $userId = null)
    {
        $userId = $userId ?: $this->fetchUserId($fieldId);
        $userData = $this->fetchUserData($userId);

        if (!isset($userData['login'])) {
            return $this->defaultValues;
        }

        $result = array_merge(
            $this->defaultValues,
            [
                'hasStoredLogin' => true,
            ],
            $userData,
            $this->fetchUserSettings($userId)
        );

        return $result;
    }

    /**
     * Map legacy database column names to property names.
     *
     * @return array
     */
    protected function getPropertyMap()
    {
        return [
            'has_stored_login' => [
                'name' => 'hasStoredlogin',
                'cast' => function ($input) {
                    return $input == '1';
                },
            ],
            'contentobject_id' => [
                'name' => 'contentId',
                'cast' => 'intval',
            ],
            'login' => [
                'name' => 'login',
                'cast' => 'strval',
            ],
            'email' => [
                'name' => 'email',
                'cast' => 'strval',
            ],
            'password_hash' => [
                'name' => 'passwordHash',
                'cast' => 'strval',
            ],
            'password_hash_type' => [
                'name' => 'passwordHashType',
                'cast' => 'strval',
            ],
            'password_updated_at' => [
                'name' => 'passwordUpdatedAt',
                'cast' => function ($timestamp) {
                    return $timestamp ? (int)$timestamp : null;
                },
            ],
            'is_enabled' => [
                'name' => 'enabled',
                'cast' => function ($input) {
                    return $input == '1';
                },
            ],
            'max_login' => [
                'name' => 'maxLogin',
                'cast' => 'intval',
            ],
        ];
    }

    /**
     * Convert the given database values to properties.
     *
     * @param array $databaseValues
     *
     * @return array
     */
    protected function convertColumnsToProperties(array $databaseValues)
    {
        $propertyValues = [];
        $propertyMap = $this->getPropertyMap();

        foreach ($databaseValues as $columnName => $value) {
            $conversionFunction = $propertyMap[$columnName]['cast'];

            $propertyValues[$propertyMap[$columnName]['name']] = $conversionFunction($value);
        }

        return $propertyValues;
    }

    /**
     * Fetch user content object id for the given field id.
     *
     * @param int $fieldId
     *
     * @return int
     */
    protected function fetchUserId($fieldId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->quoteIdentifier('contentobject_id')
            )
            ->from($this->connection->quoteIdentifier('ezcontentobject_attribute'))
            ->where(
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('id'),
                    ':fieldId'
                )
            )
            ->setParameter(':fieldId', $fieldId, PDO::PARAM_INT)
        ;

        $statement = $query->execute();

        return (int) $statement->fetchColumn();
    }

    /**
     * Fetch user data.
     *
     * @param int $userId
     *
     * @return array
     */
    protected function fetchUserData($userId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->quoteIdentifier('usr.contentobject_id'),
                $this->connection->quoteIdentifier('usr.login'),
                $this->connection->quoteIdentifier('usr.email'),
                $this->connection->quoteIdentifier('usr.password_hash'),
                $this->connection->quoteIdentifier('usr.password_hash_type'),
                $this->connection->quoteIdentifier('usr.password_updated_at')
            )
            ->from($this->connection->quoteIdentifier(self::USER_TABLE), 'usr')
            ->where(
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('usr.contentobject_id'),
                    ':userId'
                )
            )
            ->setParameter(':userId', $userId, PDO::PARAM_INT)
        ;

        $statement = $query->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return isset($rows[0]) ? $this->convertColumnsToProperties($rows[0]) : [];
    }

    /**
     * Fetch user settings.
     *
     * @param int $userId
     *
     * @return array
     */
    protected function fetchUserSettings($userId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->quoteIdentifier('s.is_enabled'),
                $this->connection->quoteIdentifier('s.max_login')
            )
            ->from($this->connection->quoteIdentifier(self::USER_SETTING_TABLE), 's')
            ->where(
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('s.user_id'),
                    ':userId'
                )
            )
            ->setParameter(':userId', $userId, PDO::PARAM_INT)
        ;

        $statement = $query->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return isset($rows[0]) ? $this->convertColumnsToProperties($rows[0]) : [];
    }

    public function storeFieldData(VersionInfo $versionInfo, Field $field): bool
    {
        if ($field->value->externalData === null) {
            //to avoid unnecessary modifications when provided field is empty (like missing data for languageCode)
            return false;
        }

        if (!empty($this->fetchUserData($versionInfo->contentInfo->id))) {
            $this->updateFieldData($versionInfo, $field);
        } else {
            $this->insertFieldData($versionInfo, $field);
        }

        return true;
    }

    protected function insertFieldData(VersionInfo $versionInfo, Field $field): void
    {
        $insertQuery = $this->connection->createQueryBuilder();

        $insertQuery
            ->insert($this->connection->quoteIdentifier(self::USER_TABLE))
            ->setValue('contentobject_id', ':userId')
            ->setValue('login', ':login')
            ->setValue('email', ':email')
            ->setValue('password_hash', ':passwordHash')
            ->setValue('password_hash_type', ':passwordHashType')
            ->setParameter(':userId', $versionInfo->contentInfo->id, ParameterType::INTEGER)
            ->setParameter(':login', $field->value->externalData['login'], ParameterType::STRING)
            ->setParameter(':email', $field->value->externalData['email'], ParameterType::STRING)
            ->setParameter(':passwordHash', $field->value->externalData['passwordHash'], ParameterType::STRING)
            ->setParameter(':passwordHashType', $field->value->externalData['passwordHashType'], ParameterType::INTEGER)
        ;

        $insertQuery->execute();

        $settingsQuery = $this->connection->createQueryBuilder();

        $settingsQuery
            ->insert($this->connection->quoteIdentifier(self::USER_SETTING_TABLE))
            ->setValue('user_id', ':userId')
            ->setValue('is_enabled', ':isEnabled')
            ->setValue('max_login', ':maxLogin')
            ->setParameter(':userId', $versionInfo->contentInfo->id, ParameterType::INTEGER)
            ->setParameter(':isEnabled', $field->value->externalData['enabled'], ParameterType::INTEGER)
            ->setParameter(':maxLogin', $field->value->externalData['maxLogin'], ParameterType::INTEGER);

        $settingsQuery->execute();
    }

    protected function updateFieldData(VersionInfo $versionInfo, Field $field): void
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->update($this->connection->quoteIdentifier(self::USER_TABLE))
            ->set('login', ':login')
            ->set('email', ':email')
            ->set('password_hash', ':passwordHash')
            ->set('password_hash_type', ':passwordHashType')
            ->setParameter(':login', $field->value->externalData['login'], ParameterType::STRING)
            ->setParameter(':email', $field->value->externalData['email'], ParameterType::STRING)
            ->setParameter(':passwordHash', $field->value->externalData['passwordHash'], ParameterType::STRING)
            ->setParameter(':passwordHashType', $field->value->externalData['passwordHashType'], ParameterType::INTEGER)
            ->where(
                $queryBuilder->expr()->eq(
                    $this->connection->quoteIdentifier('contentobject_id'),
                    ':userId'
                )
            )
            ->setParameter(':userId', $versionInfo->contentInfo->id, ParameterType::INTEGER)
        ;

        $queryBuilder->execute();

        $settingsQuery = $this->connection->createQueryBuilder();

        $settingsQuery
            ->update($this->connection->quoteIdentifier(self::USER_SETTING_TABLE))
            ->set('is_enabled', ':isEnabled')
            ->set('max_login', ':maxLogin')
            ->setParameter(':isEnabled', $field->value->externalData['enabled'], ParameterType::INTEGER)
            ->setParameter(':maxLogin', $field->value->externalData['maxLogin'], ParameterType::INTEGER)
            ->where(
                $queryBuilder->expr()->eq(
                    $this->connection->quoteIdentifier('user_id'),
                    ':userId'
                )
            )
            ->setParameter(':userId', $versionInfo->contentInfo->id, ParameterType::INTEGER);

        $settingsQuery->execute();
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds): bool
    {
        // Delete external storage only, when when deleting last relation to fieldType
        // to avoid removing it when deleting draft, translation or by exceeding archive limit
        if (!$this->isLastRelationToFieldType($fieldIds)) {
            return false;
        }

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete($this->connection->quoteIdentifier(self::USER_SETTING_TABLE))
            ->where(
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('user_id'),
                    ':userId'
                )
            )
            ->setParameter(':userId', $versionInfo->contentInfo->id, ParameterType::INTEGER);

        $query->execute();

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete($this->connection->quoteIdentifier(self::USER_TABLE))
            ->where(
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('contentobject_id'),
                    ':userId'
                )
            )
            ->setParameter(':userId', $versionInfo->contentInfo->id, ParameterType::INTEGER);

        $query->execute();

        return true;
    }

    /**
     * @param int[] $fieldIds
     *
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function isLastRelationToFieldType(array $fieldIds): bool
    {
        $countExpr = $this->connection->getDatabasePlatform()->getCountExpression('id');

        $checkQuery = $this->connection->createQueryBuilder();
        $checkQuery
            ->select($countExpr)
            ->from('ezcontentobject_attribute')
            ->where(
                $checkQuery->expr()->in(
                    $this->connection->quoteIdentifier('id'),
                    ':fieldIds'
                )
            )
            ->setParameter(':fieldIds', $fieldIds, Connection::PARAM_INT_ARRAY)
            ->groupBy('id')
            ->having($countExpr . ' > 1');

        $numRows = (int)$checkQuery->execute()->fetchColumn();

        return $numRows === 0;
    }
}
