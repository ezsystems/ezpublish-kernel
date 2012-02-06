<?php
/**
 * File contains: ezp\Publish\PublicAPI\Tests\Service\Legacy\SectionTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Legacy;
use eZ\Publish\Core\Repository\Tests\Service\LanguageBase as BaseLanguageServiceTest;

/**
 * Test case for Section Service using Legacy storage class
 *
 */
class LanguageTest extends BaseLanguageServiceTest
{
    protected function getRepository()
    {
        return include 'common.php';
    }
}
