<?php
/**
 * File containing the SessionInputTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\SessionInput;
use eZ\Publish\Core\REST\Server\Values\SessionInput as SessionInputValue;

class SessionInputTest extends BaseTest
{
    /**
     * Tests the SessionInput parser
     */
    public function testParse()
    {
        $inputArray = array(
            'login' => 'Login Foo',
            'password' => 'Password Bar',
        );

        $sessionInput = $this->getSessionInput();
        $result = $sessionInput->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertEquals(
            new SessionInputValue( $inputArray ),
            $result,
            'SessionInput not created correctly.'
        );
    }

    /**
     * Test SessionInput parser throwing exception on missing password
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'password' attribute for SessionInput.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = array(
            'login' => 'Login Foo',
        );

        $sessionInput = $this->getSessionInput();
        $sessionInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test SessionInput parser throwing exception on missing login
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'login' attribute for SessionInput.
     */
    public function testParseExceptionOnMissingName()
    {
        $inputArray = array(
            'password' => 'Password Bar',
        );

        $sessionInput = $this->getSessionInput();
        $sessionInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the session input parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\SessionInput
     */
    protected function getSessionInput()
    {
        return new SessionInput( $this->getUrlHandler() );
    }
}
