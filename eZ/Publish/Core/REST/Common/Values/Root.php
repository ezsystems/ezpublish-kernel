<?php

/**
 * File containing the Root class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Common\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * This class represents the root resource.
 */
class Root extends RestValue
{
    /**
     * @var Resource[]
     */
    protected $resources;

    public function __construct(array $resources = array())
    {
        $this->resources = $resources;
    }

    /**
     * @return Resource[]
     */
    public function getResources()
    {
        return $this->resources;
    }
}
