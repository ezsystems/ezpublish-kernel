<?php

/**
 * File containing the UserStorage LegacyStorage Gateway.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\User\UserStorage\Gateway;

use eZ\Publish\Core\FieldType\User\UserStorage\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;

/**
 * @deprecated since 6.11. Use {@see \eZ\Publish\Core\FieldType\User\UserStorage\Gateway\DoctrineStorage} instead.
 */
class LegacyStorage extends Gateway
{
    /**
     * Connection.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    public function __construct(DatabaseHandler $dbHandler)
    {
        @trigger_error(
            sprintf('%s is deprecated, use %s instead', self::class, DoctrineStorage::class),
            E_USER_DEPRECATED
        );
        $this->dbHandler = $dbHandler;
    }

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

    /**
     * Maps legacy database column names to property names.
     *
     * @var array
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
     * Get field data.
     *
     * The User storage handles the following attributes, following the user field
     * type in eZ Publish 4:
     * - hasStoredLogin
     * - contentobjectId
     * - login
     * - email
     * - passwordHash
     * - passwordHashType
     * - isEnabled
     * - maxLogin
     *
     * @param mixed $fieldId
     * @param mixed $userId
     *
     * @return array
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
     * Converts the given database values to properties.
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
     * Fetch basic user data.
     *
     * @param mixed $fieldId
     *
     * @return array
     */
    protected function fetchUserId($fieldId)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn('contentobject_id')
            )
            ->from($this->dbHandler->quoteTable('ezcontentobject_attribute'))
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id', 'ezcontentobject_attribute'),
                    $query->bindValue($fieldId)
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Fetch user data.
     *
     * @param mixed $userId
     *
     * @return array
     */
    protected function fetchUserData($userId)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn('contentobject_id', 'ezuser'),
                $this->dbHandler->quoteColumn('login', 'ezuser'),
                $this->dbHandler->quoteColumn('email', 'ezuser'),
                $this->dbHandler->quoteColumn('password_hash', 'ezuser'),
                $this->dbHandler->quoteColumn('password_hash_type', 'ezuser')
            )
            ->from($this->dbHandler->quoteTable('ezuser'))
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 'ezuser'),
                    $query->bindValue($userId)
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return isset($rows[0]) ? $this->convertColumnsToProperties($rows[0]) : [];
    }

    /**
     * Fetch user settings.
     *
     * Naturally this would be a RIGHT OUTER JOIN, but this is not supported by
     * ezcDatabase nor by databases like SQLite
     *
     * @param mixed $userId
     *
     * @return array
     */
    protected function fetchUserSettings($userId)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn('is_enabled', 'ezuser_setting'),
                $this->dbHandler->quoteColumn('max_login', 'ezuser_setting')
            )
            ->from($this->dbHandler->quoteTable('ezuser_setting'))
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('user_id', 'ezuser_setting'),
                    $query->bindValue($userId)
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return isset($rows[0]) ? $this->convertColumnsToProperties($rows[0]) : [];
    }
}
