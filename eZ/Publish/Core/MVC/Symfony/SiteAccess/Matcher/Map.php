<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher;

abstract class Map implements VersatileMatcher
{
    /**
     * String that will be looked up in the map.
     *
     * @var string
     */
    protected $key;

    /**
     * Map used for the matching.
     *
     * @var array
     */
    protected $map;

    /**
     * Map used for reverse matching.
     *
     * @var array
     */
    protected $reverseMap;

    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest */
    protected $request;

    /**
     * Constructor.
     *
     * @param array $map Map used for matching.
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Do not serialize the Siteaccess configuration in order to reduce ESI request URL size.
     *
     * @see https://jira.ez.no/browse/EZP-23168
     *
     * @return array
     */
    public function __sleep()
    {
        $this->map = [];
        $this->reverseMap = [];

        return ['map', 'reverseMap', 'key'];
    }

    public function setRequest(SimplifiedRequest $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Injects the key that will be used for matching against the map configuration.
     *
     * @param string $key
     */
    public function setMapKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getMapKey()
    {
        return $this->key;
    }

    /**
     * Returns matching Siteaccess.
     *
     * @return string|false Siteaccess matched or false.
     */
    public function match()
    {
        return isset($this->map[$this->key])
            ? $this->map[$this->key]
            : false;
    }

    /**
     * @param string $siteAccessName
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher|Map|null
     */
    public function reverseMatch($siteAccessName)
    {
        $reverseMap = $this->getReverseMap($siteAccessName);

        if (!isset($reverseMap[$siteAccessName])) {
            return null;
        }

        $this->setMapKey($reverseMap[$siteAccessName]);

        return $this;
    }

    private function getReverseMap($defaultSiteAccess)
    {
        if (!empty($this->reverseMap)) {
            return $this->reverseMap;
        }

        $map = $this->map;
        foreach ($map as &$value) {
            // $value can be true in the case of the use of a Compound matcher
            if ($value === true) {
                $value = $defaultSiteAccess;
            }
        }

        return $this->reverseMap = array_flip($map);
    }
}
