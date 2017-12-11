<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use Buzz\Message\Form\FormRequest;
use Buzz\Message\Response;
use Buzz\Message\Request;
use stdClass;

class SessionTest extends TestCase
{
    public function setUp()
    {
        $this->autoLogin = false;
        parent::setUp();
    }

    public function testCreateSessionBadCredentials()
    {
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/user/sessions', 'SessionInput+json', 'Session+json');
        $this->setSessionInput($request, 'badpassword');
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 401);
    }

    /**
     * @return \stdClass The login request's response
     */
    public function testCreateSession()
    {
        return $this->login();
    }

    /**
     * @depends testCreateSession
     */
    public function testRefreshSession(stdClass $session)
    {
        $response = $this->sendHttpRequest($this->createRefreshRequest($session));
        self::assertHttpResponseCodeEquals($response, 200);
    }

    public function testRefreshSessionExpired()
    {
        $session = $this->login();

        $response = $this->sendHttpRequest($this->createDeleteRequest($session));
        self::assertHttpResponseCodeEquals($response, 204);

        $response = $this->sendHttpRequest($this->createRefreshRequest($session));
        self::assertHttpResponseCodeEquals($response, 404);

        self::assertHttpResponseDeletesSessionCookie($session, $response);
    }

    public function testRefreshSessionMissingCsrfToken()
    {
        $session = $this->login();

        $refreshRequest = $this->createRefreshRequest($session);
        $this->removeCsrfHeader($refreshRequest);
        $response = $this->sendHttpRequest($refreshRequest);
        self::assertHttpResponseCodeEquals($response, 401);
    }

    public function testDeleteSession()
    {
        $session = $this->login();
        $response = $this->sendHttpRequest($this->createDeleteRequest($session));
        self::assertHttpResponseCodeEquals($response, 204);
        self::assertHttpResponseDeletesSessionCookie($session, $response);

        return $session;
    }

    /**
     * CSRF needs to be tested as session handling bypasses the CsrfListener.
     */
    public function testDeleteSessionMissingCsrfToken()
    {
        $session = $this->login();
        $request = $this->createDeleteRequest($session);
        $this->removeCsrfHeader($request);
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 401);
    }

    public function testLoginWithExistingFrontendSession()
    {
        $loginFormResponse = $this->sendHttpRequest(new Request('GET', '/login', $this->getHttpHost()));
        $domDocument = $loginFormResponse->toDomDocument();
        $xpath = new \DOMXPath($domDocument);

        $csrfDomElements = $xpath->query("//input[@name='_csrf_token']/@value");
        self::assertGreaterThan(0, $csrfDomElements->length);
        $csrfTokenValue = $csrfDomElements->item(0)->nodeValue;

        $loginPostRequest = new FormRequest('POST', '/login_check', $this->getHttpHost());
        $loginPostRequest->addFields([
            '_username' => $this->getLoginUsername(),
            '_password' => $this->getLoginPassword(),
            '_csrf_token' => $csrfTokenValue,
        ]);
        $loginResponse = $this->sendHttpRequest($loginPostRequest);
        if (!$sessionCookieHeader = $loginResponse->getHeader('set-cookie')) {
            self::fail('No cookie in login response');
        }

        list($sessionCookie) = explode(';', $sessionCookieHeader);

        $request = $this->createHttpRequest('POST', '/api/ezp/v2/user/sessions', 'SessionInput+json', 'Session+json');
        $this->setSessionInput($request);
        $request->addHeader("Cookie: $sessionCookie");
        $response = $this->sendHttpRequest($request);
        // Since Session is reused, not created, expect 200 instead of 201
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testDeleteSession
     */
    public function testDeleteSessionExpired($session)
    {
        $response = $this->sendHttpRequest($this->createDeleteRequest($session));
        self::assertHttpResponseCodeEquals($response, 404);
        self::assertHttpResponseDeletesSessionCookie($session, $response);
    }

    /**
     * @param stdClass $session
     * @return \Buzz\Message\Request
     */
    protected function createRefreshRequest(stdClass $session)
    {
        $request = $this->createHttpRequest('POST',
            sprintf('/api/ezp/v2/user/sessions/%s/refresh', $session->identifier), '', 'Session+json');
        $request->addHeaders([
            sprintf('Cookie: %s=%s', $session->name, $session->identifier),
            sprintf('X-CSRF-Token: %s', $session->csrfToken),
        ]);

        return $request;
    }

    /**
     * @param $session
     * @return \Buzz\Message\Request
     */
    protected function createDeleteRequest($session)
    {
        $deleteRequest = $this->createHttpRequest('DELETE', $session->_href);
        $deleteRequest->addHeaders([
            sprintf('Cookie: %s=%s', $session->name, $session->identifier),
            sprintf('X-CSRF-Token: %s', $session->csrfToken),
        ]);

        return $deleteRequest;
    }

    private static function assertHttpResponseDeletesSessionCookie($session, Response $response)
    {
        self::assertStringStartsWith("{$session->name}=deleted;", $response->getHeader('set-cookie'));
    }

    /**
     * Removes the CSRF token header from a $request.
     *
     * @param Request $request
     */
    private function removeCsrfHeader(Request $request)
    {
        foreach ($request->getHeaders() as $headerString) {
            list($headerName) = explode(': ', $headerString);
            if (strtolower($headerName) !== 'x-csrf-token') {
                $headers[] = $headerString;
            }
        }

        $request->setHeaders($headers);
    }
}
