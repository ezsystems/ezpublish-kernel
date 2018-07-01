<?php

/**
 * File containing a BadStateExceptionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\API\Repository\Exceptions\BadStateException;

class BadStateExceptionTest extends BaseTest
{
    /**
     * Tests the parsing of BadStateException.
     */
    public function testParse()
    {
        $parser = $this->getParser();

        $inputArray = array(
            'errorDescription' => 'Section with ID "23" not found.',
            'errorCode' => '409',
        );

        $exception = $parser->parse($inputArray, $this->getParsingDispatcherMock());
        self::assertInstanceOf(BadStateException::class, $exception);
        self::assertEquals('Section with ID "23" not found.', $exception->getMessage());
    }

    /**
     * Gets the error message parser.
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\ErrorMessage
     */
    protected function getParser()
    {
        return new Parser\ErrorMessage();
    }
}
