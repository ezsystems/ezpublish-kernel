<?php

/**
 * File containing a RoleTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\API\Repository\Values\User\Role;

class RoleTest extends BaseTest
{
    /**
     * Tests the role parser.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function testParse()
    {
        $roleParser = $this->getParser();

        $inputArray = array(
            '_href' => '/user/roles/6',
            'identifier' => 'some-role',
        );

        $result = $roleParser->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Tests that the resulting role is in fact an instance of Role class.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $result
     *
     * @depends testParse
     */
    public function testResultIsRole($result)
    {
        $this->assertInstanceOf(Role::class, $result);
    }

    /**
     * Tests that the resulting role contains the ID.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $result
     *
     * @depends testParse
     */
    public function testResultContainsId($result)
    {
        $this->assertEquals(
            '/user/roles/6',
            $result->id
        );
    }

    /**
     * Tests that the resulting role contains identifier.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $result
     *
     * @depends testParse
     */
    public function testResultContainsIdentifier($result)
    {
        $this->assertEquals(
            'some-role',
            $result->identifier
        );
    }

    /**
     * Gets the parser for role.
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Role;
     */
    protected function getParser()
    {
        return new Parser\Role();
    }
}
