<?php
/**
 * File containing the UserStorage LegacyStorage Gateway
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\UserStorage\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\UserStorage\Gateway;

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
        'account_key'                   => null,
        'has_stored_login'              => false,
        'is_logged_in'                  => true,
        'is_enabled'                    => false,
        'is_locked'                     => false,
        'last_visit'                    => null,
        'login_count'                   => null,
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
     *  - account_key
     *  - groups
     *  - has_stored_login
     *  - original_password
     *  - original_password_confirm
     *  - roles
     *  - role_id_list
     *  - limited_assignment_value_list
     *  - is_logged_in
     *  - is_enabled
     *  - is_locked
     *  - last_visit
     *  - login_count
     *  - has_manage_locations
     *
     * @param mixed $fieldId
     * @return array
     */
    public function getFieldData( $fieldId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn( 'contentobject_id', 'ezuser' ),
                $this->dbHandler->quoteColumn( 'login', 'ezuser' )
            )
            ->from( $this->dbHandler->quoteTable( 'ezcontentobject_attribute' ) )
            ->rightJoin(
                $this->dbHandler->quoteTable( 'ezuser' ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezuser' ),
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_attribute' )
                )
            )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id', 'ezcontentobject_attribute' ),
                    $query->bindValue( $fieldId )
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        $baseData = isset( $rows[0] ) ? $rows[0] : array();

        if ( !isset( $baseData['login'] ) )
        {
            return $this->defaultValues;
        }

        $result = array_merge(
            $this->defaultValues,
            array(
                'has_stored_login' => true,
            ),
            $this->fetchAccountKeyData( $baseData['contentobject_id'] ),
            $this->fetchUserSettings( $baseData['contentobject_id'] ),
            $this->fetchUserVisits( $baseData['contentobject_id'] )
        );

        $result['is_locked'] = $result['login_count'] > $result['max_login'];

        return $result;
    }

    /**
     * Fetch account key data
     *
     * Naturally this would be a RIGHT OUTER JOIN, but this is not supported by 
     * ezcDatabase nor by databases like SQLite
     *
     * @param mixed $userId
     * @return void
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
                    $this->dbHandler->quoteColumn( 'user_id', 'ezuser_accountkey' ),
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
     * @return void
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
     * @return void
     */
    protected function fetchUserVisits( $userId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn( 'current_visit_timestamp', 'ezuservisit' ),
                $this->dbHandler->quoteColumn( 'failed_login_attempts', 'ezuservisit' ),
                $this->dbHandler->quoteColumn( 'last_visit_timestamp', 'ezuservisit' ),
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
}

