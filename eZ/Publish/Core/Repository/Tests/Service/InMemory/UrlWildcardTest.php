<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\InMemory\UrlWildcardTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\InMemory;
use eZ\Publish\Core\Repository\Tests\Service\UrlWildcardBase as BaseUrlWildcardTest,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler;

/**
 * Test case for UrlWildcard Service using InMemory storage class
 */
class UrlWildcardTest extends BaseUrlWildcardTest
{
    protected function getRepository()
    {
        return Utils::getRepository();
    }
}
