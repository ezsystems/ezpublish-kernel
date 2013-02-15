<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\NameSchemaTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory;

use eZ\Publish\Core\Repository\Tests\Service\Integration\NameSchemaBase as BaseNameSchemaTest;

/**
 * Test case for NameSchema Service using InMemory storage class
 */
class NameSchemaTest extends BaseNameSchemaTest
{
    protected function getRepository()
    {
        return Utils::getRepository();
    }
}
