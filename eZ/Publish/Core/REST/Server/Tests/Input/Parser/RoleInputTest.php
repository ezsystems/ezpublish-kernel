<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\Repository\RoleService;
use eZ\Publish\Core\REST\Server\Input\Parser\RoleInput;
use eZ\Publish\Core\Repository\Values\User\RoleCreateStruct;

class RoleInputTest extends BaseTest
{
    /**
     * Tests the RoleInput parser.
     */
    public function testParse()
    {
        $inputArray = [
            'identifier' => 'Identifier Bar',
            /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
            'mainLanguageCode' => 'eng-GB',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test role'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test role description'
                    )
                )
            )
            */
        ];

        $roleInput = $this->getParser();
        $result = $roleInput->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            RoleCreateStruct::class,
            $result,
            'RoleCreateStruct not created correctly.'
        );

        $this->assertEquals(
            'Identifier Bar',
            $result->identifier,
            'RoleCreateStruct identifier property not created correctly.'
        );

        /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        $this->assertEquals(
            array( 'eng-GB' => 'Test role' ),
            $result->names,
            'RoleCreateStruct names property not created correctly.'
        );

        $this->assertEquals(
            array( 'eng-GB' => 'Test role description' ),
            $result->descriptions,
            'RoleCreateStruct descriptions property not created correctly.'
        );
        */
    }

    /**
     * Returns the role input parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\RoleInput
     */
    protected function internalGetParser()
    {
        return new RoleInput(
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
            ->method('newRoleCreateStruct')
            ->with($this->equalTo('Identifier Bar'))
            ->will(
                $this->returnValue(new RoleCreateStruct(['identifier' => 'Identifier Bar']))
            );

        return $roleServiceMock;
    }
}
