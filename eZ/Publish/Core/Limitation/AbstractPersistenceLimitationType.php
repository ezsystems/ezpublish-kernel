<?php
/**
 * File containing the eZ\Publish\Core\Limitation\AbstractPersistenceLimitationType class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Limitation;

use eZ\Publish\SPI\Persistence\Handler as SPIPersistenceHandler;

/**
 * LocationLimitation is a Content limitation
 */
class AbstractPersistenceLimitationType
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistence;

    /**
     * @param \eZ\Publish\SPI\Persistence\Handler $persistence
     */
    public function __construct( SPIPersistenceHandler $persistence )
    {
        $this->persistence = $persistence;
    }
}
