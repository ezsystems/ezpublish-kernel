<?php
/**
 * File containing the ErrorContext class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext\RestSubContext;
use EzSystems\BehatBundle\Features\Context\SentencesInterfaces\Error;
use Behat\Behat\Context\Step;

/**
 * Class ErrorContext
 *
 * This class contains the implementation of the Error interface which
 * has the sentences for the Errors BDD
 */
class ErrorContext extends RestSubContext implements Error
{
    public function iSeeAnInvalidFieldError()
    {
        $errorDescriptionRegEx = "/^Argument '([^']*)' is invalid:(.*)/";

        return array(
            new Step\Then( 'I see 403 status code' ),
            new Step\Then( 'I see "Forbidden" status message' ),
            new Step\Then( 'I see "content-type" header with an "ErrorMessage"' ),
            new Step\Then( 'I see response body with "eZ\\Publish\\Core\\REST\\Common\\Exceptions\\ForbiddenException" object' ),
            new Step\Then( 'I see response error 403 status code' ),
            new Step\Then( 'I see response error description with "' . $errorDescriptionRegEx . '"' ),
        );
    }

    public function iSeeNotAuthorizedError()
    {
        return array(
            new Step\Then( 'I see 401 status code' ),
            new Step\Then( 'I see "Unauthorized" status message' ),
        );
    }
}
