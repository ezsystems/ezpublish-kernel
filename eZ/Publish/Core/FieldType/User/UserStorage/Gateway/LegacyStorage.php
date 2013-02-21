<?php
/**
 * File containing the UserStorage LegacyStorage Gateway
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\User\UserStorage\Gateway;

use eZ\Publish\Core\FieldType\User\UserStorage\Gateway;

class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var mixed
     */
    protected $dbHandler;

    /**
     * Default values for user fielfs
     *
     * @var array
     */
    protected $defaultValues = array(
        'hasStoredLogin'   => false,
        'contentId'   => null,
        'login'              => null,
        'email'              => null,
        'passwordHash'      => null,
        'passwordHashType' => null,
        'enabled'         => false,
        'maxLogin'          => null,
    );

    /**
     * Maps legacy database column names to property names
     *
     * @var array
     */
    protected function getPropertyMap()
    {
        return array(
            'has_stored_login' => array(
                'name' => 'hasStoredlogin',
                'cast' =>
                    function ( $input )
                    {
                        return ( $input == "1" );
                    },
            ),
            'contentobject_id' => array(
                'name' => 'contentId',
                'cast' => 'intval'
            ),
            'login' => array(
                'name' => 'login',
                'cast' => 'strval'
            ),
            'email' => array(
                'name' => 'email',
                'cast' => 'strval'
            ),
            'password_hash' => array(
                'name' => 'passwordHash',
                'cast' => 'strval'
            ),
            'password_hash_type' => array(
                'name' => 'passwordHashType',
                'cast' => 'strval'
            ),
            'is_enabled' => array(
                'name' => 'enabled',
                'cast' =>
                    function ( $input )
                    {
                        return ( $input == "1" );
                    }
            ),
            'max_login' => array(
                'name' => 'maxLogin',
                'cast' => 'intval'
            ),
        );
    }

    /**
     * Set dbHandler for gateway
     *
     * @param mixed $dbHandler
     *
     * @return void
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler )
        {
            throw new \RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Get field data
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
    public function getFieldData( $fieldId, $userId = null )
    {
        $userId   = $userId ?: $this->fetchUserId( $fieldId );
        $userData = $this->fetchUserData( $userId );

        if ( !isset( $userData['login'] ) )
        {
            return $this->defaultValues;
        }

        $result = array_merge(
            $this->defaultValues,
            array(
                'hasStoredLogin' => true,
            ),
            $userData,
            $this->fetchUserSettings( $userId )
        );

        return $result;
    }

    /**
     * Converts the given database values to properties
     *
     * @param array $databaseValues
     *
     * @return array
     */
    protected function convertColumnsToProperties( array $databaseValues )
    {
        $propertyValues = array();
        $propertyMap = $this->getPropertyMap();

        foreach ( $databaseValues as $columnName => $value )
        {
            $conversionFunction = $propertyMap[$columnName]['cast'];

            $propertyValues[$propertyMap[$columnName]['name']] = $conversionFunction( $value );
        }

        return $propertyValues;
    }

    /**
     * Fetch basic user data
     *
     * @param mixed $fieldId
     *
     * @return array
     */
    protected function fetchUserId( $fieldId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn( 'contentobject_id' )
            )
            ->from( $this->dbHandler->quoteTable( 'ezcontentobject_attribute' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id', 'ezcontentobject_attribute' ),
                    $query->bindValue( $fieldId )
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Fetch user data
     *
     * @param mixed $userId
     *
     * @return array
     */
    protected function fetchUserData( $userId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn( 'contentobject_id', 'ezuser' ),
                $this->dbHandler->quoteColumn( 'login', 'ezuser' ),
                $this->dbHandler->quoteColumn( 'email', 'ezuser' ),
                $this->dbHandler->quoteColumn( 'password_hash', 'ezuser' ),
                $this->dbHandler->quoteColumn( 'password_hash_type', 'ezuser' )
            )
            ->from( $this->dbHandler->quoteTable( 'ezuser' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezuser' ),
                    $query->bindValue( $userId )
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        return isset( $rows[0] ) ? $this->convertColumnsToProperties( $rows[0] ) : array();
    }

    /**
     * Fetch user settings
     *
     * Naturally this would be a RIGHT OUTER JOIN, but this is not supported by
     * ezcDatabase nor by databases like SQLite
     *
     * @param mixed $userId
     *
     * @return array
     */
    protected function fetchUserSettings( $userId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn( 'is_enabled', 'ezuser_setting' ),
                $this->dbHandler->quoteColumn( 'max_login', 'ezuser_setting' )
            )
            ->from( $this->dbHandler->quoteTable( 'ezuser_setting' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'user_id', 'ezuser_setting' ),
                    $query->bindValue( $userId )
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        return isset( $rows[0] ) ? $this->convertColumnsToProperties( $rows[0] ) : array();
    }
}

