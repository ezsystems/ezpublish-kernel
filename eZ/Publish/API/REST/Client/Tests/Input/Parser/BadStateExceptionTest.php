<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Tests\Input\Parser;

use eZ\Publish\API\REST\Client\Input\Parser;

class BadStateExceptionTest extends BaseTest
{
    /**
     * @return void
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @expectedExceptionMessage Section with ID "23" not found.
     * @expectedExceptionCode 42
     */
    public function testParse()
    {
        $sectionParser = $this->getParser();

        $inputArray = array(
            'message' => 'Section with ID "23" not found.',
            '_code'    => '42',
        );

        $result = $sectionParser->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * @return eZ\Publish\API\REST\Client\Input\Parser\BadStateException;
     */
    protected function getParser()
    {
        return new Parser\BadStateException();
    }
}
