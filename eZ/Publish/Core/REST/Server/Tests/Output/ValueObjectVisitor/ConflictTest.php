<?php
/**
 * File containing the ConflictTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Common;

class ConflictTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Conflict visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getConflictVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $noContent = new Values\Conflict();

        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'setStatus' )
            ->with( $this->equalTo( 409 ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $noContent
        );

        $this->assertTrue( $generator->isEmpty() );
    }

    /**
     * Get the Conflict visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Conflict
     */
    protected function getConflictVisitor()
    {
        return new ValueObjectVisitor\Conflict(
            new Common\UrlHandler\eZPublish()
        );
    }
}
