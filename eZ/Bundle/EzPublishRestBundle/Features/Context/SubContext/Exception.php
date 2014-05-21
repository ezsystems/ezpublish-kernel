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
    public function iSeeAnInvalidFieldError()
    {
        $this->getMainContext()->setLastAction( "invalid" );

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
        $this->getMainContext()->setLastAction( "unauthorized" );

        return array(
            new Step\Then( 'I see 401 status code' ),
            new Step\Then( 'I see "Unauthorized" status message' ),
        );
    }
}
