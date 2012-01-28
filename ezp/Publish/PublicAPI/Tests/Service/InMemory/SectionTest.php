<?php
/**
 * File contains: ezp\Publish\PublicAPI\Tests\Service\Legacy\SectionTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Publish\PublicAPI\Tests\Service\InMemory;
use ezp\Publish\PublicAPI\Tests\Service\SectionBase as BaseSectionServiceTest,
    ezp\Publish\PublicAPI\Repository,
    ezp\Io\Storage\InMemory as InMemoryIoHandler,
    ezp\Persistence\Storage\InMemory\Handler as InMemoryPersistenceHandler;

/**
 * Test case for Section Service using Legacy storage class
 *
 */
class SectionTest extends BaseSectionServiceTest
{
    protected function getRepository()
    {
        return new Repository( new InMemoryPersistenceHandler(), new InMemoryIoHandler() );
    }
}
