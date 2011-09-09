<?php
/**
 * File containing the Type Update Handler base class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type\Update;

/**
 * Base class for update handlers
 */
abstract class Handler
{
    /**
     * Performs the update of $contentTypeId from $srcVersion
     *
     * @param int $contentTypeId
     * @param int $srcVersion
     * @return void
     */
    abstract public function performUpdate( $contentTypeId, $srcVersion );
}
