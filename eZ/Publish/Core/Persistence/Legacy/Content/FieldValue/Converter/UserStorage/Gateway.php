<?php
/**
 * File containing the UserStorage Gateway
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\UserStorage;

abstract class Gateway
{
    /**
     * Set dbHandler for gateway
     *
     * @param mixed $dbHandler
     * @return void
     */
    abstract public function setConnection( $dbHandler );
}

