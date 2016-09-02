<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\PathExpansion\ValueLoaders;

use eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders\UniqueUriValueLoader;
use stdClass;

class UniqueUriValueLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $innerLoaderMock;

    /**
     * @expectedException \eZ\Publish\Core\REST\Server\Output\PathExpansion\Exceptions\MultipleValueLoadException
     */
    public function testLoad()
    {
        $loader = $this->buildLoader();

        $returnValue = new stdClass();

        $this->getInnerLoaderMock()
            ->expects($this->exactly(2))
            ->method('load')
            ->withConsecutive(
                ['/doctors/david-tenant', null],
                ['/doctors/david-tenant', 'application/other-type']
            )
            ->will($this->returnValue($returnValue));

        self::assertEquals($returnValue, $loader->load('/doctors/david-tenant'));
        self::assertEquals($returnValue, $loader->load('/doctors/david-tenant', 'application/other-type'));
        $loader->load('/doctors/david-tenant');
    }

    /**
     * @return \eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders\UniqueUriValueLoader
     */
    protected function buildLoader()
    {
        return new UniqueUriValueLoader($this->getInnerLoaderMock());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders\UriValueLoader
     */
    protected function getInnerLoaderMock()
    {
        if (!isset($this->innerLoaderMock)) {
            $this->innerLoaderMock = $this
                ->getMockBuilder('eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders\UriValueLoader')
                ->getMock();
        }

        return $this->innerLoaderMock;
    }
}
