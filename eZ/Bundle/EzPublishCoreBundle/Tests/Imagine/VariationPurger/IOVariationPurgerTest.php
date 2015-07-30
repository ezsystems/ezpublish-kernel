<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\VariationPurger;

use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger\IOVariationPurger;

class IOVariationPurgerTest extends \PHPUnit_Framework_TestCase
{
    public function testPurgesAliasList()
    {
        $ioService = $this->getMock('eZ\Publish\Core\IO\IOServiceInterface');
        $ioService
            ->expects($this->exactly(2))
            ->method('deleteDirectory')
            ->withConsecutive(
                array('_aliases/medium'),
                array('_aliases/large')
            );
        $purger = new IOVariationPurger($ioService);
        $purger->purge(array('medium', 'large'));
    }
}
