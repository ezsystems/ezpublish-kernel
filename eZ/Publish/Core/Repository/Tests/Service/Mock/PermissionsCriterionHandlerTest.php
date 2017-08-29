<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\PermissionCriterionHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CriterionInterface;
use eZ\Publish\Core\Repository\Values\User\Policy;

/**
 * Mock test case for PermissionCriterionHandler.
 */
class PermissionsCriterionHandlerTest extends BaseServiceMockTest
{
    /**
     * Test for the __construct() method.
     */
    public function testConstructor()
    {
        $permissionResolverMock = $this->getPermissionResolverMock();
        $limitationServiceMock = $this->getLimitationServiceMock();
        $handler = $this->getPermissionsCriterionHandlerMock();

        $this->assertAttributeSame(
            $permissionResolverMock,
            'permissionResolver',
            $handler
        );
        $this->assertAttributeSame(
            $limitationServiceMock,
            'limitationService',
            $handler
        );
    }

    public function providerForTestAddPermissionsCriterionWithBooleanPermission()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * Test for the addPermissionsCriterion() method.
     *
     * @dataProvider providerForTestAddPermissionsCriterionWithBooleanPermission
     */
    public function testAddPermissionsCriterionWithBooleanPermission($permissionsCriterion)
    {
        $handler = $this->getPermissionsCriterionHandlerMock(array('getPermissionsCriterion'));
        $criterionMock = $this
            ->getMockBuilder(CriterionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler
            ->expects($this->once())
            ->method('getPermissionsCriterion')
            ->will($this->returnValue($permissionsCriterion));

        /** @var CriterionInterface $criterionMock */
        $result = $handler->addPermissionsCriterion($criterionMock);

        $this->assertSame($permissionsCriterion, $result);
    }

    public function providerForTestAddPermissionsCriterion()
    {
        $criterionMock = $this
            ->getMockBuilder(CriterionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array(
                $criterionMock,
                new Criterion\LogicalOperator\LogicalAnd(array()),
                new Criterion\LogicalOperator\LogicalAnd(array($criterionMock)),
            ),
            array(
                $criterionMock,
                $criterionMock,
                new Criterion\LogicalOperator\LogicalAnd(array($criterionMock, $criterionMock)),
            ),
        );
    }

    /**
     * Test for the addPermissionsCriterion() method.
     *
     * @dataProvider providerForTestAddPermissionsCriterion
     */
    public function testAddPermissionsCriterion($permissionsCriterionMock, $givenCriterion, $expectedCriterion)
    {
        $handler = $this->getPermissionsCriterionHandlerMock(array('getPermissionsCriterion'));
        $handler
            ->expects($this->once())
            ->method('getPermissionsCriterion')
            ->will($this->returnValue($permissionsCriterionMock));

        /** @var CriterionInterface $criterionMock */
        $result = $handler->addPermissionsCriterion($givenCriterion);

        $this->assertTrue($result);
        $this->assertEquals($expectedCriterion, $givenCriterion);
    }

    public function providerForTestGetPermissionsCriterion()
    {
        $criterionMock = $this
            ->getMockBuilder(CriterionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $limitationMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation')
            ->getMockForAbstractClass();
        $limitationMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('limitationIdentifier'));

        $policy1 = new Policy(array('limitations' => array($limitationMock)));
        $policy2 = new Policy(array('limitations' => array($limitationMock, $limitationMock)));

        return array(
            array(
                $criterionMock,
                1,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array($policy1),
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
                        'policies' => array($policy1, $policy1),
                    ),
                ),
                new Criterion\LogicalOperator\LogicalOr(array($criterionMock, $criterionMock)),
            ),
            array(
                $criterionMock,
                0,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array(new Policy(array('limitations' => '*')), $policy1),
                    ),
                ),
                false,
            ),
            array(
                $criterionMock,
                0,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array(new Policy(array('limitations' => array())), $policy1),
                    ),
                ),
                false,
            ),
            array(
                $criterionMock,
                2,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array($policy2),
                    ),
                ),
                new Criterion\LogicalOperator\LogicalAnd(array($criterionMock, $criterionMock)),
            ),
            array(
                $criterionMock,
                3,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array($policy1, $policy2),
                    ),
                ),
                new Criterion\LogicalOperator\LogicalOr(
                    array(
                        $criterionMock,
                        new Criterion\LogicalOperator\LogicalAnd(array($criterionMock, $criterionMock)),
                    )
                ),
            ),
            array(
                $criterionMock,
                2,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array($policy1),
                    ),
                    array(
                        'limitation' => null,
                        'policies' => array($policy1),
                    ),
                ),
                new Criterion\LogicalOperator\LogicalOr(array($criterionMock, $criterionMock)),
            ),
            array(
                $criterionMock,
                3,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array($policy1),
                    ),
                    array(
                        'limitation' => null,
                        'policies' => array($policy1, $policy1),
                    ),
                ),
                new Criterion\LogicalOperator\LogicalOr(array($criterionMock, $criterionMock, $criterionMock)),
            ),
            array(
                $criterionMock,
                3,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array($policy2),
                    ),
                    array(
                        'limitation' => null,
                        'policies' => array($policy1),
                    ),
                ),
                new Criterion\LogicalOperator\LogicalOr(
                    array(
                        new Criterion\LogicalOperator\LogicalAnd(array($criterionMock, $criterionMock)),
                        $criterionMock,
                    )
                ),
            ),
            array(
                $criterionMock,
                2,
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array($policy1),
                    ),
                ),
                new Criterion\LogicalOperator\LogicalAnd(array($criterionMock, $criterionMock)),
            ),
            array(
                $criterionMock,
                4,
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array($policy1),
                    ),
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array($policy1),
                    ),
                ),
                new Criterion\LogicalOperator\LogicalOr(
                    array(
                        new Criterion\LogicalOperator\LogicalAnd(array($criterionMock, $criterionMock)),
                        new Criterion\LogicalOperator\LogicalAnd(array($criterionMock, $criterionMock)),
                    )
                ),
            ),
            array(
                $criterionMock,
                1,
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array(new Policy(array('limitations' => '*'))),
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
                        'policies' => array(new Policy(array('limitations' => '*'))),
                    ),
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array(new Policy(array('limitations' => '*'))),
                    ),
                ),
                new Criterion\LogicalOperator\LogicalOr(array($criterionMock, $criterionMock)),
            ),
        );
    }

    protected function mockServices($criterionMock, $limitationCount, $permissionSets)
    {
        $userMock = $this->getMock('eZ\\Publish\\API\\Repository\\Values\\User\\User');
        $limitationTypeMock = $this->getMock('eZ\\Publish\\SPI\\Limitation\\Type');
        $limitationServiceMock = $this->getLimitationServiceMock(['getLimitationType']);
        $permissionResolverMock = $this->getPermissionResolverMock(
            [
                'hasAccess',
                'getCurrentUserReference',
            ]
        );

        $limitationTypeMock
            ->expects($this->any())
            ->method('getCriterion')
            ->with(
                $this->isInstanceOf('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation'),
                $this->equalTo($userMock)
            )
            ->will($this->returnValue($criterionMock));

        $limitationServiceMock
            ->expects($this->exactly($limitationCount))
            ->method('getLimitationType')
            ->with($this->equalTo('limitationIdentifier'))
            ->will($this->returnValue($limitationTypeMock));

        $permissionResolverMock
            ->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('content'), $this->equalTo('read'))
            ->will($this->returnValue($permissionSets));

        $permissionResolverMock
            ->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue($userMock));
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
    ) {
        $this->mockServices($criterionMock, $limitationCount, $permissionSets);
        $handler = $this->getPermissionsCriterionHandlerMock(null);

        $permissionsCriterion = $handler->getPermissionsCriterion();

        $this->assertEquals($expectedCriterion, $permissionsCriterion);
    }

    public function providerForTestGetPermissionsCriterionBooleanPermissionSets()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * Test for the getPermissionsCriterion() method.
     *
     * @dataProvider providerForTestGetPermissionsCriterionBooleanPermissionSets
     */
    public function testGetPermissionsCriterionBooleanPermissionSets($permissionSets)
    {
        $permissionResolverMock = $this->getPermissionResolverMock(['hasAccess']);
        $permissionResolverMock
            ->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('testModule'), $this->equalTo('testFunction'))
            ->will($this->returnValue($permissionSets));
        $handler = $this->getPermissionsCriterionHandlerMock(null);

        $permissionsCriterion = $handler->getPermissionsCriterion('testModule', 'testFunction');

        $this->assertEquals($permissionSets, $permissionsCriterion);
    }

    /**
     * Returns the PermissionsCriterionHandler to test with $methods mocked.
     *
     * @param string[]|null $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\PermissionsCriterionHandler
     */
    protected function getPermissionsCriterionHandlerMock($methods = [])
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\Core\\Repository\\PermissionsCriterionHandler')
            ->setMethods($methods)
            ->setConstructorArgs(
                [
                    $this->getPermissionResolverMock(),
                    $this->getLimitationServiceMock(),
                ]
            )
            ->getMock();
    }

    protected $permissionResolverMock;

    protected function getPermissionResolverMock($methods = [])
    {
        if ($this->permissionResolverMock === null) {
            $this->permissionResolverMock = $this
                ->getMockBuilder('eZ\Publish\API\Repository\PermissionResolver')
                ->setMethods($methods)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
        }

        return $this->permissionResolverMock;
    }

    protected $limitationServiceMock;

    protected function getLimitationServiceMock($methods = [])
    {
        if ($this->limitationServiceMock === null) {
            $this->limitationServiceMock = $this
                ->getMockBuilder('eZ\Publish\Core\Repository\Helper\LimitationService')
                ->setMethods($methods)
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->limitationServiceMock;
    }
}
