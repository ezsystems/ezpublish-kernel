<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\ContentTypeTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

use eZ\Publish\Core\Repository\Tests\Service\Integration\ContentTypeBase as BaseContentTypeServiceTest;

/**
 * Test case for ContentType Service using Legacy storage class
 */
class ContentTypeTest extends BaseContentTypeServiceTest
{
    protected function getRepository()
    {
        return Utils::getRepository();
    }
}
