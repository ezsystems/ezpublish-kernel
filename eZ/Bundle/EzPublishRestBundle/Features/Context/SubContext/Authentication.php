<?php

/**
 * File containing the Authentication context class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use eZ\Publish\Core\REST\Server\Values\SessionInput;

trait Authentication
{
    /**
     * @var eZ\Publish\Core\REST\Server\Values\UserSession
     */
    protected $userSession;

    /**
     * @Given I have :role permissions
     */
    public function usePermissionsOfRole($role)
    {
        $credentials = $this->getCredentialsFor($role);

        switch ($this->authType) {
            case self::AUTHTYPE_BASICHTTP:
                $this->restDriver->setAuthentication(
                    $credentials['login'],
                    $credentials['password']
                );
                break;
            case self::AUTHTYPE_SESSION:
                $this->createSession($credentials['login'], $credentials['password']);
                break;
            default:
                throw new \Exception("Unknown auth type: '{$this->authType}'.");
        }

        // also authenticate the user on the local repository instance
        $this->getRepository()->setCurrentUser(
            $this->getRepository()->getUserService()->loadUserByLogin($credentials['login'])
        );
    }

    /**
     * @Given I don't have permissions
     * @Given I do not have permissions
     */
    public function useAnonymousRole()
    {
        switch ($this->authType) {
            case self::AUTHTYPE_BASICHTTP:
                $this->restDriver->setAuthentication('anonymous', '');
                break;
            case self::AUTHTYPE_SESSION:
                $this->cleanupSession();
                break;
            default:
                throw new \Exception("Unknown auth type: '{$this->authType}'.");
        }
    }

    /**
     * @When I create a (new) session with login :login and password :password
     */
    public function createSession($login, $password)
    {
        $this->createRequest('post', '/user/sessions');
        $this->setHeaderWithObject('accept', 'Session');
        $this->setHeaderWithObject('content-type', 'SessionInput');

        $this->makeObject('SessionInput');
        $this->setFieldToValue('login', $login);
        $this->setFieldToValue('password', $password);
        $this->sendRequest();

        $this->userSession = $this->getResponseObject();

        $this->resetDriver();

        // apply session/csrf token to next request
        $this->restDriver->setHeader('cookie', "{$this->userSession->sessionName}={$this->userSession->sessionId}");
        $this->restDriver->setHeader('x-csrf-token', $this->userSession->csrfToken);
    }

    /**
     * @AfterScenario
     *
     * Cleanup session, if applicable.
     */
    public function cleanupSession()
    {
        if ($this->userSession) {
            $this->resetDriver();
            $this->createRequest('post', "/user/sessions/{$this->userSession->sessionId}");
            $this->restDriver->setHeader('cookie', "{$this->userSession->sessionName}={$this->userSession->sessionId}");
            $this->restDriver->setHeader('x-csrf-token', $this->userSession->csrfToken);
            $this->sendRequest();
            $this->userSession = null;
        }
    }
}
