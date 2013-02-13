<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\URLWildcardCreate;

class URLWildcardCreateTest extends BaseTest
{
    /**
     * Tests the URLWildcardCreate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'sourceUrl' => '/source/url',
            'destinationUrl' => '/destination/url',
            'forward' => 'true'
        );

        $urlWildcardCreate = $this->getURLWildcardCreate();
        $result = $urlWildcardCreate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertEquals(
            array(
                'sourceUrl' => '/source/url',
                'destinationUrl' => '/destination/url',
                'forward' => true
            ),
            $result,
            'URLWildcardCreate not parsed correctly.'
        );
    }

    /**
     * Test URLWildcardCreate parser throwing exception on missing sourceUrl
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'sourceUrl' value for URLWildcardCreate.
     */
    public function testParseExceptionOnMissingSourceUrl()
    {
        $inputArray = array(
            'destinationUrl' => '/destination/url',
            'forward' => 'true'
        );

        $urlWildcardCreate = $this->getURLWildcardCreate();
        $urlWildcardCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test URLWildcardCreate parser throwing exception on missing destinationUrl
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'destinationUrl' value for URLWildcardCreate.
     */
    public function testParseExceptionOnMissingDestinationUrl()
    {
        $inputArray = array(
            'sourceUrl' => '/source/url',
            'forward' => 'true'
        );

        $urlWildcardCreate = $this->getURLWildcardCreate();
        $urlWildcardCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test URLWildcardCreate parser throwing exception on missing forward
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'forward' value for URLWildcardCreate.
     */
    public function testParseExceptionOnMissingForward()
    {
        $inputArray = array(
            'sourceUrl' => '/source/url',
            'destinationUrl' => '/destination/url'
        );

        $urlWildcardCreate = $this->getURLWildcardCreate();
        $urlWildcardCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the URLWildcard input parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\URLWildcardCreate
     */
    protected function getURLWildcardCreate()
    {
        return new URLWildcardCreate( $this->getUrlHandler(), $this->getParserTools() );
    }
}
