<?php

/**
 * File containing the RepositoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\SignalSlot\Repository;
use eZ\Publish\Core\Repository\Repository as InnerRepository;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\SignalSlot\ContentService;
use eZ\Publish\Core\SignalSlot\ContentTypeService;
use eZ\Publish\Core\SignalSlot\FieldTypeService;
use eZ\Publish\Core\SignalSlot\RoleService;
use eZ\Publish\Core\SignalSlot\ObjectStateService;
use eZ\Publish\Core\SignalSlot\URLWildcardService;
use eZ\Publish\Core\SignalSlot\URLAliasService;
use eZ\Publish\Core\SignalSlot\UserService;
use eZ\Publish\Core\SignalSlot\SearchService;
use eZ\Publish\Core\SignalSlot\SectionService;
use eZ\Publish\Core\SignalSlot\TrashService;
use eZ\Publish\Core\SignalSlot\LocationService;
use eZ\Publish\Core\SignalSlot\LanguageService;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    /**
     * @dataProvider serviceMethods
     */
    public function testServiceMethod($method, $class)
    {
        $innerRepositoryMock = $this->getMockBuilder(InnerRepository::class)->disableOriginalConstructor()->getMock();
        $signalDispatcherMock = $this->createMock(SignalDispatcher::class);

        $contentServiceMock = $this->getMockBuilder(ContentService::class)->disableOriginalConstructor()->getMock();
        $contentTypeServiceMock = $this->getMockBuilder(ContentTypeService::class)->disableOriginalConstructor()->getMock();
        $fieldTypeServiceMock = $this->getMockBuilder(FieldTypeService::class)->disableOriginalConstructor()->getMock();
        $roleServiceMock = $this->getMockBuilder(RoleService::class)->disableOriginalConstructor()->getMock();
        $objectStateServiceMock = $this->getMockBuilder(ObjectStateService::class)->disableOriginalConstructor()->getMock();
        $urlWildcardServiceMock = $this->getMockBuilder(URLWildcardService::class)->disableOriginalConstructor()->getMock();
        $urlAliasServiceMock = $this->getMockBuilder(URLAliasService::class)->disableOriginalConstructor()->getMock();
        $userServiceMock = $this->getMockBuilder(UserService::class)->disableOriginalConstructor()->getMock();
        $searchServiceMock = $this->getMockBuilder(SearchService::class)->disableOriginalConstructor()->getMock();
        $sectionServiceMock = $this->getMockBuilder(SectionService::class)->disableOriginalConstructor()->getMock();
        $trashServiceMock = $this->getMockBuilder(TrashService::class)->disableOriginalConstructor()->getMock();
        $locationServiceMock = $this->getMockBuilder(LocationService::class)->disableOriginalConstructor()->getMock();
        $languageServiceMock = $this->getMockBuilder(LanguageService::class)->disableOriginalConstructor()->getMock();

        $repository = new Repository(
            $innerRepositoryMock,
            $signalDispatcherMock,
            $contentServiceMock,
            $contentTypeServiceMock,
            $fieldTypeServiceMock,
            $roleServiceMock,
            $objectStateServiceMock,
            $urlWildcardServiceMock,
            $urlAliasServiceMock,
            $userServiceMock,
            $searchServiceMock,
            $sectionServiceMock,
            $trashServiceMock,
            $locationServiceMock,
            $languageServiceMock
        );

        $service = $repository->{$method}();
        $this->assertInstanceOf($class, $service);
        $service2 = $repository->{$method}();
        $this->assertTrue($service === $service2);
    }

    public function serviceMethods()
    {
        return [
            ['getContentService', ContentService::class],
            ['getContentLanguageService', LanguageService::class],
            ['getContentTypeService', ContentTypeService::class],
            ['getLocationService', LocationService::class],
            ['getTrashService', TrashService::class],
            ['getSectionService', SectionService::class],
            ['getUserService', UserService::class],
            ['getURLAliasService', URLAliasService::class],
            ['getURLWildcardService', URLWildcardService::class],
            ['getObjectStateService', ObjectStateService::class],
            ['getRoleService', RoleService::class],
            ['getSearchService', SearchService::class],
            ['getFieldTypeService', FieldTypeService::class],
        ];
    }

    /**
     * @dataProvider aggregatedMethods
     */
    public function testAggregation($method, $parameters, $return)
    {
        $innerRepositoryMock = $this->getMockBuilder(InnerRepository::class)->disableOriginalConstructor()->getMock();
        $signalDispatcherMock = $this->createMock(SignalDispatcher::class);

        $contentServiceMock = $this->getMockBuilder(ContentService::class)->disableOriginalConstructor()->getMock();
        $contentTypeServiceMock = $this->getMockBuilder(ContentTypeService::class)->disableOriginalConstructor()->getMock();
        $fieldTypeServiceMock = $this->getMockBuilder(FieldTypeService::class)->disableOriginalConstructor()->getMock();
        $roleServiceMock = $this->getMockBuilder(RoleService::class)->disableOriginalConstructor()->getMock();
        $objectStateServiceMock = $this->getMockBuilder(ObjectStateService::class)->disableOriginalConstructor()->getMock();
        $urlWildcardServiceMock = $this->getMockBuilder(URLWildcardService::class)->disableOriginalConstructor()->getMock();
        $urlAliasServiceMock = $this->getMockBuilder(URLAliasService::class)->disableOriginalConstructor()->getMock();
        $userServiceMock = $this->getMockBuilder(UserService::class)->disableOriginalConstructor()->getMock();
        $searchServiceMock = $this->getMockBuilder(SearchService::class)->disableOriginalConstructor()->getMock();
        $sectionServiceMock = $this->getMockBuilder(SectionService::class)->disableOriginalConstructor()->getMock();
        $trashServiceMock = $this->getMockBuilder(TrashService::class)->disableOriginalConstructor()->getMock();
        $locationServiceMock = $this->getMockBuilder(LocationService::class)->disableOriginalConstructor()->getMock();
        $languageServiceMock = $this->getMockBuilder(LanguageService::class)->disableOriginalConstructor()->getMock();

        $innerRepositoryMock->expects($this->once())
            ->method($method)
            ->will(
                $this->returnValueMap([
                    array_merge($parameters, [$return]),
                ])
            );

        $repository = new Repository(
            $innerRepositoryMock,
            $signalDispatcherMock,
            $contentServiceMock,
            $contentTypeServiceMock,
            $fieldTypeServiceMock,
            $roleServiceMock,
            $objectStateServiceMock,
            $urlWildcardServiceMock,
            $urlAliasServiceMock,
            $userServiceMock,
            $searchServiceMock,
            $sectionServiceMock,
            $trashServiceMock,
            $locationServiceMock,
            $languageServiceMock
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
        ];
    }
}
