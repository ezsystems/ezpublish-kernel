<?php
/**
 * File containing the AnonymousAuthenticationProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->getMock( 'eZ\Publish\API\Repository\Repository' );
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
    }

    public function testAuthenticate()
    {
        $anonymousUserId = 10;
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'anonymous_user_id' )
            ->will( $this->returnValue( $anonymousUserId ) );
        $userService = $this->getMock( 'eZ\Publish\API\Repository\UserService' );
        $anonymousUser = $this->getMock( 'eZ\Publish\API\Repository\Values\User\User' );
        $userService
            ->expects( $this->once() )
            ->method( 'loadUser' )
            ->with( $anonymousUserId )
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
        $authProvider->setRepository( $this->repository );
        $authProvider->setConfigResolver( $this->configResolver );
        $anonymousToken = $this
            ->getMockBuilder( 'Symfony\Component\Security\Core\Authentication\Token\AnonymousToken' )
            ->setConstructorArgs( array( $key, $this->getMock( 'Symfony\Component\Security\Core\User\UserInterface' ) ) )
            ->getMockForAbstractClass();
        $this->assertSame( $anonymousToken, $authProvider->authenticate( $anonymousToken ) );
    }
}
