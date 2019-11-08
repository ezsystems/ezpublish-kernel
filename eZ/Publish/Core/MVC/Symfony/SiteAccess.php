<?php

/**
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
    public const DEFAULT_MATCHING_TYPE = 'default';

    /**
     * Name of the siteaccess.
     *
     * @var string
     */
    public $name;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccessGroup[] */
    public $groups = [];

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

    /**
     * The name of the provider from which Site Access comes.
     *
     * @var string
     */
    public $provider;

    public function __construct(
        string $name,
        string $matchingType = self::DEFAULT_MATCHING_TYPE,
        $matcher = null,
        ?string $provider = null
    ) {
        $this->name = $name;
        $this->matchingType = $matchingType;
        $this->matcher = $matcher;
        $this->provider = $provider;
    }

    public function __toString()
    {
        return "$this->name (matched by '$this->matchingType')";
    }
}
