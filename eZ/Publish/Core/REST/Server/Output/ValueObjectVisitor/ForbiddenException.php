<?php
/**
 * File containing the ForbiddenException ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

/**
 * ForbiddenException value object visitor
 */
class ForbiddenException extends Exception
{
    /**
     * Returns HTTP status code
     *
     * @return int
     */
    protected function getStatus()
    {
        return 403;
    }
}
