<?php
/**
 * File containing the ForbiddenExceptionTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Exceptions;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the ForbiddenException using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Exceptions\ForbiddenException
 */
class ForbiddenExceptionTest extends BaseTest
{
    /**
     * Test for the getErrorCode() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Exceptions\ForbiddenException::getErrorCode()
     * 
     */
    public function testGetErrorCode()
    {
        $this->markTestIncomplete( "@TODO: Test for ForbiddenException::getErrorCode() is not implemented." );
    }

}
