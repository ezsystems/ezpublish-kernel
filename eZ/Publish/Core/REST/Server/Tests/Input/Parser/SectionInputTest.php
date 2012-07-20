<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\SectionInput;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;

class SectionInputTest extends BaseTest
{
    /**
     * Tests the SectionInput parser
     */
    public function testParse()
    {
        $inputArray = array(
            'name'       => 'Name Foo',
            'identifier' => 'Identifier Bar',
        );

        $sectionInput = $this->getSectionInput();
        $result = $sectionInput->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertEquals(
            new SectionCreateStruct( $inputArray ),
            $result,
            'SectionCreateStruct not created correctly.'
        );
    }

    /**
     * Test SectionInput parser throwing exception on missing identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' attribute for SectionInput.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = array(
            'name'       => 'Name Foo',
        );

        $sectionInput = $this->getSectionInput();
        $sectionInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test SectionInput parser throwing exception on missing name
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'name' attribute for SectionInput.
     */
    public function testParseExceptionOnMissingName()
    {
        $inputArray = array(
            'identifier' => 'Identifier Bar',
        );

        $sectionInput = $this->getSectionInput();
        $sectionInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the section input parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\SectionInput
     */
    protected function getSectionInput()
    {
        return new SectionInput( $this->getUrlHandler(), $this->getRepository()->getSectionService() );
    }
}
