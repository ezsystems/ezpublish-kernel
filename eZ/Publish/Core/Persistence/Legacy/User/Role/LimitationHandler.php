<?php
/**
 * File containing the abstract Limitation handler
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\User\Role;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Limitation Handler
 *
 * Takes care of Converting a Policy limitation from Legacy value to spi value accepted by API.
 */
abstract class LimitationHandler
{
    /**
     * Database handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new criterion handler
     *
     * @param \EzcDbHandler $dbHandler
     */
    public function __construct( EzcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * @param Policy $policy
     */
    abstract public function toLegacy( Policy $policy );

    /**
     * @param Policy $policy
     */
    abstract public function toSPI( Policy $policy );
}
