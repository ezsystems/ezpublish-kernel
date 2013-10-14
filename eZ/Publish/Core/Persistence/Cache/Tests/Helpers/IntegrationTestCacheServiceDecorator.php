<?php
/**
 * File containing the CacheServiceDecorator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests\Helpers;

use eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator;
use Stash\Pool;
use Stash\Driver\Ephemeral;

/**
 * Class CacheServiceDecorator
 *
 * Wraps the Cache Service for Spi cache to apply key prefix for the cache
 */
class IntegrationTestCacheServiceDecorator extends CacheServiceDecorator
{
    /**
     * Constructs the cache service decorator
     */
    public function __construct()
    {
        $this->cacheService = new Pool( new Ephemeral() );
    }

    /**
     * Private function for integration test runner to clear data between tests
     */
    public function clearAllTestData()
    {
        $this->cacheService->flush();
    }
}
