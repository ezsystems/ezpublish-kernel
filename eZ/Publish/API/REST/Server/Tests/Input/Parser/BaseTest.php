<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Tests\Input\Parser;

/**
 * Base test for input parsers.
 */
abstract class BaseTest extends \eZ\Publish\API\REST\Server\Tests\BaseTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $parsingDispatcherMock;

    /**
     * @var \eZ\Publish\API\REST\Common\Input\ParsingDispatcher
     */
    protected function getParsingDispatcherMock()
    {
        if ( !isset( $this->parsingDispatcherMock ) )
        {
            $this->parsingDispatcherMock = $this->getMock(
                '\\eZ\\Publish\\API\\REST\\Common\\Input\\ParsingDispatcher',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->parsingDispatcherMock;
    }
}
