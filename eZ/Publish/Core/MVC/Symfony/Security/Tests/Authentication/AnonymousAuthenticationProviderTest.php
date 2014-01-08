<?php
/**
 * File containing the AnonymousAuthenticationProviderTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Authentication;

use eZ\Publish\Core\MVC\Symfony\Security\Authentication\AnonymousAuthenticationProvider;
use PHPUnit_Framework_TestCase;

class AnonymousAuthenticationProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \Closure
     */
    private $lazyRepository;

    protected function setUp()
    {
        parent::setUp();
        $repository = $this->repository = $this->getMock( 'eZ\Publish\API\Repository\Repository' );
        $this->lazyRepository = function () use ( $repository )
        {
            return $repository;
        };
    }

    public function testAuthenticate()
    {
        $userService = $this->getMock( 'eZ\Publish\API\Repository\UserService' );
        $anonymousUser = $this->getMock( 'eZ\Publish\API\Repository\Values\User\User' );
        $userService
            ->expects( $this->once() )
            ->method( 'loadAnonymousUser' )
            ->will( $this->returnValue( $anonymousUser ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'getUserService' )
            ->will( $this->returnValue( $userService ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'setCurrentUser' )
            ->with( $anonymousUser );

        $key = 'some_key';
        $authProvider = new AnonymousAuthenticationProvider( $key );
        $authProvider->setLazyRepository( $this->lazyRepository );
        $anonymousToken = $this
            ->getMockBuilder( 'Symfony\Component\Security\Core\Authentication\Token\AnonymousToken' )
            ->setConstructorArgs( array( $key, $this->getMock( 'Symfony\Component\Security\Core\User\UserInterface' ) ) )
            ->getMockForAbstractClass();
        $this->assertSame( $anonymousToken, $authProvider->authenticate( $anonymousToken ) );
    }
}
