<?php
/**
 * File containing the RemoveLastGroupFromType class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Exception;

use eZ\Publish\Core\Base\Exceptions\BadStateException;

/**
 * Exception thrown when a Type is to be unlinked from its last Group.
 */
class RemoveLastGroupFromType extends BadStateException
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
            '$typeId',
            sprintf(
                'Type with ID "%s" in status "%s" cannot be unlinked from its last group.',
                $typeId,
                $status
            )
        );
    }
}
