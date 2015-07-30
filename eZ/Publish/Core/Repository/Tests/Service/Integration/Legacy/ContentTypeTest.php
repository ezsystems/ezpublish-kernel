<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\ContentTypeTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

use eZ\Publish\Core\Repository\Tests\Service\Integration\ContentTypeBase as BaseContentTypeServiceTest;

/**
 * Test case for ContentType Service using Legacy storage class.
 */
class ContentTypeTest extends BaseContentTypeServiceTest
{
    protected function getRepository()
    {
        return Utils::getRepository();
    }
}
