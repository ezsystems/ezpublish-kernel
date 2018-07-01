<?php

/**
 * File containing the Json handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Input\Handler;

use eZ\Publish\Core\REST\Common\Input\Handler;
use eZ\Publish\Core\REST\Common\Exceptions\Parser as ParserException;

/**
 * Input format handler base class.
 */
class Json extends Handler
{
    /**
     * Converts the given string to an array structure.
     *
     * @throw eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @param string $string
     *
     * @return array
     */
    public function convert($string)
    {
        $json = json_decode($string, true);
        if (JSON_ERROR_NONE !== ($jsonErrorCode = json_last_error())) {
            $message = "An error occured while decoding the JSON input:\n";
            $message .= $this->jsonDecodeErrorMessage($jsonErrorCode);
            $message .= "\nInput JSON:\n\n" . $string;
            throw new ParserException($message);
        }

        return $json;
    }

    /**
     * Returns the error message associated with the $jsonErrorCode.
     *
     * @param $jsonErrorCode
     *
     * @return string
     */
    private function jsonDecodeErrorMessage($jsonErrorCode)
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }
        switch ($jsonErrorCode) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
        }

        return 'Unknown JSON decode error';
    }
}
