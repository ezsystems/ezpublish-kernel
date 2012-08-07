<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\ObjectStateList;
use eZ\Publish\Core\REST\Common;

class ObjectStateListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ObjectStateList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getObjectStateListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $stateList = new ObjectStateList( array(), 42 );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $stateList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains ObjectStateList element
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsObjectStateListElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'ObjectStateList',
            ),
            $result,
            'Invalid <ObjectStateList> element.',
            false
        );
    }

    /**
     * Test if result contains ObjectStateList element attributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsObjectStateListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'ObjectStateList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ObjectStateList+xml',
                    'href'       => '/content/objectstategroups/42/objectstates',
                )
            ),
            $result,
            'Invalid <ObjectStateList> attributes.',
            false
        );
    }

    /**
     * Get the ObjectStateList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ObjectStateList
     */
    protected function getObjectStateListVisitor()
    {
        return new ValueObjectVisitor\ObjectStateList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
