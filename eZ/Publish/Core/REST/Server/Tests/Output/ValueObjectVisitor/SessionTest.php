<?php
/**
 * File containing the SessionTest class
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

class SessionTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Session visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getSessionVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $session = new Values\UserSession(
            $this->getUserMock(),
            "sessionName",
            "sessionId",
            "csrfToken"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $session
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains Session element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSessionElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'Session',
                'children' => array(
                    'count' => 4,
                )
            ),
            $result,
            'Invalid <Session> element.',
            false
        );
    }

    /**
     * Test if result contains Session element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSessionAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'Session',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Session+xml',
                    'href'       => '/user/sessions/sessionId',
                )
            ),
            $result,
            'Invalid <Session> attributes.',
            false
        );
    }

    /**
     * Test if result contains name value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNameValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'name',
                'content' => 'sessionName',
            ),
            $result,
            'Invalid or non-existing <Session> name value element.',
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
                'tag' => 'identifier',
                'content' => 'sessionId',
            ),
            $result,
            'Invalid or non-existing <Session> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains csrf-token value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsCsrfTokenValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'csrfToken',
                'content' => 'csrfToken',
            ),
            $result,
            'Invalid or non-existing <Session> csrf-token value element.',
            false
        );
    }

    protected function getUserMock()
    {
        $user = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $user->expects( $this->any() )
            ->method( "__get" )
            ->with( $this->equalTo( "id" ) )
            ->will( $this->returnValue( "user123" ) );

        return $user;
    }

    /**
     * Test if result contains User element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'User'
            ),
            $result,
            'Invalid <User> element.',
            false
        );
    }

    /**
     * Test if result contains User element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'User',
                'attributes' => array(
                    'href' => '/user/users/user123',
                    'media-type' => 'application/vnd.ez.api.User+xml'
                )
            ),
            $result,
            'Invalid <User> element attributes.',
            false
        );
    }

    /**
     * Get the Session visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\UserSession
     */
    protected function getSessionVisitor()
    {
        return new ValueObjectVisitor\UserSession(
            new Common\UrlHandler\eZPublish()
        );
    }
}
