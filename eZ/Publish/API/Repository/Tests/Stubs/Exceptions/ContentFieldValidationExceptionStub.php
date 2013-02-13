<?php
/**
 * File containing the ContentValidationExceptionStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\Exceptions\ContentValidationException
 */
class ContentFieldValidationExceptionStub extends ContentFieldValidationException
{
    /**
     * Returns an array of field validation error messages
     *
     * @return array
     */
    public function getFieldErrors()
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }
}
