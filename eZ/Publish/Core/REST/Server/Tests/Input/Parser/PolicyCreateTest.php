<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\PolicyCreate;
use eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct;

class PolicyCreateTest extends BaseTest
{
    /**
     * Tests the PolicyCreate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'module' => 'content',
            'function' => 'delete',
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

        $parsedLimitations = $result->getLimitations();

        $this->assertInternalType(
            'array',
            $parsedLimitations,
            'PolicyCreateStruct limitations not created correctly'
        );

        $this->assertCount(
            1,
            $parsedLimitations,
            'PolicyCreateStruct limitations not created correctly'
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
     * Test PolicyCreate parser throwing exception on missing module
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'module' attribute for PolicyCreate.
     */
    public function testParseExceptionOnMissingModule()
    {
        $inputArray = array(
            'function' => 'delete',
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
            'module' => 'content',
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

        $policyCreate = $this->getPolicyCreate();
        $policyCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test PolicyCreate parser throwing exception on missing identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_identifier' attribute for Limitation.
     */
    public function testParseExceptionOnMissingLimitationIdentifier()
    {
        $inputArray = array(
            'module' => 'content',
            'function' => 'delete',
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

        $policyCreate = $this->getPolicyCreate();
        $policyCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test PolicyCreate parser throwing exception on missing values
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid format for limitation values in Limitation.
     */
    public function testParseExceptionOnMissingLimitationValues()
    {
        $inputArray = array(
            'module' => 'content',
            'function' => 'delete',
            'limitations' => array(
                'limitation' => array(
                    array(
                        '_identifier' => 'Class'
                    )
                )
            )
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
        return new PolicyCreate(
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
            ->method( 'newPolicyCreateStruct' )
            ->with(
                $this->equalTo( 'content' ),
                $this->equalTo( 'delete' )
            )
            ->will(
                $this->returnValue(
                    new PolicyCreateStruct(
                        array(
                            'module' => 'content',
                            'function' => 'delete'
                        )
                    )
                )
            );

        return $roleServiceMock;
    }
}
