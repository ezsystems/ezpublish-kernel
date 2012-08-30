<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\PolicyUpdate;

class PolicyUpdateTest extends BaseTest
{
    /**
     * Tests the PolicyUpdate parser
     * @todo test with limitations
     */
    public function testParse()
    {
        $inputArray = array();

        $policyUpdate = $this->getPolicyUpdate();
        $result = $policyUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\PolicyUpdateStruct',
            $result,
            'PolicyUpdateStruct not created correctly.'
        );
    }

    /**
     * Returns the PolicyUpdateStruct parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\PolicyUpdate
     */
    protected function getPolicyUpdate()
    {
        return new PolicyUpdate( $this->getUrlHandler(), $this->getRepository()->getRoleService() );
    }
}
