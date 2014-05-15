<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\FieldTypeTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

use eZ\Publish\Core\Repository\Tests\Service\Integration\FieldTypeBase as BaseFieldTypeTest;

/**
 * Test case for FieldType Service using Legacy storage class
 */
class FieldTypeTest extends BaseFieldTypeTest
{
    protected function getRepository()
    {
        return Utils::getRepository();
    }
}
