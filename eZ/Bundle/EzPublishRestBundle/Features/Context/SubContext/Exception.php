<?php
/**
 * File containing the Exception context class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use EzSystems\BehatBundle\Sentence\Exception as ExceptionSentences;
use Behat\Behat\Context\Step;

class Exception extends Base implements ExceptionSentences
{
    /**
     * Then I see (?:an |)invalid field (?:exception|error)
     */
    public function iSeeAnInvalidFieldError()
    {
        return array(
            new Step\Then( 'I see 403 status code' ),
            new Step\Then( 'I see "Forbidden" status message' ),
            new Step\Then( 'I see "content-type" header with an "ErrorMessage"' ),
            new Step\Then( 'I see response body with "eZ\\Publish\\Core\\REST\\Common\\Exceptions\\ForbiddenException" object' ),
            new Step\Then( 'I see response error 403 status code' ),
            new Step\Then( 'I see response error description with "' . self::REGEX_INVALID_FIELD_MESSAGE . '"' ),
        );
    }

    /**
     * Then I see (?:a |)forbidden (?:exception|error)
     */
    public function iSeeAForbiddenError()
    {
        return array(
            new Step\Then( 'I see 403 status code' ),
            new Step\Then( 'I see "Forbidden" status message' ),
            new Step\Then( 'I see "content-type" header with an "ErrorMessage"' ),
            new Step\Then( 'I see response error 403 status code' ),
        );
    }

    /**
     * Then I see (?:a |)forbidden (?:exception|error) with "<message>" message
     */
    public function iSeeAForbiddenErrorWithMessage( $message )
    {
        return array(
            new Step\Then( 'I see 403 status code' ),
            new Step\Then( 'I see "Forbidden" status message' ),
            new Step\Then( 'I see "content-type" header with an "ErrorMessage"' ),
            new Step\Then( 'I see response error 403 status code' ),
            new Step\Then( 'I see response error description with "/' . $message . '/"' ),
        );
    }

    /**
     * Then I see (?:a |)not authorized (?:exception|error)
     * Then I see (?:an |)unauthorized (?:exception|error)
     */
    public function iSeeNotAuthorizedError()
    {
        return array(
            new Step\Then( 'I see 401 status code' ),
            new Step\Then( 'I see "Unauthorized" status message' ),
        );
    }

    /**
     * Then I see (?:a |)not found (?:exception|error)
     */
    public function iSeeNotFoundError()
    {
        return array(
            new Step\Then( 'I see 404 status code' ),
            new Step\Then( 'I see "Not Found" status message' ),
            new Step\Then( 'I see "content-type" header with an "ErrorMessage"' ),
            new Step\Then( 'I see response body with "eZ\\Publish\\Core\\REST\\Common\\Exceptions\\NotFoundException" object' ),
            new Step\Then( 'I see response error 404 status code' ),
            new Step\Then( 'I see response error description with "' . self::REGEX_NOT_FOUND_MESSAGE . '"' ),
        );
    }
}
