<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostElement class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher;

class HostElement implements VersatileMatcher
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest */
    private $request;

    /**
     * Number of elements to take into account.
     *
     * @var int
     */
    private $elementNumber;

    /**
     * Host elements used for matching as an array.
     *
     * @var array
     */
    private $hostElements;

    /**
     * Constructor.
     *
     * @param array|int $elementNumber Number of elements to take into account.
     */
    public function __construct($elementNumber)
    {
        if (is_array($elementNumber)) {
            // DI config parser will create an array with 'value' => number
            $elementNumber = current($elementNumber);
        }

        $this->elementNumber = (int)$elementNumber;
    }

    public function __sleep()
    {
        return ['elementNumber', 'hostElements'];
    }

    /**
     * Returns matching Siteaccess.
     *
     * @return string|false Siteaccess matched or false.
     */
    public function match()
    {
        $elements = $this->getHostElements();

        return isset($elements[$this->elementNumber - 1]) ? $elements[$this->elementNumber - 1] : false;
    }

    public function getName()
    {
        return 'host:element';
    }

    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     */
    public function setRequest(SimplifiedRequest $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function reverseMatch($siteAccessName)
    {
        $hostElements = explode('.', $this->request->host);
        $elementNumber = $this->elementNumber - 1;
        if (!isset($hostElements[$elementNumber])) {
            return null;
        }

        $hostElements[$elementNumber] = $siteAccessName;
        $this->request->setHost(implode('.', $hostElements));

        return $this;
    }

    /**
     * @return array
     */
    private function getHostElements()
    {
        if (isset($this->hostElements)) {
            return $this->hostElements;
        } elseif (!isset($this->request)) {
            return [];
        }

        $elements = explode('.', $this->request->host);

        return $this->hostElements = $elements;
    }
}
