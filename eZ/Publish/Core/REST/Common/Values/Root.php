<?php

/**
 * File containing the Root class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * This class represents the root resource.
 */
class Root extends RestValue
{
    /** @var resource[] */
    protected $resources;

    public function __construct(array $resources = [])
    {
        $this->resources = $resources;
    }

    /**
     * @return resource[]
     */
    public function getResources()
    {
        return $this->resources;
    }
}
