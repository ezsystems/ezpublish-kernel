<?php
/**
 * File containing Concrete User class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Model,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Persistence\User as UserValue,
    ezp\User;

/**
 * This class represents a User item
 *
 * @property-read mixed $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property int $hashAlgorithm
 * @property \ezp\User\Group[] $groups
 * @property \ezp\User\Role[] $roles
 * @property \ezp\User\Policy[] $policies
 */
class Concrete extends Model implements Groupable, User
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'login' => true,
        'email' => true,
        'passwordHash' => true,
        'hashAlgorithm' => true,// @todo Make read only?
        'isEnabled' => true,
        //'maxLogin' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'groups' => false,
        'roles' => false,
        'policies' => false,
    );

    /**
     * @var \ezp\Content The User Group Content Object
     */
    protected $content;

    /**
     * Assigned Roles
     *
     * @var \ezp\User\Role[]
     */
    protected $roles;

    /**
     * Assigned and inherited policies (via assigned and inherited roles)
     *
     * @var \ezp\User\Policy[]
     */
    protected $policies;

    /**
     * @var \ezp\User\Group[] The Groups user is assigned to
     */
    protected $groups;

    /**
     * Creates and setups User object
     *
     * @param mixed $id Lets you specify id of User object on creation
     */
    public function __construct( $id = null )
    {
        $this->properties = new UserValue( array( 'id' => $id ) );
        $this->content = (object)array( 'locations' => array() );
        $this->roles = new TypeCollection( 'ezp\\User\\Role' );
        $this->policies = new TypeCollection( 'ezp\\User\\Policy' );
        $this->groups = new TypeCollection( 'ezp\\User\\Group' );
    }

    /**
     * Returns definition of the user object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        return array(
            'module' => 'user',
            'functions' => array(
                'login' => array(
                    'SiteAccess' => array(
                        'compare' => function( User $user, array $limitationsValues )
                        {
                            // ezp code in def 64 compat mode: sprintf( '%u', crc32( $siteAccessName ) )
                            return true;// @todo Use current siteaccess when it becomes part of API
                        },
                    ),
                ),
                'password' => array(),
                'preferences' => array(),
                'register' => array(),
                'selfedit' => array(),// @todo If this was a limitation somewhere, then logic could have been on limitation
                                      // instead of having to be inline in the code with specific calls to selfedit + logic
            ),
        );
    }

    /**
     * List of assigned groups
     *
     * @return \ezp\User\Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * List of assigned Roles
     *
     * @return array|User\Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * List of assigned and inherited policies (via assigned and inherited roles)
     *
     * @return array|User\Policy[]
     */
    public function getPolicies()
    {
        return $this->policies;
    }

    /**
     * Checks if user has access to a specific module/function
     *
     * Return array of limitations if user has access to a certain function
     * but limited by the returned limitations.
     * If you have the model instance you want to check permissions against, then
     * use {@link \ezp\Base\Repository::canUser()}.
     *
     * @param string $module
     * @param string $function
     * @return array|bool
     */
    public function hasAccessTo( $module, $function )
    {
        $limitationArray = array();
        foreach ( $this->getPolicies() as $policy )
        {
            if ( $policy->module === '*' )
                return true;

            if ( $policy->module !== $module )
                continue;

            if ( $policy->function === '*' )
                return true;

            if ( $policy->function !== $function )
                continue;

            if ( $policy->limitations === '*' )
                return true;

            /*// Try to do some optimization if only one limitation, {@see \ezp\User\Tests\ServiceTest::testLoadPoliciesByUserIdHasAccessTo()}
            if ( !empty( $limitationArray ) && count( $policy->limitations ) === 1 )
            {
                foreach ( $policy->limitations as $limitationKey => $limitationValues );// to get first & only pair
                foreach ( $limitationArray as &$limitationSet )
                {
                    if ( isset( $limitationSet[ $limitationKey ] ) && count( $limitationSet ) === 1 )
                    {
                        $limitationSet[ $limitationKey ] = array_merge( $limitationSet[ $limitationKey ], $limitationValues );
                        continue 2;
                    }
                }
            }*/

            $limitationArray[] = $policy->limitations;
        }

        if ( !empty( $limitationArray ) )
            return $limitationArray;

        return false;// No policies matching $module and $function
    }
}
