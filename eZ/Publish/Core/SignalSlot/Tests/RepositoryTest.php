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
use PHPUnit_Framework_TestCase;

class RepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider serviceMethods
     */
    public function testServiceMethod($method, $class)
    {
        $innerRepositoryMock = $this->getMockBuilder('eZ\\Publish\Core\\Repository\\Repository')->disableOriginalConstructor()->getMock();
        $signalDispatcherMock = $this->getMock('eZ\\Publish\\Core\\SignalSlot\\SignalDispatcher');

        $contentServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\ContentService')->disableOriginalConstructor()->getMock();
        $contentTypeServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\ContentTypeService')->disableOriginalConstructor()->getMock();
        $fieldTypeServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\FieldTypeService')->disableOriginalConstructor()->getMock();
        $roleServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\RoleService')->disableOriginalConstructor()->getMock();
        $objectStateServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\ObjectStateService')->disableOriginalConstructor()->getMock();
        $urlWildcardServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\URLWildcardService')->disableOriginalConstructor()->getMock();
        $urlAliasServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\URLAliasService')->disableOriginalConstructor()->getMock();
        $userServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\UserService')->disableOriginalConstructor()->getMock();
        $searchServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\SearchService')->disableOriginalConstructor()->getMock();
        $sectionServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\SectionService')->disableOriginalConstructor()->getMock();
        $trashServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\TrashService')->disableOriginalConstructor()->getMock();
        $locationServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\LocationService')->disableOriginalConstructor()->getMock();
        $languageServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\LanguageService')->disableOriginalConstructor()->getMock();

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
        return array(
            array(
                'getContentService',
                '\eZ\Publish\Core\SignalSlot\ContentService',
            ),
            array(
                'getContentLanguageService',
                '\eZ\Publish\Core\SignalSlot\LanguageService',
            ),
            array(
                'getContentTypeService',
                '\eZ\Publish\Core\SignalSlot\ContentTypeService',
            ),
            array(
                'getLocationService',
                '\eZ\Publish\Core\SignalSlot\LocationService',
            ),
            array(
                'getTrashService',
                '\eZ\Publish\Core\SignalSlot\TrashService',
            ),
            array(
                'getSectionService',
                '\eZ\Publish\Core\SignalSlot\SectionService',
            ),
            array(
                'getUserService',
                '\eZ\Publish\Core\SignalSlot\UserService',
            ),
            array(
                'getURLAliasService',
                '\eZ\Publish\Core\SignalSlot\URLAliasService',
            ),
            array(
                'getURLWildcardService',
                '\eZ\Publish\Core\SignalSlot\URLWildcardService',
            ),
            array(
                'getObjectStateService',
                '\eZ\Publish\Core\SignalSlot\ObjectStateService',
            ),
            array(
                'getRoleService',
                '\eZ\Publish\Core\SignalSlot\RoleService',
            ),
            array(
                'getSearchService',
                '\eZ\Publish\Core\SignalSlot\SearchService',
            ),
            array(
                'getFieldTypeService',
                '\eZ\Publish\Core\SignalSlot\FieldTypeService',
            ),
        );
    }

    /**
     * @dataProvider aggregatedMethods
     */
    public function testAggregation($method, $parameters, $return)
    {
        $innerRepositoryMock = $this->getMockBuilder('eZ\\Publish\Core\\Repository\\Repository')->disableOriginalConstructor()->getMock();
        $signalDispatcherMock = $this->getMock('eZ\\Publish\\Core\\SignalSlot\\SignalDispatcher');

        $contentServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\ContentService')->disableOriginalConstructor()->getMock();
        $contentTypeServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\ContentTypeService')->disableOriginalConstructor()->getMock();
        $fieldTypeServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\FieldTypeService')->disableOriginalConstructor()->getMock();
        $roleServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\RoleService')->disableOriginalConstructor()->getMock();
        $objectStateServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\ObjectStateService')->disableOriginalConstructor()->getMock();
        $urlWildcardServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\URLWildcardService')->disableOriginalConstructor()->getMock();
        $urlAliasServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\URLAliasService')->disableOriginalConstructor()->getMock();
        $userServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\UserService')->disableOriginalConstructor()->getMock();
        $searchServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\SearchService')->disableOriginalConstructor()->getMock();
        $sectionServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\SectionService')->disableOriginalConstructor()->getMock();
        $trashServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\TrashService')->disableOriginalConstructor()->getMock();
        $locationServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\LocationService')->disableOriginalConstructor()->getMock();
        $languageServiceMock = $this->getMockBuilder('eZ\\Publish\\Core\\SignalSlot\\LanguageService')->disableOriginalConstructor()->getMock();

        $innerRepositoryMock->expects($this->once())
            ->method($method)
            ->will(
                $this->returnValueMap(
                    array(array_merge($parameters, array($return)))
                )
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

        $result = call_user_func_array(array($repository, $method), $parameters);
        $this->assertTrue($result === $return);
    }

    public function aggregatedMethods()
    {
        $ts = 374390100;
        $dt = new \DateTime();
        $dt->setTimestamp($ts);

        return array(
            array(
                'getCurrentUser',
                array(),
                new User(),
            ),
            array(
                'setCurrentUser',
                array(new User()),
                null,
            ),
            array(
                'hasAccess',
                array('module', 'function', new User()),
                array('limitations'),
            ),
            array(
                'canUser',
                array('module', 'function', new User(), new Location()),
                false,
            ),
            array(
                'beginTransaction',
                array(),
                true,
            ),
            array(
                'commit',
                array(),
                true,
            ),
            array(
                'rollback',
                array(),
                true,
            ),
            array(
                'createDateTime',
                array($ts),
                $dt,
            ),
        );
    }
}
