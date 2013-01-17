<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Test case for Persistence\Cache\UserHandler
 */
class UserHandlerTest extends HandlerTest
{
    /**
     * @return array
     */
    function providerForUnCachedMethods()
    {
        return array(
            array( 'create', array( new User ) ),
            array( 'load', array( 14 ) ),
            array( 'loadByLogin', array( 'admin', true ) ),
            array( 'update', array( new User ) ),
            array( 'delete', array( 14 ) ),
            array( 'createRole', array( new Role ) ),
            array( 'loadRole', array( 22 ) ),
            array( 'loadRoleByIdentifier', array( 'users' ) ),
            array( 'loadRoles', array() ),
            array( 'loadRolesByGroupId', array( 44 ) ),
            array( 'loadRoleAssignmentsByRoleId', array( 22 ) ),
            array( 'loadRoleAssignmentsByGroupId', array( 44, true ) ),
            array( 'updateRole', array( new RoleUpdateStruct ) ),
            array( 'deleteRole', array( 22 ) ),
            array( 'addPolicy', array( 22, new Policy ) ),
            array( 'updatePolicy', array( new Policy ) ),
            array( 'removePolicy', array( 22, 66 ) ),
            array( 'loadPoliciesByUserId', array( 14 ) ),
            array( 'assignRole', array( 44, 22, array( 42 ) ) ),
            array( 'unAssignRole', array( 44, 22 ) ),
        );
    }

    /**
     * @dataProvider providerForUnCachedMethods
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler
     */
    public function testUnCachedMethods( $method, array $arguments )
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $expects = $innerHandler
            ->expects( $this->once() )
            ->method( $method );

        if ( isset( $arguments[2] ) )
            $expects->with( $arguments[0], $arguments[1], $arguments[2] );
        else if ( isset( $arguments[1] ) )
            $expects->with( $arguments[0], $arguments[1] );
        else if ( isset( $arguments[0] ) )
            $expects->with( $arguments[0] );

        $expects->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->userHandler();
        call_user_func_array( array( $handler, $method ), $arguments );
    }
}
