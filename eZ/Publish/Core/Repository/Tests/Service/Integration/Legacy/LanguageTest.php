<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\LanguageTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

use eZ\Publish\Core\Repository\Tests\Service\Integration\LanguageBase as BaseLanguageServiceTest;

/**
 * Test case for Language Service using Legacy storage class.
 */
class LanguageTest extends BaseLanguageServiceTest
{
    protected function getRepository()
    {
        return Utils::getRepository();
    }
}
