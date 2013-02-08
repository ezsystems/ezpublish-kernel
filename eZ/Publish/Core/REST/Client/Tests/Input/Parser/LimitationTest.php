<?php
/**
 * File containing a LimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;

class LimitationTest extends BaseTest
{
    /**
     * Tests the Limitation parser
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function testParse()
    {
        $limitationParser = $this->getParser();

        $inputArray = array(
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
        );

        $result = $limitationParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the resulting policy is in fact an instance of Limitation class
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $result
     *
     * @depends testParse
     */
    public function testResultIsLimitation( $result )
    {
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Limitation',
            $result
        );
    }

    /**
     * Tests that the resulting policy contains the identifier
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $result
     *
     * @depends testParse
     */
    public function testResultContainsIdentifier( $result )
    {
        $this->assertEquals(
            'Class',
            $result->getIdentifier()
        );
    }

    /**
     * Tests that the resulting policy contains limitation values
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $result
     *
     * @depends testParse
     */
    public function testResultContainsLimitationValues( $result )
    {
        $this->assertEquals(
            array( 1, 2, 3 ),
            $result->limitationValues
        );
    }

    /**
     * Gets the parser for Limitation
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Limitation
     */
    protected function getParser()
    {
        return new Parser\Limitation();
    }
}
