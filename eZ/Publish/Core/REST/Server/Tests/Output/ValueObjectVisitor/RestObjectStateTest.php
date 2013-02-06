<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\Core\REST\Common\Values;
use eZ\Publish\Core\REST\Common;

class RestObjectStateTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestObjectState visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getObjectStateVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $objectState = new Values\RestObjectState(
            new ObjectState(
                array(
                    'id'         => 42,
                    'identifier' => 'test-state',
                    'priority' => '0',
                    'defaultLanguageCode' => 'eng-GB',
                    'languageCodes' => array( 'eng-GB', 'eng-US' ),
                    'names' => array(
                        'eng-GB' => 'State name EN',
                        'eng-US' => 'State name EN US',
                    ),
                    'descriptions' => array(
                        'eng-GB' => 'State description EN',
                        'eng-US' => 'State description EN US',
                    )
                )
            ),
            21
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $objectState
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains ObjectState element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ObjectState',
                'children' => array(
                    'count' => 8
                )
            ),
            $result,
            'Invalid <ObjectState> element.',
            false
        );
    }

    /**
     * Test if result contains ObjectState element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ObjectState',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ObjectState+xml',
                    'href'       => '/content/objectstategroups/21/objectstates/42',
                )
            ),
            $result,
            'Invalid <ObjectState> attributes.',
            false
        );
    }

    /**
     * Test if result contains ObjectStateGroup element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ObjectStateGroup',
            ),
            $result,
            'Invalid <ObjectStateGroup> element.',
            false
        );
    }

    /**
     * Test if result contains ObjectStateGroup element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ObjectStateGroup',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ObjectStateGroup+xml',
                    'href'       => '/content/objectstategroups/21',
                )
            ),
            $result,
            'Invalid <ObjectStateGroup> attributes.',
            false
        );
    }

    /**
     * Test if result contains id value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'id',
                'content'  => '42'
            ),
            $result,
            'Invalid or non-existing <ObjectState> id value element.',
            false
        );
    }

    /**
     * Test if result contains identifier value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'identifier',
                'content'  => 'test-state'
            ),
            $result,
            'Invalid or non-existing <ObjectState> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains priority value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPriorityValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'priority',
                'content'  => '0'
            ),
            $result,
            'Invalid or non-existing <ObjectState> priority value element.',
            false
        );
    }

    /**
     * Test if result contains defaultLanguageCode value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDefaultLanguageCodeValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'defaultLanguageCode',
                'content'  => 'eng-GB'
            ),
            $result,
            'Invalid or non-existing <ObjectState> defaultLanguageCode value element.',
            false
        );
    }

    /**
     * Test if result contains languageCodes value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLanguageCodesValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'languageCodes',
                'content'  => 'eng-GB,eng-US'
            ),
            $result,
            'Invalid or non-existing <ObjectState> languageCodes value element.',
            false
        );
    }

    /**
     * Test if result contains names element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNamesElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'names',
                'children' => array(
                    'count' => 2
                )
            ),
            $result,
            'Invalid <names> element.',
            false
        );
    }

    /**
     * Test if result contains descriptions element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDescriptionsElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'descriptions',
                'children' => array(
                    'count' => 2
                )
            ),
            $result,
            'Invalid <descriptions> element.',
            false
        );
    }

    /**
     * Get the ObjectState visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestObjectState
     */
    protected function getObjectStateVisitor()
    {
        return new ValueObjectVisitor\RestObjectState(
            new Common\UrlHandler\eZPublish()
        );
    }
}
