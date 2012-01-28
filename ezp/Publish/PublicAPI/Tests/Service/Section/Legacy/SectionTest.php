<?php
/**
 * File contains: ezp\Publish\PublicAPI\Tests\Service\Legacy\SectionTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Publish\PublicAPI\Tests\Service\Section\Legacy;
use ezp\Publish\PublicAPI\Tests\Service\Section\Base as BaseSectionServiceTest;

/**
 * Test case for Section Service using Legacy storage class
 *
 */
class SectionTest extends BaseSectionServiceTest
{
    protected function getRepository()
    {
        return include 'common.php';
    }
}
