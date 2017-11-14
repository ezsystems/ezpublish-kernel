<?php

/**
 * File containing the AliasCleanerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\AliasCleaner;
use PHPUnit\Framework\TestCase;

class AliasCleanerTest extends TestCase
{
    /**
     * @var AliasCleaner
     */
    private $aliasCleaner;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resolver;

    protected function setUp()
    {
        parent::setUp();
        $this->resolver = $this->createMock('\Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface');
        $this->aliasCleaner = new AliasCleaner($this->resolver);
    }

    public function testRemoveAliases()
    {
        $originalPath = 'foo/bar/test.jpg';
        $this->resolver
            ->expects($this->once())
            ->method('remove')
            ->with(array($originalPath), array());

        $this->aliasCleaner->removeAliases($originalPath);
    }
}
