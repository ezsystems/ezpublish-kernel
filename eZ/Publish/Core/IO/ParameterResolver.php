<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO;

/**
 * Retrieves a siteaccess aware configuration variable
 */
interface ParameterResolver
{
    /**
     * @return mixed The resolved configuration variable
     */
    public function get();
}
