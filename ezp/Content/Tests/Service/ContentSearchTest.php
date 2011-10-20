<?php
/**
 * File contains: ezp\Content\Tests\Service\ContentTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Location,
    ezp\Content\Search\Result,
    ezp\Content\Query,
    ezp\Content\Query\Builder as QueryBuilder,
    ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Base\Exception\NotFound,
    ezp\Persistence\Content as ContentValue,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Persistence\Content\Search\Result as ResultValue,
    ezp\User\Proxy as ProxyUser,
    ReflectionObject;

/**
 * Test case for Content service
 */
class ContentSearchTest extends BaseServiceTest
{
    /**
     * @var \ezp\Content\Service
     */
    protected $service;

    /**
     * @var \ezp\Persistence\Content\Search\Handler
     */
    protected $searchHandler;

    /**
     * @var \ezp\Content[]
     */
    protected $expectedContent;

    /**
     * @var \ezp\Persistence\Content[]
     */
    protected $expectedContentVo;

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getContentService();
        $this->repository->setUser( new ProxyUser( 14, $this->repository->getUserService() ) );// "Login" admin

        /*
         * Mocking search handler and forcing the mock into the repository handler
         * 1. Get protected $handler property (repository handler) from repository
         * 2. Get mock object for InMemory search handler
         * 3. Get protected $serviceHandlers property from repo handler
         *    and inject search handler mock in it
         *
         * This way, content service will only use the mock object as search handler
         */
        $refRepository = new ReflectionObject( $this->repository );
        $refHandlerProp = $refRepository->getProperty( 'persistenceHandler' );
        $refHandlerProp->setAccessible( true );
        $persistenceHandler = $refHandlerProp->getValue( $this->repository );
        $refHandler = new ReflectionObject( $persistenceHandler );
        $refBackend = $refHandler->getProperty( 'backend' );
        $refBackend->setAccessible( true );
        $this->searchHandler = $this->getMockBuilder(
            'ezp\\Persistence\\Storage\\InMemory\\SearchHandler'
        )->setConstructorArgs(
            array(
                $persistenceHandler,
                $refBackend->getValue( $persistenceHandler )
            )
        )->getMock();
        $refServiceHandlersProp = $refHandler->getProperty( 'serviceHandlers' );
        $refServiceHandlersProp->setAccessible( true );
        $refServiceHandlersProp->setValue(
            $persistenceHandler,
            array(
                'ezp\\Persistence\\Storage\\InMemory\\SearchHandler' => $this->searchHandler
            )
        );

        // Build expected results
        $type = $this->repository->getContentTypeService()->load( 1 );
        $section = $this->repository->getSectionService()->load( 1 );
        $this->expectedContent = array();
        $this->expectedContentVo = array();
        for ( $i = 0; $i < 10; ++$i )
        {
            $content = new ConcreteContent( $type, new ProxyUser( 14, $this->repository->getUserService() ) );
            $content->name = array( "eng-GB" => "foo$i" );
            $content->setSection( $section );
            $fields = $content->getCurrentVersion()->getFields();
            $fields['name'] = "bar$i";
            $content = $this->service->create( $content );
            $this->expectedContent[] = $content;
            $this->expectedContentVo[] = $content->getState( 'properties' );
        }
    }

    /**
     * Tests find operation on content service
     *
     * @group contentService
     * @covers \ezp\Content\Service::find
     */
    public function testFind()
    {
        $qb = new QueryBuilder;
        $qb->addCriteria(
            $qb->fullText->like( 'foo*' )
        )->addSortClause(
            $qb->sort->field( 'folder', 'name', Query::SORT_ASC ),
            $qb->sort->dateCreated( Query::SORT_DESC )
        );

        $this->searchHandler
            ->expects( $this->once() )
            ->method( 'find' )
            ->will(
                $this->returnValue(
                    new ResultValue(
                        array(
                            'content' => $this->expectedContentVo,
                            'count' => count( $this->expectedContentVo )
                        )
                    )
                )
            );

        $result = $this->service->find( $qb->getQuery() );
        self::assertInstanceOf( 'ezp\\Content\\Search\\Result', $result );
        self::assertEquals( 10, count( $result ) );
        foreach ( $result as $key => $content )
        {
            $originalVo = $this->expectedContent[$key]->getState( 'properties' );
            foreach ( $content->getState( 'properties' ) as $prop => $value )
            {
                self::assertEquals( $originalVo->$prop, $value );
            }
        }
    }

    /**
     * Tests findSingle operation on content service
     *
     * @group contentService
     * @covers \ezp\Content\Service::findSingle
     */
    public function testFindSingle()
    {
        $qb = new QueryBuilder;
        $qb->addCriteria( $qb->fullText->eq( 'foo0' ) );

        $this->searchHandler
            ->expects( $this->once() )
            ->method( 'findSingle' )
            ->will( $this->returnValue( $this->expectedContentVo[0] ) );

        $content = $this->service->findSingle( $qb->getQuery() );
        self::assertInstanceOf( 'ezp\\Content', $content );
        foreach ( $content->getState( 'properties' ) as $prop => $value )
        {
            self::assertEquals( $this->expectedContentVo[0]->$prop, $value );
        }
    }
}
