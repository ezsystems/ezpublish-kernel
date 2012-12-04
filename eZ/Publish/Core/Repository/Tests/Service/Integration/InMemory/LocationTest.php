<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory\LocationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory;
use eZ\Publish\Core\Repository\Tests\Service\Integration\LocationBase as BaseLocationServiceTest;

/**
 * Test case for Location Service using InMemory storage class
 */
class LocationTest extends BaseLocationServiceTest
{
    protected function getRepository()
    {
        return Utils::getRepository();
    }
}
