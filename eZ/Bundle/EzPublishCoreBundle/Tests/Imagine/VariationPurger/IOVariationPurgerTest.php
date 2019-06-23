<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\VariationPurger;

use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger\IOVariationPurger;
use eZ\Publish\Core\IO\IOServiceInterface;
use PHPUnit\Framework\TestCase;

class IOVariationPurgerTest extends TestCase
{
    public function testPurgesAliasList()
    {
        $ioService = $this->createMock(IOServiceInterface::class);
        $ioService
            ->expects($this->exactly(2))
            ->method('deleteDirectory')
            ->withConsecutive(
                ['_aliases/medium'],
                ['_aliases/large']
            );
        $purger = new IOVariationPurger($ioService);
        $purger->purge(['medium', 'large']);
    }
}
