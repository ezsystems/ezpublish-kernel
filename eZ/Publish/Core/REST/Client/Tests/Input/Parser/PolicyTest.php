<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;

class PolicyTest extends BaseTest
{
    /**
     * Tests the policy parser
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
                    ),
                    array(
                        '_identifier' => 'Section',
                        'values' => array(
                            'ref' => array(
                                array(
                                    '_href' => 4
                                ),
                                array(
                                    '_href' => 5
                                ),
                                array(
                                    '_href' => 6
                                )
                            )
                        )
                    )
                )
            )
        );

        $result = $policyParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the resulting policy is in fact an instance of Policy class
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $result
     * @depends testParse
     */
    public function testResultIsPolicy( $result )
    {
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Policy',
            $result
        );
    }

    /**
     * Tests that the resulting policy contains the ID
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $result
     * @depends testParse
     */
    public function testResultContainsId( $result )
    {
        $this->assertEquals(
            '42',
            $result->id
        );
    }

    /**
     * Tests that the resulting policy contains module
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $result
     * @depends testParse
     */
    public function testResultContainsModule( $result )
    {
        $this->assertEquals(
            'content',
            $result->module
        );
    }

    /**
     * Tests that the resulting policy contains function
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $result
     * @depends testParse
     */
    public function testResultContainsFunction( $result )
    {
        $this->assertEquals(
            'delete',
            $result->function
        );
    }

    /**
     * Tests that the resulting policy contains limitations
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $result
     * @depends testParse
     */
    public function testResultContainsLimitations( $result )
    {
        $contentTypeLimitation = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $contentTypeLimitation->limitationValues = array( 1, 2, 3 );

        $sectionLimitation = new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();
        $sectionLimitation->limitationValues = array( 4, 5, 6 );

        $this->assertEquals(
            array(
                $contentTypeLimitation,
                $sectionLimitation
            ),
            $result->limitations
        );
    }

    /**
     * Gets the parser for policy
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Policy;
     */
    protected function getParser()
    {
        return new Parser\Policy();
    }
}
