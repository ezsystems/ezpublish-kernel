<?php

/**
 * File containing a NotFoundExceptionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

class NotFoundExceptionTest extends BaseTest
{
    /**
     * Tests parsing of NotFoundException.
     */
    public function testParse()
    {
        $parser = $this->getParser();

        $inputArray = array(
            'errorDescription' => 'Section with ID "23" not found.',
            'errorCode' => '404',
        );

        $exception = $parser->parse($inputArray, $this->getParsingDispatcherMock());
        self::assertInstanceOf(NotFoundException::class, $exception);
        self::assertEquals('Section with ID "23" not found.', $exception->getMessage());
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
