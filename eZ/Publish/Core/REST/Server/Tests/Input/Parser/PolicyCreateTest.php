<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\PolicyCreate;

class PolicyCreateTest extends BaseTest
{
    /**
     * Tests the PolicyCreate parser
     * @todo test with limitations
     */
    public function testParse()
    {
        $inputArray = array(
            'module' => 'content',
            'function' => 'delete'
        );

        $policyCreate = $this->getPolicyCreate();
        $result = $policyCreate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\PolicyCreateStruct',
            $result,
            'PolicyCreateStruct not created correctly.'
        );

        $this->assertEquals(
            'content',
            $result->module,
            'PolicyCreateStruct module property not created correctly.'
        );

        $this->assertEquals(
            'delete',
            $result->function,
            'PolicyCreateStruct function property not created correctly.'
        );
    }

    /**
     * Test PolicyCreate parser throwing exception on missing module
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'module' attribute for PolicyCreate.
     */
    public function testParseExceptionOnMissingModule()
    {
        $inputArray = array(
            'function' => 'delete'
        );

        $policyCreate = $this->getPolicyCreate();
        $policyCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test PolicyCreate parser throwing exception on missing function
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'function' attribute for PolicyCreate.
     */
    public function testParseExceptionOnMissingFunction()
    {
        $inputArray = array(
            'module' => 'content'
        );

        $policyCreate = $this->getPolicyCreate();
        $policyCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the PolicyCreateStruct parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\PolicyCreate
     */
    protected function getPolicyCreate()
    {
        return new PolicyCreate( $this->getUrlHandler(), $this->getRepository()->getRoleService() );
    }
}
