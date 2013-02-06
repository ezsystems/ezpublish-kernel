<?php
/**
 * File containing the eZ\Publish\API\Container class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API;

/**
 * Container interface
 *
 * Starting point for getting all Public API's
 *
 * @package eZ\Publish\API
 */
interface Container
{
    /**
     * Get Repository object
     *
     * Public API for
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository();
}
