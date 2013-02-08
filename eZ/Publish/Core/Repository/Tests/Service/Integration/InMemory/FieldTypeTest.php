<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory\FieldTypeTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory;

use eZ\Publish\Core\Repository\Tests\Service\Integration\FieldTypeBase as BaseFieldTypeTest;

/**
 * Test case for FieldType Service using InMemory storage class
 */
class FieldTypeTest extends BaseFieldTypeTest
{
    protected function getRepository()
    {
        return Utils::getRepository();
    }
}
