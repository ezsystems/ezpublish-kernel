<?php

/**
 * File containing the ErrorMessage parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Client\Values\ErrorMessage as ErrorMessageValue;

/**
 * Parser for ErrorMessage.
 */
class ErrorMessage extends BaseParser
{
    /**
     * Mapping of error codes to the respective exception classes.
     *
     * @var array
     */
    protected $errorCodeMapping = array(
        403 => '\\eZ\\Publish\\Core\\REST\\Common\\Exceptions\\ForbiddenException',
        404 => '\\eZ\\Publish\\Core\\REST\\Common\\Exceptions\\NotFoundException',
        406 => '\\eZ\\Publish\\Core\\REST\\Client\\Exceptions\\InvalidArgumentException',
        409 => '\\eZ\\Publish\\Core\\REST\\Client\\Exceptions\\BadStateException',
    );

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \Exception|ErrorMessage
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (isset($this->errorCodeMapping[$data['errorCode']])) {
            $exceptionClass = $this->errorCodeMapping[$data['errorCode']];
            return new $exceptionClass($data['errorDescription'], $data['errorCode']);
        }

        return new ErrorMessageValue([
            'code' => $data['errorCode'],
            'message' => isset($data['errorMessage']) ? $data['errorMessage'] : null,
            'description' => isset($data['errorDescription']) ? $data['errorDescription'] : null,
            'trace' => isset($data['trace']) ? $data['trace'] : null,
            'file' => isset($data['file']) ? $data['file'] : null,
            'line' => isset($data['line']) ? $data['line'] : null,
        ]);
    }
}
