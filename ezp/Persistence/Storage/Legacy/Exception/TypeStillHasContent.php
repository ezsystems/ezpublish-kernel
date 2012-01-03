<?php
/**
 * File containing the TypeStillHasContent class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Exception;

/**
 * Exception thrown when a Type is to be unlinked from its last Group.
 */
class TypeStillHasContent extends \LogicException
{
    /**
     * Creates a new exception for $typeId in $status;
     *
     * @param mixed $typeId
     * @param mixed $status
     */
    public function __construct( $typeId, $status )
    {
        parent::__construct(
            sprintf(
                'Type with ID "%s" in status "%s" still has content instances and can therefore not be deleted.',
                $typeId,
                $status
            )
        );
    }
}
