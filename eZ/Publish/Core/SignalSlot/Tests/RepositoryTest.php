<?php

/**
 * File containing the RepositoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\SignalSlot\Repository;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    /**
     * @dataProvider serviceMethods
     */
    public function testServiceMethod($method, $innerClass, $class)
    {
        $innerRepository = $this->getMockBuilder('eZ\\Publish\Core\\Repository\\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        $innerRepository->expects($this->once())
            ->method($method)
            ->will($this->returnValue($this->getMock($innerClass)));
        $repository = new Repository(
            $innerRepository,
            $this->getMock('eZ\\Publish\\Core\\SignalSlot\\SignalDispatcher')
        );

        $service = $repository->{$method}();
        $this->assertInstanceOf($class, $service);
        $service2 = $repository->{$method}();
        $this->assertTrue($service === $service2);
    }

    public function serviceMethods()
    {
        return [
            [
                'getContentService',
                '\eZ\Publish\API\Repository\ContentService',
                '\eZ\Publish\Core\SignalSlot\ContentService',
            ],
            [
                'getContentLanguageService',
                '\eZ\Publish\API\Repository\LanguageService',
                '\eZ\Publish\Core\SignalSlot\LanguageService',
            ],
            [
                'getContentTypeService',
                '\eZ\Publish\API\Repository\ContentTypeService',
                '\eZ\Publish\Core\SignalSlot\ContentTypeService',
            ],
            [
                'getLocationService',
                '\eZ\Publish\API\Repository\LocationService',
                '\eZ\Publish\Core\SignalSlot\LocationService',
            ],
            [
                'getTrashService',
                '\eZ\Publish\API\Repository\TrashService',
                '\eZ\Publish\Core\SignalSlot\TrashService',
            ],
            [
                'getSectionService',
                '\eZ\Publish\API\Repository\SectionService',
                '\eZ\Publish\Core\SignalSlot\SectionService',
            ],
            [
                'getUserService',
                '\eZ\Publish\API\Repository\UserService',
                '\eZ\Publish\Core\SignalSlot\UserService',
            ],
            [
                'getURLAliasService',
                '\eZ\Publish\API\Repository\URLAliasService',
                '\eZ\Publish\Core\SignalSlot\URLAliasService',
            ],
            [
                'getURLWildcardService',
                '\eZ\Publish\API\Repository\URLWildcardService',
                '\eZ\Publish\Core\SignalSlot\URLWildcardService',
            ],
            [
                'getObjectStateService',
                '\eZ\Publish\API\Repository\ObjectStateService',
                '\eZ\Publish\Core\SignalSlot\ObjectStateService',
            ],
            [
                'getRoleService',
                '\eZ\Publish\API\Repository\RoleService',
                '\eZ\Publish\Core\SignalSlot\RoleService',
            ],
            [
                'getSearchService',
                '\eZ\Publish\API\Repository\SearchService',
                '\eZ\Publish\Core\SignalSlot\SearchService',
            ],
            [
                'getFieldTypeService',
                '\eZ\Publish\API\Repository\FieldTypeService',
                '\eZ\Publish\Core\SignalSlot\FieldTypeService',
            ],
        ];
    }

    /**
     * @dataProvider aggregatedMethods
     */
    public function testAggregation($method, $parameters, $return)
    {
        $innerRepository = $this->getMockBuilder('eZ\\Publish\Core\\Repository\\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        $innerRepository->expects($this->once())
            ->method($method)
            ->will(
                $this->returnValueMap(
                    [array_merge($parameters, [$return])]
                )
            );
        $repository = new Repository(
            $innerRepository,
            $this->getMock('eZ\\Publish\\Core\\SignalSlot\\SignalDispatcher')
        );

        $result = call_user_func_array([$repository, $method], $parameters);
        $this->assertTrue($result === $return);
    }

    public function aggregatedMethods()
    {
        $ts = 374390100;
        $dt = new \DateTime();
        $dt->setTimestamp($ts);

        return [
            [
                'getCurrentUser',
                [],
                new User(),
            ],
            [
                'setCurrentUser',
                [new User()],
                null,
            ],
            [
                'hasAccess',
                ['module', 'function', new User()],
                ['limitations'],
            ],
            [
                'canUser',
                ['module', 'function', new User(), new Location()],
                false,
            ],
            [
                'beginTransaction',
                [],
                true,
            ],
            [
                'commit',
                [],
                true,
            ],
            [
                'rollback',
                [],
                true,
            ],
            [
                'createDateTime',
                [$ts],
                $dt,
            ],
        ];
    }
}
