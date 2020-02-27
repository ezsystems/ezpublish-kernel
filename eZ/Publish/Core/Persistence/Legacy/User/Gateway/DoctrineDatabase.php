<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User\Gateway;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\User\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\User\UserTokenUpdateStruct;
use function time;

/**
 * User gateway implementation using the Doctrine database.
 *
 * @internal Gateway implementation is considered internal. Use Persistence User Handler instead.
 *
 * @see \eZ\Publish\SPI\Persistence\User\Handler
 */
final class DoctrineDatabase extends Gateway
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $handler;

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    /**
     * Construct from database handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(DatabaseHandler $handler)
    {
        $this->handler = $handler;
        $this->connection = $handler->getConnection();
        $this->dbPlatform = $this->connection->getDatabasePlatform();
    }

    /**
     * Loads user with user ID.
     *
     * @param mixed $userId
     *
     * @return array
     */
    public function load($userId)
    {
        $query = $this->getLoadUserQueryBuilder();
        $query
            ->where(
                $query->expr()->eq(
                    'u.contentobject_id',
                    $query->createPositionalParameter($userId, ParameterType::INTEGER)
                )
            );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads user with user login.
     *
     * @param string $login
     *
     * @return array
     */
    public function loadByLogin($login)
    {
        $query = $this->getLoadUserQueryBuilder();
        $expr = $query->expr();
        $query
            ->where(
                $expr->eq(
                    $this->dbPlatform->getLowerExpression('u.login'),
                    // Index is case in-sensitive, on some db's lowercase, so we lowercase $login
                    $query->createPositionalParameter(
                        mb_strtolower($login, 'UTF-8'),
                        ParameterType::STRING
                    )
                )
            );

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads user with user email.
     *
     * @param string $email
     *
     * @return array
     */
    public function loadByEmail($email)
    {
        $query = $this->getLoadUserQueryBuilder();
        $query->where(
            $query->expr()->eq(
                'u.email',
                $query->createPositionalParameter($email, ParameterType::STRING)
            )
        );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads a user with user hash key.
     *
     * @param string $hash
     *
     * @return array
     */
    public function loadUserByToken($hash)
    {
        $query = $this->getLoadUserQueryBuilder();
        $query
            ->leftJoin(
                'u',
                'ezuser_accountkey',
                'token',
                $query->expr()->eq(
                    'token.user_id',
                    'u.contentobject_id'
                )
            )
            ->where(
                $query->expr()->eq(
                    'token.hash_key',
                    $query->createPositionalParameter($hash, ParameterType::STRING)
                )
            )
            ->andWhere(
                $query->expr()->gte(
                    'token.time',
                    $query->createPositionalParameter(time(), ParameterType::INTEGER)
                )
            );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Update or insert the user token information specified by the user token struct.
     *
     * @param \eZ\Publish\SPI\Persistence\User\UserTokenUpdateStruct $userTokenUpdateStruct
     */
    public function updateUserToken(UserTokenUpdateStruct $userTokenUpdateStruct)
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query->select(
            'token.id'
        )->from(
            'ezuser_accountkey', 'token'
        )->where(
            $expr->eq(
                'token.user_id',
                $query->createPositionalParameter(
                    $userTokenUpdateStruct->userId,
                    ParameterType::INTEGER
                )
            )
        );

        $statement = $query->execute();

        if (empty($statement->fetchAll(FetchMode::ASSOCIATIVE))) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->insert('ezuser_accountkey')
                ->values(
                    [
                        'hash_key' => $query->createPositionalParameter(
                            $userTokenUpdateStruct->hashKey,
                            ParameterType::STRING
                        ),
                        'time' => $query->createPositionalParameter(
                            $userTokenUpdateStruct->time,
                            ParameterType::INTEGER
                        ),
                        'user_id' => $query->createPositionalParameter(
                            $userTokenUpdateStruct->userId,
                            ParameterType::INTEGER
                        ),
                    ]
                );

            $query->execute();
        } else {
            $query = $this->connection->createQueryBuilder();
            $query
                ->update('ezuser_accountkey')
                ->set(
                    'hash_key',
                    $query->createPositionalParameter(
                        $userTokenUpdateStruct->hashKey,
                        ParameterType::STRING
                    )
                )->set(
                    'time',
                    $query->createPositionalParameter(
                        $userTokenUpdateStruct->time,
                        ParameterType::INTEGER
                    )
                )->where(
                    $expr->eq(
                        'user_id',
                        $query->createPositionalParameter(
                            $userTokenUpdateStruct->userId,
                            ParameterType::INTEGER
                        )
                    )
                );
            $query->execute();
        }
    }

    /**
     * Expires user token with user hash.
     *
     * @param string $hash
     */
    public function expireUserToken($hash)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezuser_accountkey')
            ->set(
                'time',
                $query->createPositionalParameter(0, ParameterType::INTEGER)
            )->where(
                $query->expr()->eq(
                    'hash_key',
                    $query->createPositionalParameter($hash, ParameterType::STRING)
                )
            );
        $query->execute();
    }

    /**
     * Assigns role to user with given limitation.
     *
     * @param mixed $contentId
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole($contentId, $roleId, array $limitation)
    {
        foreach ($limitation as $identifier => $values) {
            foreach ($values as $value) {
                $query = $this->connection->createQueryBuilder();
                $query
                    ->insert('ezuser_role')
                    ->values(
                        [
                            'contentobject_id' => $query->createPositionalParameter(
                                $contentId,
                                ParameterType::INTEGER
                            ),
                            'role_id' => $query->createPositionalParameter(
                                $roleId,
                                ParameterType::INTEGER
                            ),
                            'limit_identifier' => $query->createPositionalParameter(
                                $identifier,
                                ParameterType::STRING
                            ),
                            'limit_value' => $query->createPositionalParameter(
                                $value,
                                ParameterType::STRING
                            ),
                        ]
                    );
                $query->execute();
            }
        }
    }

    /**
     * Remove role from user or user group.
     *
     * @param mixed $contentId
     * @param mixed $roleId
     */
    public function removeRole($contentId, $roleId)
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->delete('ezuser_role')
            ->where(
                $expr->eq(
                    'contentobject_id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'role_id',
                    $query->createPositionalParameter($roleId, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    /**
     * Remove role from user or user group, by assignment ID.
     *
     * @param mixed $roleAssignmentId
     */
    public function removeRoleAssignmentById($roleAssignmentId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('ezuser_role')
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($roleAssignmentId, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    private function getLoadUserQueryBuilder(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select(
                'u.contentobject_id',
                'u.login',
                'u.email',
                'u.password_hash',
                'u.password_hash_type',
                'u.password_updated_at',
                's.is_enabled',
                's.max_login'
            )
            ->from('ezuser', 'u')
            ->leftJoin(
                'u',
                'ezuser_setting',
                's',
                $expr->eq(
                    's.user_id',
                    'u.contentobject_id'
                )
            );

        return $query;
    }
}
