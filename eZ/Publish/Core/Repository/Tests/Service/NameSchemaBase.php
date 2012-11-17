<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\NameSchemaBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest;

/**
 * Test case for NameSchema service
 */
abstract class NameSchemaBase extends BaseServiceTest
{
    /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService ::resolveUrlAliasSchema
     */
    public function testResolveUrlAliasSchema()
    {
        /** @var \eZ\Publish\Core\Repository\NameSchemaService $service */
        $service = $this->repository->getNameSchemaService();
        self::markTestIncomplete( "Not implemented: " . __METHOD__ );
    }

     /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService ::resolveNameSchema
     */
    public function testResolveNameSchema()
    {
        /** @var \eZ\Publish\Core\Repository\NameSchemaService $service */
        $service = $this->repository->getNameSchemaService();
        self::markTestIncomplete( "Not implemented: " . __METHOD__ );
    }

     /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService ::validate
     */
    public function testValidate()
    {
        /** @var \eZ\Publish\Core\Repository\NameSchemaService $service */
        $service = $this->repository->getNameSchemaService();
        self::assertTrue( $service->validate( '', '' ) );
    }

     /**
     * Test eZ\Publish\Core\Repository\NameSchemaService method
     * @covers \eZ\Publish\Core\Repository\NameSchemaService ::resolve
     */
    public function testResolve()
    {
        /** @var \eZ\Publish\Core\Repository\NameSchemaService $service */
        $service = $this->repository->getNameSchemaService();
        self::markTestIncomplete( "Not implemented: " . __METHOD__ );
    }
}
