<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\User\UserStorage\Gateway;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\FieldType\User\UserStorage\Gateway;
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
        'enabled' => false,
        'maxLogin' => null,
    ];

    public function __construct(Connection $connection)
    {
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
                $this->connection->quoteIdentifier('usr.password_hash_type')
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
}
