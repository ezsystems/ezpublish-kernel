<?php

/**
 * File containing a PolicyTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\API\Repository\Values\User\Policy;

class PolicyTest extends BaseTest
{
    /**
     * Tests the policy parser.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function testParse()
    {
        $policyParser = $this->getParser();

        $inputArray = array(
            'id' => '42',
            'module' => 'content',
            'function' => 'delete',
        );

        $result = $policyParser->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Tests that the resulting policy is in fact an instance of Policy class.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $result
     *
     * @depends testParse
     */
    public function testResultIsPolicy($result)
    {
        $this->assertInstanceOf(Policy::class, $result);
    }

    /**
     * Tests that the resulting policy contains the ID.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $result
     *
     * @depends testParse
     */
    public function testResultContainsId($result)
    {
        $this->assertEquals(
            '42',
            $result->id
        );
    }

    /**
     * Tests that the resulting policy contains module.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $result
     *
     * @depends testParse
     */
    public function testResultContainsModule($result)
    {
        $this->assertEquals(
            'content',
            $result->module
        );
    }

    /**
     * Tests that the resulting policy contains function.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $result
     *
     * @depends testParse
     */
    public function testResultContainsFunction($result)
    {
        $this->assertEquals(
            'delete',
            $result->function
        );
    }

    /**
     * Gets the parser for policy.
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Policy;
     */
    protected function getParser()
    {
        return new Parser\Policy();
    }
}
