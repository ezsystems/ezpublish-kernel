<?php
/**
 * File contains: eZ\Publish\Core\Repository\Permission\Tests\Service\Mock\PermissionCriterionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Permission\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Permission\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Permission\PermissionsCriterionHandler;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Repository\Permission\Values\User\Policy;

/**
 * Mock test case for PermissionCriterionHandler
 */
class PermissionCriterionHandlerTest extends BaseServiceMockTest
{
    protected $repositoryMock;

    /**
     * Test for the __construct() method.
     */
    public function testConstructor()
    {
        $repositoryMock = $this->getRepositoryMock();
        $handler = new PermissionsCriterionHandler( $repositoryMock );

        $this->assertAttributeSame(
            $repositoryMock,
            "repository",
            $handler
        );
    }

    public function providerForTestAddPermissionsCriterionWithBooleanPermission()
    {
        return array(
            array( true ),
            array( false )
        );
    }

    /**
     * Test for the addPermissionsCriterion() method.
     *
     * @dataProvider providerForTestAddPermissionsCriterionWithBooleanPermission
     */
    public function testAddPermissionsCriterionWithBooleanPermission( $permissionsCriterion )
    {
        $handler = $this->getPartlyMockedPermissionsCriterionHandler( array( "getPermissionsCriterion" ) );
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();

        $handler
            ->expects( $this->once() )
            ->method( "getPermissionsCriterion" )
            ->will( $this->returnValue( $permissionsCriterion ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $result = $handler->addPermissionsCriterion( $criterionMock );

        $this->assertSame( $permissionsCriterion, $result );
    }

    public function providerForTestAddPermissionsCriterion()
    {
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array(
                $criterionMock,
                new Criterion\LogicalAnd( array() ),
                new Criterion\LogicalAnd( array( $criterionMock ) )
            ),
            array(
                $criterionMock,
                $criterionMock,
                new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) )
            )
        );
    }

    /**
     * Test for the addPermissionsCriterion() method.
     *
     * @dataProvider providerForTestAddPermissionsCriterion
     */
    public function testAddPermissionsCriterion( $permissionsCriterionMock, $givenCriterion, $expectedCriterion )
    {
        $handler = $this->getPartlyMockedPermissionsCriterionHandler( array( "getPermissionsCriterion" ) );
        $handler
            ->expects( $this->once() )
            ->method( "getPermissionsCriterion" )
            ->will( $this->returnValue( $permissionsCriterionMock ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $result = $handler->addPermissionsCriterion( $givenCriterion );

        $this->assertTrue( $result );
        $this->assertEquals( $expectedCriterion, $givenCriterion );
    }

    public function providerForTestGetPermissionsCriterion()
    {
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
        $limitationMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation" );
        $limitationMock
            ->expects( $this->any() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "limitationIdentifier" ) );

        $policy1 = new Policy( array( 'limitations' => array( $limitationMock ) ) );
        $policy2 = new Policy( array( 'limitations' => array( $limitationMock, $limitationMock ) ) );

        return array(
            array(
                $criterionMock,
                1,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 )
                    ),
                ),
                $criterionMock,
            ),
            array(
                $criterionMock,
                2,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1, $policy1 ),
                    ),
                ),
                new Criterion\LogicalOr( array( $criterionMock, $criterionMock ) ),
            ),
            array(
                $criterionMock,
                1,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( new Policy( array( 'limitations' => "*" ) ), $policy1 ),
                    ),
                ),
                $criterionMock,
            ),
            array(
                $criterionMock,
                1,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( new Policy( array( 'limitations' => array() ) ), $policy1 ),
                    ),
                ),
                $criterionMock,
            ),
            array(
                $criterionMock,
                2,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy2 ),
                    ),
                ),
                new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
            ),
            array(
                $criterionMock,
                3,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1, $policy2 ),
                    ),
                ),
                new Criterion\LogicalOr(
                    array(
                        $criterionMock,
                        new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) )
                    )
                ),
            ),
            array(
                $criterionMock,
                2,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 ),
                    ),
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 )
                    ),
                ),
                new Criterion\LogicalOr( array( $criterionMock, $criterionMock ) ),
            ),
            array(
                $criterionMock,
                3,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 )
                    ),
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1, $policy1 ),
                    ),
                ),
                new Criterion\LogicalOr( array( $criterionMock, $criterionMock, $criterionMock ) ),
            ),
            array(
                $criterionMock,
                3,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy2 ),
                    ),
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 ),
                    ),
                ),
                new Criterion\LogicalOr(
                    array(
                        new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
                        $criterionMock
                    )
                ),
            ),
            array(
                $criterionMock,
                2,
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( $policy1 ),
                    ),
                ),
                new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
            ),
            array(
                $criterionMock,
                4,
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( $policy1 ),
                    ),
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( $policy1 ),
                    ),
                ),
                new Criterion\LogicalOr(
                    array(
                        new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
                        new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
                    )
                ),
            ),
            array(
                $criterionMock,
                1,
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( new Policy( array( 'limitations' => "*" ) ) ),
                    ),
                ),
                $criterionMock,
            ),
            array(
                $criterionMock,
                2,
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( new Policy( array( 'limitations' => "*" ) ) ),
                    ),
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( new Policy( array( 'limitations' => "*" ) ) ),
                    ),
                ),
                new Criterion\LogicalOr( array( $criterionMock, $criterionMock ) ),
            ),
        );
    }

    protected function getMockedRepository( $criterionMock, $limitationCount, $permissionSets )
    {
        $repositoryMock = $this->getRepositoryMock();
        $roleServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\RoleService" );
        $userMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );

        $limitationTypeMock
            ->expects( $this->any() )
            ->method( "getCriterion" )
            ->with(
                $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation" ),
                $this->equalTo( $userMock )
            )
            ->will( $this->returnValue( $criterionMock ) );

        $roleServiceMock
            ->expects( $this->exactly( $limitationCount ) )
            ->method( "getLimitationType" )
            ->with( $this->equalTo( "limitationIdentifier" ) )
            ->will( $this->returnValue( $limitationTypeMock ) );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( $permissionSets ) );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "getRoleService" )
            ->will( $this->returnValue( $roleServiceMock ) );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $userMock ) );

        return $repositoryMock;
    }

    /**
     * Test for the getPermissionsCriterion() method.
     *
     * @dataProvider providerForTestGetPermissionsCriterion
     */
    public function testGetPermissionsCriterion(
        $criterionMock,
        $limitationCount,
        $permissionSets,
        $expectedCriterion
    )
    {
        $repositoryMock = $this->getMockedRepository(
            $criterionMock,
            $limitationCount,
            $permissionSets
        );
        $handler = new PermissionsCriterionHandler( $repositoryMock );

        $permissionsCriterion = $handler->getPermissionsCriterion();

        $this->assertEquals( $expectedCriterion, $permissionsCriterion );
    }

    public function providerForTestGetPermissionsCriterionBooleanPermissionSets()
    {
        return array(
            array( true ),
            array( false ),
        );
    }

    /**
     * Test for the getPermissionsCriterion() method.
     *
     * @dataProvider providerForTestGetPermissionsCriterionBooleanPermissionSets
     */
    public function testGetPermissionsCriterionBooleanPermissionSets( $permissionSets )
    {
        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock
            ->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "testModule" ), $this->equalTo( "testFunction" ) )
            ->will( $this->returnValue( $permissionSets ) );
        $handler = new PermissionsCriterionHandler( $this->getRepositoryMock() );

        $permissionsCriterion = $handler->getPermissionsCriterion( "testModule", "testFunction" );

        $this->assertEquals( $permissionSets, $permissionsCriterion );
    }

    /**
     * Returns the PermissionsCriterionHandler to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()}
     *
     * @param string[] $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\Permission\PermissionsCriterionHandler
     */
    protected function getPartlyMockedPermissionsCriterionHandler( array $methods = array() )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Permission\\PermissionsCriterionHandler",
            $methods,
            array( $this->getRepositoryMock() )
        );
    }
}
