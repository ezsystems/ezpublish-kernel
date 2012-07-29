<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\Limitation;

class LimitationTest extends BaseTest
{
    /**
     * Tests the Limitation parser
     */
    public function testParse()
    {
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

        $limitation = $this->getLimitation();
        $result = $limitation->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Limitation',
            $result,
            'Limitation not created correctly.'
        );

        $this->assertEquals(
            'Class',
            $result->getIdentifier(),
            'Limitation identifier not created correctly.'
        );

        $this->assertEquals(
            array( 1, 2, 3 ),
            $result->limitationValues,
            'Limitation values not created correctly.'
        );
    }

    /**
     * Test Limitation parser throwing exception on missing identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_identifier' attribute for Limitation.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = array(
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

        $limitation = $this->getLimitation();
        $limitation->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test Limitation parser throwing exception on missing values
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid format for limitation values in Limitation.
     */
    public function testParseExceptionOnMissingFunction()
    {
        $inputArray = array(
            '_identifier' => 'Class'
        );

        $limitation = $this->getLimitation();
        $limitation->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the Limitation parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Limitation
     */
    protected function getLimitation()
    {
        return new Limitation( $this->getUrlHandler(), $this->getRepository()->getRoleService() );
    }
}
