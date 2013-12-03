<?php
/**
 * File containing the ProviderTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Authentication;

use eZ\Publish\Core\MVC\Symfony\Security\Authentication\Provider;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\User\UserChecker;

class ProviderTest extends PHPUnit_Framework_TestCase
{
    const PROVIDER_KEY = 'i_am_a_provider_key';

    /**
     * @var \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\UserService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userService;

    /**
     * @var Provider
     */
    private $authenticationProvider;

    /**
     * @var \Symfony\Component\Security\Core\User\UserProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userProvider;

    /**
     * @var \Symfony\Component\Security\Core\User\UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    protected function setUp()
    {
        parent::setUp();
        $repository = $this->repository = $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' );
        $this->userService = $this->getMock( 'eZ\\Publish\\API\\Repository\\UserService' );
        $this->repository
            ->expects( $this->any() )
            ->method( 'getUserService' )
            ->will( $this->returnValue( $this->userService ) );

        $this->userProvider = $this->getMock( 'Symfony\\Component\\Security\\Core\\User\\UserProviderInterface' );
        $this->userChecker = new UserChecker();
        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );
        $this->configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );

        $this->authenticationProvider = new Provider(
            $this->userProvider,
            $this->userChecker,
            static::PROVIDER_KEY
        );
        $this->authenticationProvider->setLazyRepository(
            function () use ( $repository )
            {
                return $repository;
            }
        );
        $this->authenticationProvider->setConfigResolver( $this->configResolver );
        $this->authenticationProvider->setLogger( $this->logger );
    }

    public function testAuthenticateNoSupport()
    {
        $this->assertNull(
            $this->authenticationProvider->authenticate(
                $this->getMock( 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface' )
            )
        );
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testAuthenticateUnsupportedUser()
    {
        $username = 'foo';
        $token = new PreAuthenticatedToken( $username, '', static::PROVIDER_KEY );

        $user = $this->getMock( 'Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface' );
        $user
            ->expects( $this->once() )
            ->method( 'getRoles' )
            ->will( $this->returnValue( array() ) );
        $user
            ->expects( $this->once() )
            ->method( 'isAccountNonLocked' )
            ->will( $this->returnValue( true ) );
        $user
            ->expects( $this->once() )
            ->method( 'isEnabled' )
            ->will( $this->returnValue( true ) );
        $user
            ->expects( $this->once() )
            ->method( 'isAccountNonExpired' )
            ->will( $this->returnValue( true ) );

        $this->userProvider
            ->expects( $this->once() )
            ->method( 'loadUserByUsername' )
            ->with( $username )
            ->will( $this->returnValue( $user ) );
        $this->authenticationProvider->authenticate( $token );
    }

    public function testAuthenticateSucceed()
    {
        $userId = '123';
        $roles = array( 'ROLE_USER' );
        $token = new PreAuthenticatedToken( $userId, '', static::PROVIDER_KEY, $roles );
        $apiUser = $this->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\User\\User' )
            ->setConstructorArgs( array( array( 'enabled' => true ) ) )
            ->getMockForAbstractClass();
        $user = new User(
            $apiUser,
            $roles
        );

        $this->userProvider
            ->expects( $this->once() )
            ->method( 'loadUserByUsername' )
            ->with( $userId )
            ->will( $this->returnValue( $user ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'setCurrentUser' )
            ->with( $apiUser );

        $authenticatedToken = $this->authenticationProvider->authenticate( $token );
        $this->assertInstanceOf( 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken', $authenticatedToken );
    }

    /**
     * Regression test for EZP-21520 and EZP-20721
     *
     * @see https://jira.ez.no/browse/EZP-21520
     */
    public function testAuthenticationDisabledUser()
    {
        $userId = '123';
        $roles = array( 'ROLE_USER' );
        $token = new PreAuthenticatedToken( $userId, '', static::PROVIDER_KEY, $roles );
        $apiUser = $this->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\User\\User' )
            ->setConstructorArgs( array( array( 'enabled' => false ) ) )
            ->getMockForAbstractClass();
        $user = new User(
            $apiUser,
            $roles
        );

        $this->userProvider
            ->expects( $this->once() )
            ->method( 'loadUserByUsername' )
            ->with( $userId )
            ->will( $this->returnValue( $user ) );

        $this->logger
            ->expects( $this->once() )
            ->method( 'warning' );

        $this->configResolver
            ->expects( $this->once() )
            ->method( "getParameter" )
            ->with( "anonymous_user_id" )
            ->will( $this->returnValue( 10 ) );

        $anonymousUser = $this->getMockForAbstractClass( 'eZ\\Publish\\API\\Repository\\Values\\User\\User' );
        $this->userService
            ->expects( $this->once() )
            ->method( 'loadUser' )
            ->with( 10 )
            ->will( $this->returnValue( $anonymousUser ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'setCurrentUser' )
            ->with( $anonymousUser );

        $authenticatedToken = $this->authenticationProvider->authenticate( $token );
        $this->assertInstanceOf( 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken', $authenticatedToken );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\Security\\User', $authenticatedToken->getUser() );
        $this->assertSame( $anonymousUser, $authenticatedToken->getUser()->getAPIUser() );
        $this->assertFalse( $authenticatedToken->isAuthenticated() );
    }
}
