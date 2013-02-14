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
use eZ\Publish\Core\REST\Server\Values\ObjectStateGroupList;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\Core\REST\Common;

class ObjectStateGroupListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ObjectStateGroupList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getObjectStateGroupListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $groupList = new ObjectStateGroupList( array() );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $groupList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains ObjectStateGroupList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupListElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'ObjectStateGroupList',
            ),
            $result,
            'Invalid <ObjectStateGroupList> element.',
            false
        );
    }

    /**
     * Test if result contains ObjectStateGroupList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'ObjectStateGroupList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ObjectStateGroupList+xml',
                    'href'       => '/content/objectstategroups',
                )
            ),
            $result,
            'Invalid <ObjectStateGroupList> attributes.',
            false
        );
    }

    /**
     * Test if ObjectStateGroupList visitor visits the children
     */
    public function testObjectStateGroupListVisitsChildren()
    {
        $visitor   = $this->getObjectStateGroupListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $groupList = new ObjectStateGroupList(
            array(
                new ObjectStateGroup(),
                new ObjectStateGroup(),
            )
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $groupList
        );
    }

    /**
     * Get the ObjectStateGroupList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ObjectStateGroupList
     */
    protected function getObjectStateGroupListVisitor()
    {
        return new ValueObjectVisitor\ObjectStateGroupList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
