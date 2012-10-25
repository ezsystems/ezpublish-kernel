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

class TemporaryRedirectTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the TemporaryRedirect visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getTemporaryRedirectVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $redirect = new Values\TemporaryRedirect( '/some/redirect/uri' );

        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'setStatus' )
            ->with( $this->equalTo( 307 ) );
        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'setHeader' )
            ->with( $this->equalTo( 'Location' ), $this->equalTo( '/some/redirect/uri' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $redirect
        );

        $this->assertTrue( $generator->isEmpty() );
    }

    /**
     * Get the TemporaryRedirect visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\TemporaryRedirect
     */
    protected function getTemporaryRedirectVisitor()
    {
        return new ValueObjectVisitor\TemporaryRedirect(
            new Common\UrlHandler\eZPublish()
        );
    }
}
