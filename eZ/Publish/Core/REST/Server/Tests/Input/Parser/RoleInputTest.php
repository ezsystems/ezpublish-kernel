<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\RoleInput;

class RoleInputTest extends BaseTest
{
    /**
     * Tests the RoleInput parser
     */
    public function testParse()
    {
        $inputArray = array(
            'identifier' => 'Identifier Bar',
        );

        $roleInput = $this->getRoleInput();
        $result = $roleInput->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleCreateStruct',
            $result,
            'RoleCreateStruct not created correctly.'
        );

        $this->assertEquals(
            'Identifier Bar',
            $result->identifier,
            'RoleCreateStruct identifier property not created correctly.'
        );
    }

    /**
     * Test RoleInput parser throwing exception on missing identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' attribute for RoleInput.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = array();

        $roleInput = $this->getRoleInput();
        $roleInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the role input parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\RoleInput
     */
    protected function getRoleInput()
    {
        return new RoleInput( $this->getUrlHandler(), $this->getRepository()->getRoleService() );
    }
}
