<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Repository\RoleService;
use eZ\Publish\Core\REST\Server\Input\Parser\PolicyUpdate;
use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;

class PolicyUpdateTest extends BaseTest
{
    /**
     * Tests the PolicyUpdate parser.
     */
    public function testParse()
    {
        $inputArray = [
            'limitations' => [
                'limitation' => [
                    [
                        '_identifier' => 'Class',
                        'values' => [
                            'ref' => [
                                [
                                    '_href' => 1,
                                ],
                                [
                                    '_href' => 2,
                                ],
                                [
                                    '_href' => 3,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $policyUpdate = $this->getParser();
        $result = $policyUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            PolicyUpdateStruct::class,
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
            Limitation::class,
            $parsedLimitations['Class'],
            'Limitation not created correctly.'
        );

        $this->assertEquals(
            'Class',
            $parsedLimitations['Class']->getIdentifier(),
            'Limitation identifier not created correctly.'
        );

        $this->assertEquals(
            [1, 2, 3],
            $parsedLimitations['Class']->limitationValues,
            'Limitation values not created correctly.'
        );
    }

    /**
     * Test PolicyUpdate parser throwing exception on missing identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_identifier' attribute for Limitation.
     */
    public function testParseExceptionOnMissingLimitationIdentifier()
    {
        $inputArray = [
            'limitations' => [
                'limitation' => [
                    [
                        'values' => [
                            'ref' => [
                                [
                                    '_href' => 1,
                                ],
                                [
                                    '_href' => 2,
                                ],
                                [
                                    '_href' => 3,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $policyUpdate = $this->getParser();
        $policyUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test PolicyUpdate parser throwing exception on missing values.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid format for limitation values in Limitation.
     */
    public function testParseExceptionOnMissingLimitationValues()
    {
        $inputArray = [
            'limitations' => [
                'limitation' => [
                    [
                        '_identifier' => 'Class',
                    ],
                ],
            ],
        ];

        $policyUpdate = $this->getParser();
        $policyUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the PolicyUpdateStruct parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\PolicyUpdate
     */
    protected function internalGetParser()
    {
        return new PolicyUpdate(
            $this->getRoleServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the role service mock object.
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    protected function getRoleServiceMock()
    {
        $roleServiceMock = $this->createMock(RoleService::class);

        $roleServiceMock->expects($this->any())
            ->method('newPolicyUpdateStruct')
            ->will(
                $this->returnValue(new PolicyUpdateStruct())
            );

        return $roleServiceMock;
    }
}
