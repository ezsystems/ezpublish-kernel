<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Tests\Input\Parser;
use eZ\Publish\API\REST\Server\Tests\BaseTest;

use eZ\Publish\API\REST\Server\Input\Parser\SectionInput;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;

class SectionInputTest extends BaseTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     *
     * @todo: This and its creation could be moved to common base test class
     *        for input parsers.
     */
    protected $parsingDispatcherMock;

    /**
     * testParse
     *
     * @return void
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
     * testParseExceptionOnMissingIdentifier
     *
     * @return void
     * @expectedException \eZ\Publish\API\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' attribute for SectionInput.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = array(
            'name'       => 'Name Foo',
        );

        $sectionInput = $this->getSectionInput();
        $result = $sectionInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * testParseExceptionOnMissingName
     *
     * @return void
     * @expectedException \eZ\Publish\API\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'name' attribute for SectionInput.
     */
    public function testParseExceptionOnMissingName()
    {
        $inputArray = array(
            'identifier' => 'Identifier Bar',
        );

        $sectionInput = $this->getSectionInput();
        $result = $sectionInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * @return \eZ\Publish\API\REST\Server\Input\Parser\SectionInput
     */
    protected function getSectionInput()
    {
        return new SectionInput();
    }

    /**
     * @var \eZ\Publish\API\REST\Common\Input\ParsingDispatcher
     */
    protected function getParsingDispatcherMock()
    {
        if ( !isset( $this->parsingDispatcherMock ) )
        {
            $this->parsingDispatcherMock = $this->getMock(
                '\\eZ\\Publish\\API\\REST\\Common\\Input\\ParsingDispatcher',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->parsingDispatcherMock;
    }
}
