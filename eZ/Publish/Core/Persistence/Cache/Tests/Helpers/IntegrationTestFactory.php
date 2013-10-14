<?php
/**
 * File containing a Persistence Factory for Integration tests
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests\Helpers;

use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\Core\Persistence\Factory;

/**
 * A reusable factory for all the "storage engine" handlers
 *
 * This class is kept in Core as it is a temporary one until
 * Legacy and InMemory is refactored to provide all handlers as
 * decoupled services.
 */
class IntegrationTestFactory extends Factory
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    private $persistence;

    /**
     * @param \eZ\Publish\SPI\Persistence\Handler $persistence
     */
    public function __construct( Handler $persistence )
    {
        $this->persistence = $persistence;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function getPersistenceHandler()
    {
        return $this->persistence;
    }
}
