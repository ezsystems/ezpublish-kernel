<?php
/**
 * File containing the UserStorage LegacyStorage Gateway
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
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
        'account_key'        => null,
        'has_stored_login'   => false,
        'contentobject_id'   => null,
        'login'              => null,
        'email'              => null,
        'password_hash'      => null,
        'password_hash_type' => null,
        'is_logged_in'       => true,
        'is_enabled'         => false,
        'is_locked'          => false,
        'last_visit'         => null,
        'login_count'        => null,
        'max_login'          => null,
    );

    /**
     * Set dbHandler for gateway
     *
     * @param mixed $dbHandler
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
     * - account_key
     * - has_stored_login
     * - contentobject_id
     * - login
     * - email
     * - password_hash
     * - password_hash_type
     * - is_logged_in
     * - is_enabled
     * - is_locked
     * - last_visit
     * - login_count
     * - max_login
     *
     * @param mixed $fieldId
     * @param mixed $userId
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
                'has_stored_login' => true,
            ),
            $userData,
            $this->fetchAccountKeyData( $userId ),
            $this->fetchUserSettings( $userId ),
            $this->fetchUserVisits( $userId )
        );

        $result['is_locked'] = $result['login_count'] > $result['max_login'];

        return $result;
    }

    /**
     * Fetch basic user data
     *
     * @param mixed $fieldId
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
        return isset( $rows[0] ) ? $rows[0] : array();
    }

    /**
     * Fetch account key data
     *
     * Naturally this would be a RIGHT OUTER JOIN, but this is not supported by 
     * ezcDatabase nor by databases like SQLite
     *
     * @param mixed $userId
     * @return array
     */
    protected function fetchAccountKeyData( $userId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn( 'hash_key', 'ezuser_accountkey' )
            )
            ->from( $this->dbHandler->quoteTable( 'ezuser_accountkey' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id', 'ezuser_accountkey' ),
                    $query->bindValue( $userId )
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        return isset( $rows[0] ) ? array( 'account_key' => $rows[0]['hash_key'] ) : array();
    }

    /**
     * Fetch user settings
     *
     * Naturally this would be a RIGHT OUTER JOIN, but this is not supported by 
     * ezcDatabase nor by databases like SQLite
     *
     * @param mixed $userId
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
        return isset( $rows[0] ) ? $rows[0] : array();
    }

    /**
     * Fetch user visits
     *
     * Naturally this would be a RIGHT OUTER JOIN, but this is not supported by 
     * ezcDatabase nor by databases like SQLite
     *
     * @param mixed $userId
     * @return array
     */
    protected function fetchUserVisits( $userId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $query->alias(
                    $this->dbHandler->quoteColumn( 'last_visit_timestamp', 'ezuservisit' ),
                    'last_visit'
                ),
                $this->dbHandler->quoteColumn( 'login_count', 'ezuservisit' )
            )
            ->from( $this->dbHandler->quoteTable( 'ezuservisit' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'user_id', 'ezuservisit' ),
                    $query->bindValue( $userId )
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        return isset( $rows[0] ) ? $rows[0] : array();
    }

    /**
     * Store external field data
     *
     * @param mixed $fieldId
     * @param array $data
     * @return void
     */
    public function storeFieldData( $fieldId, array $data )
    {
        $userId  = $this->fetchUserId( $fieldId );
        $oldData = $this->getFieldData( $fieldId, $userId );

        $this->storeAccountKey( $userId, $oldData, $data );
        $this->storeVisits( $userId, $oldData, $data );
        // All other property changes are intentionally ignored. Use the user
        // service instead.

        return $this->getFieldData( $fieldId, $userId );
    }

    /**
     * Store account key associated with user
     *
     * @param mixed $userId
     * @param array $old
     * @param array $data
     * @return void
     */
    protected function storeAccountKey( $userId, array $old, array $data )
    {
        if ( !array_key_exists( 'account_key', $data ) ||
             ( $data['account_key'] == $old['account_key'] ) )
        {
            return;
        }

        if ( !$old['account_key'] )
        {
            $query = $this->dbHandler->createInsertQuery();
            $query
                ->insertInto( $this->dbHandler->quoteTable( 'ezuser_accountkey' ) )
                ->set(
                    $this->dbHandler->quoteColumn( 'hash_key' ),
                    $query->bindValue( $data['account_key'] )
                )
                ->set(
                    $this->dbHandler->quoteColumn( 'time' ),
                    $query->bindValue( time() )
                )
                ->set(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $query->bindValue( $userId )
                );
        }
        elseif ( !$data['account_key'] )
        {
            $query = $this->dbHandler->createDeleteQuery();
            $query
                ->deleteFrom( $this->dbHandler->quoteTable( 'ezuser_accountkey' ) )
                ->where(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'id' ),
                        $query->bindValue( $userId )
                    )
                );
        }
        else
        {
            $query = $this->dbHandler->createUpdateQuery();
            $query
                ->update( $this->dbHandler->quoteTable( 'ezuser_accountkey' ) )
                ->set(
                    $this->dbHandler->quoteColumn( 'hash_key' ),
                    $query->bindValue( $data['account_key'] )
                )
                ->where(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'id' ),
                        $query->bindValue( $userId )
                    )
                );
        }

        $stmt = $query->prepare();
        $stmt->execute();
    }

    /**
     * Store user visit data
     *
     * @param mixed $userId
     * @param array $old
     * @param array $data
     * @return void
     */
    protected function storeVisits( $userId, array $old, array $data )
    {
        if ( ( $data['last_visit'] == $old['last_visit'] ) &&
             ( $data['login_count'] == $old['login_count'] ) )
        {
            return;
        }

        if ( $old['last_visit'] === null )
        {
            $query = $this->dbHandler->createInsertQuery();
            $query
                ->insertInto( $this->dbHandler->quoteTable( 'ezuservisit' ) )
                ->set(
                    $this->dbHandler->quoteColumn( 'last_visit_timestamp' ),
                    $query->bindValue( $data['last_visit'] )
                )
                ->set(
                    $this->dbHandler->quoteColumn( 'login_count' ),
                    $query->bindValue( $data['login_count'] )
                )
                ->set(
                    $this->dbHandler->quoteColumn( 'user_id' ),
                    $query->bindValue( $userId )
                );
        }
        else
        {
            $query = $this->dbHandler->createUpdateQuery();
            $query
                ->update( $this->dbHandler->quoteTable( 'ezuservisit' ) )
                ->set(
                    $this->dbHandler->quoteColumn( 'last_visit_timestamp' ),
                    $query->bindValue( $data['last_visit'] )
                )
                ->set(
                    $this->dbHandler->quoteColumn( 'login_count' ),
                    $query->bindValue( $data['login_count'] )
                )
                ->where(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'user_id' ),
                        $query->bindValue( $userId )
                    )
                );
        }

        $stmt = $query->prepare();
        $stmt->execute();
    }

    /**
     * Copy all field data
     *
     * @param mixed $fieldId
     * @return void
     */
    public function copyFieldData( $fieldId )
    {
        return $this->defaultValues;
    }

    /**
     * Delete all field data
     *
     * @param mixed $fieldId
     * @return void
     */
    public function deleteFieldData( $fieldId )
    {
        $userId = $this->fetchUserId( $fieldId );

        $query = $this->dbHandler->createDeleteQuery();
        $query
            ->deleteFrom( $this->dbHandler->quoteTable( 'ezuser_accountkey' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $query->bindValue( $userId )
                )
            );
        $stmt = $query->prepare();
        $stmt->execute();

        $query = $this->dbHandler->createDeleteQuery();
        $query
            ->deleteFrom( $this->dbHandler->quoteTable( 'ezuser_setting' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'user_id' ),
                    $query->bindValue( $userId )
                )
            );
        $stmt = $query->prepare();
        $stmt->execute();

        $query = $this->dbHandler->createDeleteQuery();
        $query
            ->deleteFrom( $this->dbHandler->quoteTable( 'ezuservisit' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'user_id' ),
                    $query->bindValue( $userId )
                )
            );
        $stmt = $query->prepare();
        $stmt->execute();

        $query = $this->dbHandler->createDeleteQuery();
        $query
            ->deleteFrom( $this->dbHandler->quoteTable( 'ezuser' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $userId )
                )
            );
        $stmt = $query->prepare();
        $stmt->execute();
    }
}

