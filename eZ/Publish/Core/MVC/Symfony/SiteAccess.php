<?php

/**
 * File containing the SiteAccess class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Base struct for a siteaccess representation.
 */
class SiteAccess extends ValueObject
{
    /**
     * Name of the siteaccess.
     *
     * @var string
     */
    public $name;

    /**
     * The matching type that has been used to discover the siteaccess.
     * Contains the matcher class FQN, or 'default' if fell back to the default siteaccess.
     *
     * @var string
     */
    public $matchingType;

    /**
     * The matcher instance that has been used to discover the siteaccess.
     *
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher
     */
    public $matcher;

    public function __construct($name = null, $matchingType = null, $matcher = null)
    {
        $this->name = $name;
        $this->matchingType = $matchingType;
        $this->matcher = $matcher;
    }

    public function __toString()
    {
        return "$this->name (matched by '$this->matchingType')";
    }
}
