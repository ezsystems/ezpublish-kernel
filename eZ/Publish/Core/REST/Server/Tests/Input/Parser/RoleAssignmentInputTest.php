<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\RoleAssignInput;

class RoleAssignInputTest extends BaseTest
{
    /**
     * Tests the RoleAssignInput parser
     * @todo test with limitations
     */
    public function testParse()
    {
        $inputArray = array(
            'Role' => array(
                '_href' => '/user/roles/42'
            ),
        );

        $roleAssignInput = $this->getRoleAssignInput();
        $result = $roleAssignInput->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RoleAssignment',
            $result,
            'RoleAssignment not created correctly.'
        );

        $this->assertEquals(
            '42',
            $result->roleId,
            'RoleAssignment roleId property not created correctly.'
        );
    }

    /**
     * Test RoleAssignInput parser throwing exception on missing Role
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'Role' element for RoleAssignInput.
     */
    public function testParseExceptionOnMissingRole()
    {
        $inputArray = array();

        $roleAssignInput = $this->getRoleAssignInput();
        $roleAssignInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test RoleAssignInput parser throwing exception on invalid Role
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'Role' element for RoleAssignInput.
     */
    public function testParseExceptionOnInvalidRole()
    {
        $inputArray = array(
            'Role' => array()
        );

        $roleAssignInput = $this->getRoleAssignInput();
        $roleAssignInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the role assign input parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\RoleAssignInput
     */
    protected function getRoleAssignInput()
    {
        return new RoleAssignInput( $this->getUrlHandler() );
    }
}
