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
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Common;

class NoContentTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the NoContent visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getNoContentVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $noContent = new Values\NoContent();

        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'setStatus' )
            ->with( $this->equalTo( 204 ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $noContent
        );

        $this->assertTrue( $generator->isEmpty() );
    }

    /**
     * Get the NoContent visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\NoContent
     */
    protected function getNoContentVisitor()
    {
        return new ValueObjectVisitor\NoContent(
            new Common\UrlHandler\eZPublish()
        );
    }
}
