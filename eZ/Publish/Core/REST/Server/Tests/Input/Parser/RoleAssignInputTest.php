<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\RoleAssignInput;

class RoleAssignInputTest extends BaseTest
{
    /**
     * Tests the RoleAssignInput parser.
     */
    public function testParse()
    {
        $inputArray = array(
            'Role' => array(
                '_href' => '/user/roles/42',
            ),
            'limitation' => array(
                '_identifier' => 'Section',
                'values' => array(
                    'ref' => array(
                        array(
                            '_href' => 1,
                        ),
                        array(
                            '_href' => 2,
                        ),
                        array(
                            '_href' => 3,
                        ),
                    ),
                ),
            ),
        );

        $roleAssignInput = $this->getParser();
        $result = $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());

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

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation',
            $result->limitation,
            'Limitation not created correctly.'
        );

        $this->assertEquals(
            'Section',
            $result->limitation->getIdentifier(),
            'Limitation identifier not created correctly.'
        );

        $this->assertEquals(
            array(1, 2, 3),
            $result->limitation->limitationValues,
            'Limitation values not created correctly.'
        );
    }

    /**
     * Test RoleAssignInput parser throwing exception on missing Role.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'Role' element for RoleAssignInput.
     */
    public function testParseExceptionOnMissingRole()
    {
        $inputArray = array(
            'limitation' => array(
                '_identifier' => 'Section',
                'values' => array(
                    'ref' => array(
                        array(
                            '_href' => 1,
                        ),
                        array(
                            '_href' => 2,
                        ),
                        array(
                            '_href' => 3,
                        ),
                    ),
                ),
            ),
        );

        $roleAssignInput = $this->getParser();
        $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test RoleAssignInput parser throwing exception on invalid Role.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'Role' element for RoleAssignInput.
     */
    public function testParseExceptionOnInvalidRole()
    {
        $inputArray = array(
            'Role' => array(),
            'limitation' => array(
                '_identifier' => 'Section',
                'values' => array(
                    'ref' => array(
                        array(
                            '_href' => 1,
                        ),
                        array(
                            '_href' => 2,
                        ),
                        array(
                            '_href' => 3,
                        ),
                    ),
                ),
            ),
        );

        $roleAssignInput = $this->getParser();
        $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test Limitation parser throwing exception on missing identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_identifier' attribute for Limitation.
     */
    public function testParseExceptionOnMissingLimitationIdentifier()
    {
        $inputArray = array(
            'Role' => array(
                '_href' => '/user/roles/42',
            ),
            'limitation' => array(
                'values' => array(
                    'ref' => array(
                        array(
                            '_href' => 1,
                        ),
                        array(
                            '_href' => 2,
                        ),
                        array(
                            '_href' => 3,
                        ),
                    ),
                ),
            ),
        );

        $roleAssignInput = $this->getParser();
        $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test Limitation parser throwing exception on missing values.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid format for limitation values in Limitation.
     */
    public function testParseExceptionOnMissingLimitationValues()
    {
        $inputArray = array(
            'Role' => array(
                '_href' => '/user/roles/42',
            ),
            'limitation' => array(
                '_identifier' => 'Section',
            ),
        );

        $roleAssignInput = $this->getParser();
        $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the role assign input parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\RoleAssignInput
     */
    protected function internalGetParser()
    {
        return new RoleAssignInput(
            $this->getParserTools()
        );
    }

    public function getParseHrefExpectationsMap()
    {
        return array(
            array('/user/roles/42', 'roleId', 42),
        );
    }
}
