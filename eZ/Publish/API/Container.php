<?php

/**
 * File containing the eZ\Publish\API\Container class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API;

/**
 * Container interface.
 *
 * Starting point for getting all Public API's
 */
interface Container
{
    /**
     * Get Repository object.
     *
     * Public API for
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository();
}
