<?php
/**
 * File containing the EzcDatabase Type Update Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler;
use ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler;

/**
 * EzcDatabase based type update handler
 */
class EzcDatabase extends Handler
{
    /**
     * Performs the update of $contentTypeId from $srcVersion
     *
     * @param int $contentTypeId
     * @param int $srcVersion
     * @return void
     */
    public function performUpdate( $contentTypeId, $srcVersion )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }
}
