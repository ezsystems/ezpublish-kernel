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
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Common;

class ResourceDeletedTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ResourceDeleted visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getResourceDeletedVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $redirect = new Values\ResourceDeleted();

        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'setStatus' )
            ->with( $this->equalTo( 204 ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $redirect
        );

        $this->assertTrue( $generator->isEmpty() );
    }

    /**
     * Get the ResourceRedirect visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ResourceDeleted
     */
    protected function getResourceDeletedVisitor()
    {
        return new ValueObjectVisitor\ResourceDeleted(
            new Common\UrlHandler\eZPublish()
        );
    }
}
