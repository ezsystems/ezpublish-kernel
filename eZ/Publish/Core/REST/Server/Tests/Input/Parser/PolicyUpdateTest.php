<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\PolicyUpdate;
use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;

class PolicyUpdateTest extends BaseTest
{
    /**
     * Tests the PolicyUpdate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'limitations' => array(
                'limitation' => array(
                    array(
                        '_identifier' => 'Class',
                        'values' => array(
                            'ref' => array(
                                array(
                                    '_href' => 1
                                ),
                                array(
                                    '_href' => 2
                                ),
                                array(
                                    '_href' => 3
                                )
                            )
                        )
                    )
                )
            )
        );

        $policyUpdate = $this->getPolicyUpdate();
        $result = $policyUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\PolicyUpdateStruct',
            $result,
            'PolicyUpdateStruct not created correctly.'
        );

        $parsedLimitations = $result->getLimitations();

        $this->assertInternalType(
            'array',
            $parsedLimitations,
            'PolicyUpdateStruct limitations not created correctly'
        );

        $this->assertCount(
            1,
            $parsedLimitations,
            'PolicyUpdateStruct limitations not created correctly'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Limitation',
            $parsedLimitations['Class'],
            'Limitation not created correctly.'
        );

        $this->assertEquals(
            'Class',
            $parsedLimitations['Class']->getIdentifier(),
            'Limitation identifier not created correctly.'
        );

        $this->assertEquals(
            array( 1, 2, 3 ),
            $parsedLimitations['Class']->limitationValues,
            'Limitation values not created correctly.'
        );
    }

    /**
     * Test PolicyUpdate parser throwing exception on missing identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_identifier' attribute for Limitation.
     */
    public function testParseExceptionOnMissingLimitationIdentifier()
    {
        $inputArray = array(
            'limitations' => array(
                'limitation' => array(
                    array(
                        'values' => array(
                            'ref' => array(
                                array(
                                    '_href' => 1
                                ),
                                array(
                                    '_href' => 2
                                ),
                                array(
                                    '_href' => 3
                                )
                            )
                        )
                    )
                )
            )
        );

        $policyUpdate = $this->getPolicyUpdate();
        $policyUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test PolicyUpdate parser throwing exception on missing values
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid format for limitation values in Limitation.
     */
    public function testParseExceptionOnMissingLimitationValues()
    {
        $inputArray = array(
            'limitations' => array(
                'limitation' => array(
                    array(
                        '_identifier' => 'Class'
                    )
                )
            )
        );

        $policyUpdate = $this->getPolicyUpdate();
        $policyUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the PolicyUpdateStruct parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\PolicyUpdate
     */
    protected function getPolicyUpdate()
    {
        return new PolicyUpdate(
            $this->getUrlHandler(),
            $this->getRoleServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the role service mock object
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    protected function getRoleServiceMock()
    {
        $roleServiceMock = $this->getMock(
            'eZ\\Publish\\Core\\Repository\\RoleService',
            array(),
            array(),
            '',
            false
        );

        $roleServiceMock->expects( $this->any() )
            ->method( 'newPolicyUpdateStruct' )
            ->will(
                $this->returnValue( new PolicyUpdateStruct() )
            );

        return $roleServiceMock;
    }
}
