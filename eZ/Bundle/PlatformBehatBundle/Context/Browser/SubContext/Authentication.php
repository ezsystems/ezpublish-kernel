<?php
/**
 * File containing the Authentication class for Browser contexts.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\PlatformBehatBundle\Context\Browser\SubContext;

/**
 * Authentication methods
 */
trait Authentication
{
    /**
     * @Given I am logged (in) as a(n) :role
     * @Given I have :role permissions
     *
     * Logs in a (new) user with the role identified by ':role' assigned.
     */
    public function iAmLoggedInAsAn( $role )
    {
        if ( $role == 'Anonymous' )
        {
            $this->iAmNotLoggedIn();
        }
        else
        {
            $credentials = $this->getCredentialsFor( $role );
            $this->iAmLoggedInAsWithPassword( $credentials['login'], $credentials['password'] );
        }
    }

    /**
     * @Given I am logged in as :user with password :password
     *
     * Performs the login action with username ':user' and password ':password'.
     * Checks that the resulting page is the homepage.
     */
    public function iAmLoggedInAsWithPassword( $user, $password )
    {
        $this->iAmOnPage( 'login' );
        $this->fillFieldWithValue( 'Username', $user );
        $this->fillFieldWithValue( 'Password', $password );
        $this->iClickAtButton( 'Login' );
        $this->iShouldBeOnPage( 'home' );
    }

    /**
     * @Given I am not logged in
     * @Given I don't have permissions
     *
     * Perform the logout action, checks that the resulting page is the homepage.
     */
    public function iAmNotLoggedIn()
    {
        $this->iAmOnPage( 'logout' );
        $this->iShouldBeOnPage( 'home' );
    }
}
