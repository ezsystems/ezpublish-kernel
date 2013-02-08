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

class ResourceCreatedTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ResourceCreated visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getResourceCreatedVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $resourceCreated = new Values\ResourceCreated(
            '/some/redirect/uri'
        );

        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'setStatus' )
            ->with( $this->equalTo( 201 ) );
        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'setHeader' )
            ->with( $this->equalTo( 'Location' ), $this->equalTo( '/some/redirect/uri' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $resourceCreated
        );

        $this->assertTrue( $generator->isEmpty() );
    }

    /**
     * Get the ResourceCreated visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ResourceCreated
     */
    protected function getResourceCreatedVisitor()
    {
        return new ValueObjectVisitor\ResourceCreated(
            new Common\UrlHandler\eZPublish()
        );
    }
}
