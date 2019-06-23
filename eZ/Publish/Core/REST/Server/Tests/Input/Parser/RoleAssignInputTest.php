<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\Core\REST\Server\Input\Parser\RoleAssignInput;
use eZ\Publish\Core\REST\Server\Values\RoleAssignment;

class RoleAssignInputTest extends BaseTest
{
    /**
     * Tests the RoleAssignInput parser.
     */
    public function testParse()
    {
        $limitation = [
            '_identifier' => 'Section',
            'values' => [
                'ref' => [['_href' => '/content/sections/1']],
            ],
        ];

        $inputArray = [
            'Role' => ['_href' => '/user/roles/42'],
            'limitation' => $limitation,
        ];

        $this->getParsingDispatcherMock()
            ->expects($this->once())
            ->method('parse')
            ->with($limitation, 'application/vnd.ez.api.internal.limitation.Section')
            ->will($this->returnValue(new SectionLimitation()));

        $roleAssignInput = $this->getParser();
        $result = $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            RoleAssignment::class,
            $result,
            'RoleAssignment not created correctly.'
        );

        $this->assertEquals(
            '42',
            $result->roleId,
            'RoleAssignment roleId property not created correctly.'
        );

        $this->assertInstanceOf(
            RoleLimitation::class,
            $result->limitation,
            'Limitation not created correctly.'
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
        $inputArray = [
            'limitation' => [
                '_identifier' => 'Section',
                'values' => [
                    'ref' => [
                        [
                            '_href' => '/content/sections/1',
                        ],
                        [
                            '_href' => '/content/sections/2',
                        ],
                        [
                            '_href' => '/content/sections/3',
                        ],
                    ],
                ],
            ],
        ];

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
        $inputArray = [
            'Role' => [],
            'limitation' => [
                '_identifier' => 'Section',
                'values' => [
                    'ref' => [
                        [
                            '_href' => '/content/sections/1',
                        ],
                        [
                            '_href' => '/content/sections/2',
                        ],
                        [
                            '_href' => '/content/sections/3',
                        ],
                    ],
                ],
            ],
        ];

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
        $inputArray = [
            'Role' => [
                '_href' => '/user/roles/42',
            ],
            'limitation' => [
                'values' => [
                    'ref' => [
                        [
                            '_href' => '/content/sections/1',
                        ],
                        [
                            '_href' => '/content/sections/2',
                        ],
                        [
                            '_href' => '/content/sections/3',
                        ],
                    ],
                ],
            ],
        ];

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
        return [
            ['/user/roles/42', 'roleId', 42],
        ];
    }
}
