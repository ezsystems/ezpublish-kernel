<?php
/**
 * File containing the Exception parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\Core\REST\Client\Input\Exceptions;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

use eZ\Publish\API\Repository\Values;

/**
 * Parser for ErrorMessage
 */
class ErrorMessage extends Parser
{
    /**
     * Mapping of error codes to the respective exception classes
     *
     * @var array
     */
    protected $errorCodeMapping = array(
        404 => '\\eZ\\Publish\\Core\\REST\\Client\\Exceptions\\NotFoundException',
        406 => '\\eZ\\Publish\\Core\\REST\\Client\\Exceptions\\InvalidArgumentException',
        409 => '\\eZ\\Publish\\Core\\REST\\Client\\Exceptions\\BadStateException',
    );

    /**
     * Parse input structure
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     * @return ValueObject
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( isset( $this->errorCodeMapping[$data['errorCode']] ) )
        {
            $exceptionClass = $this->errorCodeMapping[$data['errorCode']];
        }
        else
        {
            $exceptionClass = '\\Exception';
        }

        throw new $exceptionClass(
            $data['errorDescription'],
            $data['errorCode']
        );
    }
}
