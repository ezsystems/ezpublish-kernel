<?php
/**
 * File containing the RepositoryTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\SignalSlot\Repository;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\User;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Location;
use PHPUnit_Framework_TestCase;

class RepositoryTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider serviceMethods
     */
    public function testServiceMethod( $method, $innerClass, $class )
    {
        $innerRepository = $this->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\DomainLogic\\Repository' )
            ->disableOriginalConstructor()
            ->getMock();
        $innerRepository->expects( $this->once() )
            ->method( $method )
            ->will( $this->returnValue( $this->getMock( $innerClass ) ) );
        $repository = new Repository(
            $innerRepository,
            $this->getMock( 'eZ\\Publish\\Core\\SignalSlot\\SignalDispatcher' )
        );

        $service = $repository->{$method}();
        $this->assertInstanceOf( $class, $service );
        $service2 = $repository->{$method}();
        $this->assertTrue( $service === $service2 );
    }

    public function serviceMethods()
    {
        return array(
            array(
                'getContentService',
                '\eZ\Publish\API\Repository\ContentService',
                '\eZ\Publish\Core\SignalSlot\ContentService'
            ),
            array(
                'getContentLanguageService',
                '\eZ\Publish\API\Repository\LanguageService',
                '\eZ\Publish\Core\SignalSlot\LanguageService'
            ),
            array(
                'getContentTypeService',
                '\eZ\Publish\API\Repository\ContentTypeService',
                '\eZ\Publish\Core\SignalSlot\ContentTypeService'
            ),
            array(
                'getLocationService',
                '\eZ\Publish\API\Repository\LocationService',
                '\eZ\Publish\Core\SignalSlot\LocationService'
            ),
            array(
                'getTrashService',
                '\eZ\Publish\API\Repository\TrashService',
                '\eZ\Publish\Core\SignalSlot\TrashService'
            ),
            array(
                'getSectionService',
                '\eZ\Publish\API\Repository\SectionService',
                '\eZ\Publish\Core\SignalSlot\SectionService'
            ),
            array(
                'getUserService',
                '\eZ\Publish\API\Repository\UserService',
                '\eZ\Publish\Core\SignalSlot\UserService'
            ),
            array(
                'getURLAliasService',
                '\eZ\Publish\API\Repository\URLAliasService',
                '\eZ\Publish\Core\SignalSlot\URLAliasService'
            ),
            array(
                'getURLWildcardService',
                '\eZ\Publish\API\Repository\URLWildcardService',
                '\eZ\Publish\Core\SignalSlot\URLWildcardService'
            ),
            array(
                'getObjectStateService',
                '\eZ\Publish\API\Repository\ObjectStateService',
                '\eZ\Publish\Core\SignalSlot\ObjectStateService'
            ),
            array(
                'getRoleService',
                '\eZ\Publish\API\Repository\RoleService',
                '\eZ\Publish\Core\SignalSlot\RoleService'
            ),
            array(
                'getSearchService',
                '\eZ\Publish\API\Repository\SearchService',
                '\eZ\Publish\Core\SignalSlot\SearchService'
            ),
            array(
                'getFieldTypeService',
                '\eZ\Publish\API\Repository\FieldTypeService',
                '\eZ\Publish\Core\SignalSlot\FieldTypeService'
            ),
        );
    }

    /**
     * @dataProvider aggregatedMethods
     */
    public function testAggregation( $method, $parameters, $return )
    {
        $innerRepository = $this->getMockBuilder( 'eZ\\Publish\Core\\Repository\\DomainLogic\\Repository' )
            ->disableOriginalConstructor()
            ->getMock();
        $innerRepository->expects( $this->once() )
            ->method( $method )
            ->will(
                $this->returnValueMap(
                    array( array_merge( $parameters, array( $return ) ) )
                )
            );
        $repository = new Repository(
            $innerRepository,
            $this->getMock( 'eZ\\Publish\\Core\\SignalSlot\\SignalDispatcher' )
        );

        $result = call_user_func_array(
            array( $repository, $method ), $parameters
        );
        $this->assertTrue( $result === $return );
    }

    public function aggregatedMethods()
    {
        $ts = 374390100;
        $dt = new \DateTime();
        $dt->setTimestamp( $ts );
        return array(
            array(
                'getCurrentUser',
                array(),
                new User
            ),
            array(
                'setCurrentUser',
                array( new User ),
                null
            ),
            array(
                'hasAccess',
                array( 'module', 'function', new User ),
                array( 'limitations' )
            ),
            array(
                'canUser',
                array( 'module', 'function', new User, new Location ),
                false
            ),
            array(
                'beginTransaction',
                array(),
                true
            ),
            array(
                'commit',
                array(),
                true
            ),
            array(
                'rollback',
                array(),
                true
            ),
            array(
                'createDateTime',
                array( $ts ),
                $dt
            ),
        );
    }
}
