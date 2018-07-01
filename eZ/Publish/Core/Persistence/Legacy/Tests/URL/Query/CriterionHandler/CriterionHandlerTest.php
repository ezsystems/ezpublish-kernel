<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL\Query\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;
use PHPUnit\Framework\TestCase;

abstract class CriterionHandlerTest extends TestCase
{
    abstract public function testAccept();

    abstract public function testHandle();

    /**
     * Check if critetion handler accepts specyfied criterion class.
     *
     * @param CriterionHandler $handler
     * @param string $criterionClass
     */
    protected function assertHandlerAcceptsCriterion(CriterionHandler $handler, $criterionClass)
    {
        $this->assertTrue($handler->accept($this->createMock($criterionClass)));
    }

    /**
     * Check if critetion handler rejects specyfied criterion class.
     *
     * @param CriterionHandler $handler
     * @param string $criterionClass
     */
    protected function assertHandlerRejectsCriterion(CriterionHandler $handler, $criterionClass)
    {
        $this->assertFalse($handler->accept($this->createMock($criterionClass)));
    }
}
