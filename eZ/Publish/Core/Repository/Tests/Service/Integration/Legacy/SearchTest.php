<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\SearchTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

use eZ\Publish\Core\Repository\Tests\Service\Integration\SearchBase as BaseSearchServiceTest;
use Exception;

/**
 * Test case for Search Service using Legacy storage class.
 */
class SearchTest extends BaseSearchServiceTest
{
    protected function getRepository()
    {
        try {
            return Utils::getRepository();
        } catch (Exception $e) {
            $this->markTestIncomplete($e->getMessage());
        }
    }
}
