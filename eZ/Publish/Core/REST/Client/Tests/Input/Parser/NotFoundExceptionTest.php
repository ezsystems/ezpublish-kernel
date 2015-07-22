<?php

/**
 * File containing a NotFoundExceptionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;

class NotFoundExceptionTest extends BaseTest
{
    /**
     * Tests parsing of NotFoundException.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @expectedExceptionMessage Section with ID "23" not found.
     */
    public function testParse()
    {
        $parser = $this->getParser();

        $inputArray = array(
            'errorDescription' => 'Section with ID "23" not found.',
            'errorCode' => '404',
        );

        $parser->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Gets the parser for error message.
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\ErrorMessage;
     */
    protected function getParser()
    {
        return new Parser\ErrorMessage();
    }
}
