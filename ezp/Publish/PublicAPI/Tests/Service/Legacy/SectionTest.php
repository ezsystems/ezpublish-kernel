<?php
/**
 * File contains: ezp\Publish\PublicAPI\Tests\Service\Legacy\SectionTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Publish\PublicAPI\Tests\Service\Legacy;
use ezp\Publish\PublicAPI\Tests\Service\SectionTest as InMemorySectionTest;

/**
 * Test case for Section Service using Legacy storage class
 *
 */
class SectionTest extends InMemorySectionTest
{

    protected static function getRepository()
    {
        self::markTestIncomplete( "@todo Fix setup of repository++" );
        return include 'common.php';
    }
}
