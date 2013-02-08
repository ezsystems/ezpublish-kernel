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
use eZ\Publish\Core\Repository\Values\ObjectState;
use eZ\Publish\Core\REST\Common;

class ObjectStateGroupTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ObjectStateGroup visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getObjectStateGroupVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $objectStateGroup = new ObjectState\ObjectStateGroup(
            array(
                'id'         => 42,
                'identifier' => 'test-group',
                'defaultLanguageCode' => 'eng-GB',
                'languageCodes' => array( 'eng-GB', 'eng-US' ),
                'names' => array(
                    'eng-GB' => 'Group name EN',
                    'eng-US' => 'Group name EN US',
                ),
                'descriptions' => array(
                    'eng-GB' => 'Group description EN',
                    'eng-US' => 'Group description EN US',
                )
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $objectStateGroup
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
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
                'children' => array(
                    'count' => 6
                )
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
                    'href'       => '/content/objectstategroups/42',
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
            'Invalid or non-existing <ObjectStateGroup> id value element.',
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
                'content'  => 'test-group'
            ),
            $result,
            'Invalid or non-existing <ObjectStateGroup> identifier value element.',
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
            'Invalid or non-existing <ObjectStateGroup> defaultLanguageCode value element.',
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
            'Invalid or non-existing <ObjectStateGroup> languageCodes value element.',
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
     * Get the ObjectStateGroup visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ObjectStateGroup
     */
    protected function getObjectStateGroupVisitor()
    {
        return new ValueObjectVisitor\ObjectStateGroup(
            new Common\UrlHandler\eZPublish()
        );
    }
}
