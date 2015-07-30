<?php

/**
 * File containing the RestConfigurationProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\CorsOptions;

use eZ\Bundle\EzPublishRestBundle\CorsOptions\RestProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Exception;

class RestProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Return value expectation for RequestMatcher::matchRequest
     * Set to false to expect Router::match() never to be called, or to an exception to have it throw one.
     */
    protected $matchRequestResult = array();

    public function testGetOptions()
    {
        $this->matchRequestResult = array('allowedMethods' => 'GET,POST,DELETE');

        self::assertEquals(
            array(
                'allow_methods' => array('GET', 'POST', 'DELETE'),
            ),
            $this->getProvider()->getOptions($this->createRequest())
        );
    }

    public function testGetOptionsResourceNotFound()
    {
        $this->matchRequestResult = new ResourceNotFoundException();
        self::assertEquals(
            array(
                'allow_methods' => array(),
            ),
            $this->getProvider()->getOptions($this->createRequest())
        );
    }

    public function testGetOptionsMethodNotAllowed()
    {
        $this->matchRequestResult = new MethodNotAllowedException(array('OPTIONS'));
        self::assertEquals(
            array(
                'allow_methods' => array(),
            ),
            $this->getProvider()->getOptions($this->createRequest())
        );
    }

    /**
     * @expectedException Exception
     */
    public function testGetOptionsException()
    {
        $this->matchRequestResult = new Exception();
        $this->getProvider()->getOptions($this->createRequest());
    }

    public function testGetOptionsNoMethods()
    {
        $this->matchRequestResult = array();
        self::assertEquals(
            array(
                'allow_methods' => array(),
            ),
            $this->getProvider()->getOptions($this->createRequest())
        );
    }

    public function testGetOptionsNotRestRequest()
    {
        $this->matchRequestResult = false;
        self::assertEquals(
            array(),
            $this->getProvider()->getOptions($this->createRequest(false))
        );
    }

    /**
     * @param bool $isRestRequest wether or not to set the is_rest_request attribute
     *
     * @return Request
     */
    protected function createRequest($isRestRequest = true)
    {
        $request = new Request();
        if ($isRestRequest) {
            $request->attributes->set('is_rest_request', true);
        }

        return $request;
    }

    protected function getProvider()
    {
        return new RestProvider(
            $this->getRequestMatcherMock()
        );
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|RequestMatcherInterface
     */
    protected function getRequestMatcherMock()
    {
        $mock = $this->getMock('Symfony\Component\Routing\Matcher\RequestMatcherInterface');

        if ($this->matchRequestResult instanceof Exception) {
            $mock->expects($this->any())
                ->method('matchRequest')
                ->will($this->throwException($this->matchRequestResult));
        } elseif ($this->matchRequestResult === false) {
            $mock->expects($this->never())
                ->method('matchRequest');
        } else {
            $mock->expects($this->any())
                ->method('matchRequest')
                ->will($this->returnValue($this->matchRequestResult));
        }

        return $mock;
    }
}
