<?php
/**
 * File containing the ContentFieldValidationExceptionTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Exceptions;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the ContentFieldValidationException using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
 */
class ContentFieldValidationExceptionTest extends BaseTest
{
    /**
     * Test for the getFieldExceptions() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException::getFieldExceptions()
     * 
     */
    public function testGetFieldExceptions()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentFieldValidationException::getFieldExceptions() is not implemented." );
    }

}
