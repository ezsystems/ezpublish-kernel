<?php
/**
 * File containing the Exception context class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContexts;

use EzSystems\BehatBundle\Sentence\Exception as ExceptionSentences;
use Behat\Behat\Context\Step;

#class Exception extends Base implements ExceptionSentences
trait Exception
{
    /**
     * @Then response has (an) unauthorized exception/error
     * @Then response has (a) not authorized exception/error
     */
    public function iSeeNotAuthorizedException()
    {
        $this->assertStatusCode( 401 );
        $this->assertStatusMessage( 'Unauthorized' );
    }

    /**
     * @Then response has (an) invalid field exception/error
     */
    public function assertInvalidFieldException()
    {
        $this->assertStatusCode( 403 );
        $this->assertStatusMessage( 'Forbidden' );
        $this->assertHeaderHasObject( 'content-type', 'ErrorMessage' );
        $this->assertResponseObject( 'eZ\\Publish\\Core\\REST\\Common\\Exceptions\\ForbiddenException' );
        $this->assertResponseErrorStatusCode( 403 );
        $this->assertResponseErrorDescription( "/^Argument '([^']+)' is invalid:(.+)\$/" );
    }

    /**
     * @Then response has a forbidden exception/error with message :message
     */
    public function assertForbiddenExceptionWithMessage( $message )
    {
        $this->assertStatusCode( 403 );
        $this->assertStatusMessage( 'Forbidden' );
        $this->assertHeaderHasObject( 'content-type', 'ErrorMessage' );
        $this->assertResponseObject( 'eZ\\Publish\\Core\\REST\\Common\\Exceptions\\ForbiddenException' );
        $this->assertResponseErrorStatusCode( 403 );
        $this->assertResponseErrorDescription( "/^$message\$/" );
    }

    /**
     * @Then response has (a) not found exception/error
     */
    public function assertNotFoundException()
    {
        $this->assertStatusCode( 404 );
        $this->assertStatusMessage( 'Not Found' );
        $this->assertHeaderHasObject( 'content-type', 'ErrorMessage' );
        $this->assertResponseObject( 'eZ\\Publish\\Core\\REST\\Common\\Exceptions\\NotFoundException' );
        $this->assertResponseErrorStatusCode( 404 );
        $this->assertResponseErrorDescription( "/^Could not find '([^']+)' with identifier '([^']+)'\$/" );
    }





//    /**
//     * Then I see an invalid field exception|error
//     */
//    public function iSeeAnInvalidFieldException()
//    {
//        return array(
//            new Step\Then( 'I see 403 status code' ),
//            new Step\Then( 'I see "Forbidden" status message' ),
//            new Step\Then( 'I see "content-type" header with an "ErrorMessage"' ),
//            new Step\Then( 'I see response body with "eZ\\Publish\\Core\\REST\\Common\\Exceptions\\ForbiddenException" object' ),
//            new Step\Then( 'I see response error 403 status code' ),
//            new Step\Then( 'I see response error description with "' . self::REGEX_INVALID_FIELD_MESSAGE . '"' ),
//        );
//    }
//
//    /**
//     * Then I see a forbidden exception|error
//     */
//    public function iSeeAForbiddenException()
//    {
//        return array(
//            new Step\Then( 'I see 403 status code' ),
//            new Step\Then( 'I see "Forbidden" status message' ),
//            new Step\Then( 'I see "content-type" header with an "ErrorMessage"' ),
//            new Step\Then( 'I see response error 403 status code' ),
//        );
//    }
}
