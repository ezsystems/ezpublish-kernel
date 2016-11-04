<?php

/**
 * File containing the Exception context class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

trait Exception
{
    /**
     * @Then response has (an) unauthorized exception/error
     * @Then response has (a) not authorized exception/error
     */
    public function iSeeNotAuthorizedException()
    {
        $this->assertStatusCode(401);
        $this->assertStatusMessage('Unauthorized');
    }

    /**
     * @Then response has (an) invalid field exception/error
     */
    public function assertInvalidFieldException()
    {
        $this->assertStatusCode(403);
        $this->assertStatusMessage('Forbidden');
        $this->assertHeaderHasObject('content-type', 'ErrorMessage');
        $this->assertResponseObject('eZ\\Publish\\Core\\REST\\Common\\Exceptions\\ForbiddenException');
        $this->assertResponseErrorStatusCode(403);
        $this->assertResponseErrorDescription("/^Argument '([^']+)' is invalid:(.+)\$/");
    }

    /**
     * @Then response has a forbidden exception/error with message :message
     */
    public function assertForbiddenExceptionWithMessage($message)
    {
        $this->assertStatusCode(403);
        $this->assertStatusMessage('Forbidden');
        $this->assertHeaderHasObject('content-type', 'ErrorMessage');
        $this->assertResponseObject('eZ\\Publish\\Core\\REST\\Common\\Exceptions\\ForbiddenException');
        $this->assertResponseErrorStatusCode(403);
        $this->assertResponseErrorDescription("/^$message\$/");
    }

    /**
     * @Then response has (a) not found exception/error
     */
    public function assertNotFoundException()
    {
        $this->assertStatusCode(404);
        $this->assertStatusMessage('Not Found');
        $this->assertHeaderHasObject('content-type', 'ErrorMessage');
        $this->assertResponseObject('eZ\\Publish\\Core\\REST\\Common\\Exceptions\\NotFoundException');
        $this->assertResponseErrorStatusCode(404);
        $this->assertResponseErrorDescription("/^Could not find '([^']+)' with identifier '([^']+)'\$/");
    }
}
