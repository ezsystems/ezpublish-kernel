<?php
/**
 * File containing the IntegrationTestRepository class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\Values\User\User;

use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Client\HttpClient\Authentication\IntegrationTestAuthenticator;

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
     * @var \eZ\Publish\Core\REST\Client\HttpClient\Authentication\IntegrationTestAuthentication
     */
    private $authenticator;

    /**
     * Client
     *
     * @var \eZ\Publish\Core\REST\Client\HttpClient
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
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\SPI\FieldType\FieldType[] $fieldTypes
     * @param \eZ\Publish\Core\REST\Client\HttpClient\Authentication\IntegrationTestAuthentication $authenticator
     */
    public function __construct( HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, UrlHandler $urlHandler, array $fieldTypes, IntegrationTestAuthenticator $authenticator )
    {
        parent::__construct( $client, $inputDispatcher, $outputVisitor, $urlHandler, $fieldTypes );
        $this->client        = $client;
        $this->authenticator = $authenticator;
    }

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed tringid
     *
     * @private
     *
     * @return void
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
     * Sets the current user to the given $user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return void
     */
    public function setCurrentUser( User $user )
    {
        $this->currentUser = $user;
        $this->authenticator->setUserId( $user->id );
    }
}
