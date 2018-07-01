<?php

/**
 * File containing the ErrorMessage parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Client\Exceptions\ServerException;
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
     * @return \Exception|ErrorMessageValue
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $error = new ErrorMessageValue([
            'code' => $data['errorCode'],
            'message' => isset($data['errorMessage']) ? $data['errorMessage'] : null,
            'description' => isset($data['errorDescription']) ? $data['errorDescription'] : null,
            'details' => isset($data['errorDetails']) ? $data['errorDetails'] : null,
            'trace' => isset($data['trace']) ? $data['trace'] : null,
            'file' => isset($data['file']) ? $data['file'] : null,
            'line' => isset($data['line']) ? $data['line'] : null,
        ]);

        // So client behaves closer to api, return relevant exceptions on status codes that maps to them
        if (isset($this->errorCodeMapping[$data['errorCode']])) {
            $exceptionClass = $this->errorCodeMapping[$data['errorCode']];
            return new $exceptionClass($data['errorDescription'], $data['errorCode'], new ServerException($error));
        }

        return $error;
    }
}
