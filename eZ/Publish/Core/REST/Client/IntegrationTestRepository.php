<?php

/**
 * File containing the IntegrationTestRepository class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Client\HttpClient\Authentication\IntegrationTestAuthenticator;

/**
 * REST Client Repository to be used in integration tests.
 *
 * Note: NEVER USE THIS IN PRODUCTION!
 *
 * @see \eZ\Publish\API\Repository\Repository
 */
class IntegrationTestRepository extends Repository implements Sessionable
{
    /**
     * Integration test authenticator.
     *
     * @var \eZ\Publish\Core\REST\Client\HttpClient\Authentication\IntegrationTestAuthentication
     */
    private $authenticator;

    /**
     * Client.
     *
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    private $client;

    /**
     * Current user.
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
    public function __construct(HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, RequestParser $requestParser, array $fieldTypes, IntegrationTestAuthenticator $authenticator)
    {
        parent::__construct($client, $inputDispatcher, $outputVisitor, $requestParser, $fieldTypes);
        $this->client = $client;
        $this->authenticator = $authenticator;
    }

    /**
     * Set session ID.
     *
     * Only for testing
     *
     * @param mixed tringid
     *
     * @private
     */
    public function setSession($id)
    {
        if ($this->client instanceof Sessionable) {
            $this->client->setSession($id);
        }
    }

    /**
     * Get current user.
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
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $user
     *
     * @return void
     */
    public function setCurrentUser(UserReference $user)
    {
        $this->currentUser = $user;
        $this->authenticator->setUserId($user->getUserId());
    }
}
