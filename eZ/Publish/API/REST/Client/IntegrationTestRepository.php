<?php
/**
 * File containing the IntegrationTestRepository class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client;

use \eZ\Publish\API\Repository\Values\ValueObject;
use \eZ\Publish\API\Repository\Values\Content\Content;
use \eZ\Publish\API\Repository\Values\Content\ContentInfo;
use \eZ\Publish\API\Repository\Values\Content\Location;
use \eZ\Publish\API\Repository\Values\Content\VersionInfo;
use \eZ\Publish\API\Repository\Values\User\User;
use \eZ\Publish\API\Repository\Values\User\Limitation;

use \eZ\Publish\API\REST\Common;
use \eZ\Publish\API\REST\Client\HttpClient\Authentication\IntegrationTestAuthenticator;

/**
 * REST Client Repository to be used in integration tests
 *
 * Note: NEVER USE THIS IN PRODUCTION!
 *
 * @see \eZ\Publish\API\Repository\Repository
 */
class IntegrationTestRepository extends Repository implements Sessionable
{
    /**
     * Optional session identifier
     *
     * @var string
     */
    private $session;

    /**
     * Integration test authenticator
     *
     * @var \eZ\Publish\API\REST\Client\HttpClient\Authentication\IntegrationTestAuthentication
     */
    private $authenticator;

    /**
     * Client
     *
     * @var \eZ\Publish\API\REST\Client\HttpClient
     */
    private $client;

    /**
     * Current user
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    private $currentUser;

    /**
     * Instantiates the REST Client repository.
     *
     * @param \eZ\Publish\API\REST\Client\HttpClient $client
     * @param \eZ\Publish\API\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\API\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\API\REST\Client\HttpClient\Authentication\IntegrationTestAuthentication $authenticator
     */
    public function __construct( HttpClient $client, Common\Input\Dispatcher $inputDispatcher, Common\Output\Visitor $outputVisitor, Common\UrlHandler $urlHandler, IntegrationTestAuthenticator $authenticator )
    {
        parent::__construct( $client, $inputDispatcher, $outputVisitor, $urlHandler );
        $this->authenticator = $authenticator;
    }

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed tringid
     * @return void
     * @private
     */
    public function setSession( $id )
    {
        if ( $this->client instanceof Sessionable )
        {
            $this->client->setSession( $id );
        }
    }

    /**
     * Get current user
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * Sets the current user to the user with the given user id
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @return void
     */
    public function setCurrentUser( User $user )
    {
        $this->currentUser = $user;
        $this->authenticator->setUserId( $user->id );
    }
}
