<?php
/**
 * File contains: ezp\Content\Tests\LocationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User\Tests;
use ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\User;

/**
 * Test case for Location class
 *
 */
class ServiceTest extends BaseServiceTest
{
    /**
     * Test service function for creating users
     *
     * @covers \ezp\User\Service::create
     */
    public function testCreate()
    {
        $service = $this->repository->getUserService();
        $do = new User();
        $do->getState( 'properties' )->id = 1;
        $do->login = $do->password ='test';
        $do->email = 'test@ez.no';
        $do->getState( 'properties' )->hashAlgorithm = 2;
        $do = $service->create( $do );
        self::assertEquals( $do->id, 1 );
        self::assertEquals( 'test', $do->login );
        self::assertEquals( 'test@ez.no', $do->email );
    }

    /**
     * Test service function for creating users
     *
     * @covers \ezp\User\Service::create
     * @expectedException \ezp\Base\Exception\Logic
     */
    public function testCreateExistingId()
    {
        $service = $this->repository->getUserService();
        $do = new User();
        $do->getState( 'properties' )->id = 14;
        $do->login = $do->password ='test';
        $do->email = 'test@ez.no';
        $do->getState( 'properties' )->hashAlgorithm = 2;
        $do = $service->create( $do );
    }

    /**
     * Test service function for loading users
     *
     * @covers \ezp\User\Service::load
     */
    public function testLoad()
    {
        $service = $this->repository->getUserService();
        $do = $service->load( 14 );
        self::assertEquals( $do->id, 14 );
        self::assertEquals( 'admin', $do->login );
        self::assertEquals( 'spam@ez.no', $do->email );
    }

    /**
     * Test service function for loading users
     *
     * @covers \ezp\User\Service::load
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadNotFound()
    {
        $service = $this->repository->getUserService();
        $service->load( 999 );
    }
}
