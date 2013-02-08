<?php
/**
 * File containing the NotFoundException ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

/**
 * NotFoundException value object visitor
 */
class NotFoundException extends Exception
{
    /**
     * Returns HTTP status code
     *
     * @return int
     */
    protected function getStatus()
    {
        return 404;
    }
}
