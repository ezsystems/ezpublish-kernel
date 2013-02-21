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
use eZ\Publish\Core\REST\Server\Values\Trash;
use eZ\Publish\Core\REST\Server\Values\RestTrashItem;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\Repository\Values\Content;

class TrashTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Trash visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getTrashVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $trash = new Trash( array(), '/content/trash' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $trash
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains Trash element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsTrashElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'Trash',
            ),
            $result,
            'Invalid <Trash> element.',
            false
        );
    }

    /**
     * Test if result contains Trash element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsTrashAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'Trash',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Trash+xml',
                    'href' => '/content/trash',
                )
            ),
            $result,
            'Invalid <Trash> attributes.',
            false
        );
    }

    /**
     * Test if Trash visitor visits the children
     */
    public function testTrashVisitsChildren()
    {
        $visitor   = $this->getTrashVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $trashList = new Trash(
            array(
                new RestTrashItem(
                    new Content\TrashItem(),
                    // Dummy value for ChildCount
                    0
                ),
                new RestTrashItem(
                    new Content\TrashItem(),
                    // Dummy value for ChildCount
                    0
                ),
            ),
            '/content/trash'
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Server\\Values\\RestTrashItem' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $trashList
        );
    }

    /**
     * Get the Trash visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Trash
     */
    protected function getTrashVisitor()
    {
        return new ValueObjectVisitor\Trash(
            new Common\UrlHandler\eZPublish()
        );
    }
}
